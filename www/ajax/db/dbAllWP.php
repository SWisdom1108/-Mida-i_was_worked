<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$pm_code = ($pm_code) ? $pm_code : 0;
	$cs_name = ehtml($_POST['cs_name']);

	$value = array(''=>'');
	$query = "SELECT * FROM mt_site_info WHERE idx = 1";
	$site_info = view_pdo($query, $value);



	# 컬럼 정리
	$andColumns = "";
	$andValues = "";	
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = 'Y'
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);

	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$data = "";
		if ( gettype($_POST[$row['column_code']]) == "array" ){
			$data = implode(",", $_POST[$row['column_code']]);

			// if ( $row['column_code'] == "cs_etc03" ){
			// 	$data = implode("@", $_POST[$row['column_code']]);
			// }

		}else{
			if($row['column_type'] == "file"){
				$file = $_FILES[$row['column_code']];
				$directoryName = "db_etc";
				$uploadResult = fileUpload($file, $directoryName);
				if($uploadResult['result']) {
					$data = $uploadResult['fileName']."@#@#".$uploadResult['originalFileName'];
				}
			}else{
				$data = $_POST[$row['column_code']];
			}
		}
		$data = ehtml($data);
		$andColumns .= ", {$row['column_code']}";
		$andValues .= ", '{$data}'";
	}

	$chkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);

	$query = "SELECT * FROM mt_block_tel";
	$blackData = list_pdo($query, $value);
	while($row = $blackData->fetch(PDO::FETCH_ASSOC)){
		if($row['block_tel'] == $chkTel){
			echo "블랙리스트에 추가된 연락처입니다.";
			return false;
		}
	}

	# 중복검사
	$overlap_yn = "N";
	if($site['overlap_yn'] == "Y"){
		if($site['overlap_days'] > 0){
			$checkAndQuery = ($site['overlap_days']) ? " AND made_date > DATE_ADD(date_format('{$made_date}', '%Y-%m-%d'), INTERVAL - {$site['overlap_days']} day)" : "";
		}
		$checkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);
		$value = array(':checkTel'=>$checkTel);
		$query = "SELECT * FROM mt_db WHERE use_yn = 'Y' AND replace(cs_tel, '-', '') = :checkTel {$checkAndQuery}";
		$overDB = view_pdo($query, $value);
		if($overDB){
			$overlap_yn = "Y";
		}

	}

	$value = array(':made_date'=>$made_date, ':pm_code'=>$pm_code, ':cs_name'=>$cs_name, ':cs_tel'=>$cs_tel, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':overlap_yn'=>$overlap_yn, );

	$sql = "
		INSERT INTO mt_db
			( made_date, pm_code, cs_name, cs_tel, reg_idx, reg_ip {$andColumns}, overlap_yn )
		VALUES
			( :made_date, :pm_code, :cs_name, :cs_tel, :proc_id, :proc_ip {$andValues}, :overlap_yn )
	";

	$exec = execute_pdo($sql, $value);

	if( $exec['data']->rowCount() > 0 ){
		$idx= $exec['insertIdx'];
		if($site_info['auto_dist_yn'] == 'T'){
			$value = array(''=>'');
			$query = "select * from mt_member_team where use_yn = 'Y' AND dist_cnt != dist_cnt_now ORDER BY dist_cnt_now, dist_sort, idx limit 0,1";
			$insert_team = view_pdo($query, $value);
			$team_cnt = $insert_team['dist_cnt_now'] + 1;


			$value = array(':tm_code'=>$insert_team['idx'], ':m_idx'=>$insert_team['m_idx'], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx  );
			$query = "UPDATE mt_db SET
					  dist_code = '002'
					, dist_date = now()
					, tm_code = :tm_code
					, m_idx = :m_idx
					, edit_idx = :proc_id
					, edit_ip = :proc_ip
					, edit_date = now()
					, order_by_date = now()
				WHERE idx = :idx";

			execute_pdo($query, $value);



			# 분배기록 등록

			$query = "INSERT INTO mt_db_dist_log
					( tm_code, m_idx, db_idx, reg_idx, reg_ip )
				VALUES
					( :tm_code, :m_idx, :idx, :proc_id, :proc_ip )";

			execute_pdo($query, $value);


			# 로그등록

			$value = array(':idx'=>$idx);
			$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
			$pmCode = view_pdo($query, $value)['pm_code'];
			if($pmCode){
				$value = array(':pmCode'=>$pmCode, ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
				$query = "INSERT INTO mt_db_chart_log
						( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
					VALUES
						( 'pm', :pmCode, 'dist', :idx, :proc_id, :proc_ip )";
				execute_pdo($query, $value);


				# 생산업체 잔여DB
				$nowDate = date("Y-m-d");

				$value = array(':pmCode'=>$pmCode);
				$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = '{$pmCode}'";
				$pmCodeStock = view_pdo($query, $value)['cnt'];

				$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :pmCode AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
				$pmCodeStockIDX = view_pdo($query, $value)['idx'];
				if(!$pmCodeStockIDX){

					$value = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
						VALUES
							( 'pm', :pmCode, 'stock', 0, :proc_id, :proc_ip )";

					$exec2 = execute_pdo($query, $value);

					$pmCodeStockIDX = $exec2['insertIdx'];
				}

				$value = array(':pmCodeStock'=>$pmCodeStock, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':pmCodeStockIDX'=>$pmCodeStockIDX );
				$query = "UPDATE mt_db_chart_log SET
						  db_cnt = :pmCodeStock
						, edit_idx = :proc_id
						, edit_ip = :proc_ip
						, edit_date = now()
					WHERE idx = :pmCodeStockIDX";

				execute_pdo($query, $value);
			}

			# 팀별 분배기록
			$value = array(':code_value'=>$insert_team['idx'], ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
			$query = "INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'tm', :code_value, 'dist', :idx, :proc_id, :proc_ip )";
			execute_pdo($query, $value);

			
			# 팀원별 분배기록
			$value = array(':code_value'=>$insert_team['m_idx'], ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
			$query = "INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'fc', :code_value, 'dist', :idx, :proc_id, :proc_ip )";
			execute_pdo($query, $value);

			$value = array(':team_cnt'=>$team_cnt, ':idx'=>$insert_team['idx']);
			$query = "UPDATE mt_member_team SET dist_cnt_now = :team_cnt WHERE idx = :idx";
			execute_pdo($query, $value);

			$value = array(''=>'');
			$query = "select count(*) as cnt from mt_member_team where use_yn = 'Y' AND dist_cnt != dist_cnt_now";
			$reset_cnt = view_pdo($query, $value)['cnt'];
			if($reset_cnt == 0){
				$query = "UPDATE mt_member_team SET dist_cnt_now = 0 WHERE use_yn = 'Y'";
				execute_pdo($query, $value);
			}
		}
		else if($site_info['auto_dist_yn'] == 'F'){
			$insert_team = $site_info['auto_dist_team'];
			$value = array(':insert_team'=>$insert_team);
			$query = "SELECT * FROM mt_member where use_yn = 'Y' AND tm_code = :insert_team AND dist_cnt != dist_cnt_now ORDER BY dist_cnt_now, dist_sort, idx limit 0,1";
			if($insert_team == '0000'){
				$value = array(''=>'');
				$query = "SELECT * FROM mt_member where use_yn = 'Y' AND dist_cnt != dist_cnt_now AND auth_code > 3 ORDER BY dist_cnt_now, dist_sort, idx limit 0,1";
			}
			$insert_fc = view_pdo($query, $value);
			$fc_cnt = $insert_fc['dist_cnt_now'] + 1;

			$value = array(':insert_team'=>$insert_fc['tm_code'], ':m_idx'=>$insert_fc['idx'], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx);
			$query = "UPDATE mt_db SET
					  dist_code = '002'
					, dist_date = now()
					, tm_code = :insert_team
					, m_idx = :m_idx
					, edit_idx = :proc_id
					, edit_ip = :proc_ip
					, edit_date = now()
					, order_by_date = now()
				WHERE idx = :idx";
			execute_pdo($query, $value);



			# 분배기록 등록
			$query = "INSERT INTO mt_db_dist_log
					( tm_code, m_idx, db_idx, reg_idx, reg_ip )
				VALUES
					( :insert_team, :m_idx, :idx, :proc_id, :proc_ip )";
			execute_pdo($query, $value);

			# 로그등록
			$value = array(':idx'=>$idx);
			$query = "SELECT pm_code FROM mt_db WHERE idx = '{$idx}'";
			$pmCode = view_pdo($query, $value)['pm_code'];
			if($pmCode){

				$value = array(':pmCode'=>$pmCode, ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
				$query = "INSERT INTO mt_db_chart_log
						( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
					VALUES
						( 'pm', :pmCode, 'dist', :idx, :proc_id, :proc_ip )";
				execute_pdo($query, $value);


				# 생산업체 잔여DB
				$nowDate = date("Y-m-d");

				$value = array(':pmCode'=>$pmCode);
				$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pmCode";
				$pmCodeStock = view_pdo($query, $value)['cnt'];

				$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :pmCode AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
				$pmCodeStockIDX = view_pdo($query, $value)['idx'];

				if(!$pmCodeStockIDX){

					$value = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
						VALUES
							( 'pm', :pmCode, 'stock', 0, :proc_id, :proc_ip )";

					$exec2 = execute_pdo($query, $value);

					$pmCodeStockIDX = $exec2['insertIdx'];
				}

				$value = array(':pmCodeStock'=>$pmCodeStock, ':pmCodeStockIDX'=>$pmCodeStockIDX, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
				$query = "UPDATE mt_db_chart_log SET
						  db_cnt = :pmCodeStock
						, edit_idx = :proc_id
						, edit_ip = :proc_ip
						, edit_date = now()
					WHERE idx = :pmCodeStockIDX";
				execute_pdo($query, $value);
			}


			# 팀별 분배기록
			$value = array(':insert_team'=>$insert_fc['tm_code'], ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query = "INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'tm', :insert_team, 'dist', :idx, :proc_id, :proc_ip )";
			execute_pdo($query, $value);
			
			# 팀원별 분배기록
			$value = array(':code_value'=>$insert_fc['idx'], ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query = "INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'fc', :code_value, 'dist', :idx, :proc_id, :proc_ip )";

			execute_pdo($query, $value);

			$value = array( ':fc_cnt'=>$fc_cnt, ':idx'=>$insert_fc['idx']);
			$query = "UPDATE mt_member SET dist_cnt_now = :fc_cnt WHERE idx = :idx";
			execute_pdo($query, $value);

			$value = array(':insert_team'=>$insert_fc['tm_code']);
			$query = "SELECT count(*) as cnt from mt_member where use_yn = 'Y' AND tm_code = :insert_team AND dist_cnt != dist_cnt_now";
			if($insert_team == '0000'){
				$value = array(''=>'');
				$query = "SELECT count(*) as cnt from mt_member where use_yn = 'Y' AND auth_code > 3 AND dist_cnt != dist_cnt_now";
			}
			$reset_cnt = view_pdo($query, $value)['cnt'];
			if($reset_cnt == 0){
				excute("UPDATE mt_member SET dist_cnt_now = 0 WHERE use_yn = 'Y'");
			}
		}
		else if($site_info['auto_dist_yn'] == 'P' && $pm_code){
			if($pm_module == 'Y'){


				$value = array(':pm_code'=>$pm_code);
				$query = "SELECT auto_dist_team FROM mt_member_cmpy WHERE idx = :pm_code";
				$insert_team = view_pdo($query, $value)['auto_dist_team'];

				if($insert_team == '0000'){
					$value = array(''=>'');
					$query = "SELECT *, (SELECT tm_code FROM mt_member WHERE idx = MT.m_idx) as tm_code FROM mt_member_pmDist MT where (SELECT use_yn FROM mt_member WHERE idx = MT.m_idx) = 'Y' AND dist_cnt != dist_cnt_now AND pm_code = {$pm_code} ORDER BY dist_cnt_now, dist_sort, idx limit 0,1";
				}else{
					$value = array(''=>'');
					$query = "SELECT *, (SELECT tm_code FROM mt_member WHERE idx = MT.m_idx) as tm_code FROM mt_member_pmDist MT where (SELECT use_yn FROM mt_member WHERE idx = MT.m_idx) = 'Y' AND dist_cnt != dist_cnt_now AND pm_code = {$pm_code} AND (SELECT tm_code FROM mt_member WHERE idx = MT.m_idx) = {$insert_team} ORDER BY dist_cnt_now, dist_sort, idx limit 0,1";
				}

				$insert_fc = view_pdo($query, $value);
				$fc_cnt = $insert_fc['dist_cnt_now'] + 1;
				

				$value = array(':insert_team'=>$insert_fc['tm_code'], ':insert_fc'=>$insert_fc['m_idx'], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx);
				$query = "UPDATE mt_db SET
						  dist_code = '002'
						, dist_date = now()
						, tm_code = :insert_team
						, m_idx = :insert_fc
						, edit_idx = :proc_id
						, edit_ip = :proc_ip
						, edit_date = now()
						, order_by_date = now()
					WHERE idx = :idx";
				execute_pdo($query, $value);

				# 분배기록 등록
				$query = "INSERT INTO mt_db_dist_log
						( tm_code, m_idx, db_idx, reg_idx, reg_ip )
					VALUES
						( :insert_team, :insert_fc, :idx, :proc_id, :proc_ip )";

				execute_pdo($query, $value);


				# 로그등록
				$value = array(':idx'=>$idx);
				$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
				$pmCode = view_pdo($query, $value)['pm_code'];
				if($pmCode){
					$value = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx);
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'pm', :pmCode, 'dist', :idx, :proc_id, :proc_ip )";
					execute_pdo($query, $value);

					# 생산업체 잔여DB
					$nowDate = date("Y-m-d");

					$value = array(':pmCode'=>$pmCode);
					$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pmCode";
					$pmCodeStock = view_pdo($query, $value)['cnt'];

					$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :pmCode AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
					$pmCodeStockIDX = view_pdo($query, $value)['idx'];
					if(!$pmCodeStockIDX){

						$value = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
						$query = "INSERT INTO mt_db_chart_log
								( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
							VALUES
								( 'pm', :pmCode, 'stock', 0, :proc_id, :proc_ip )";
						$exec2 = execute_pdo($query, $value);

						$pmCodeStockIDX = $exec2['insertIdx'];
					}

					$value = array(':pmCodeStock'=>$pmCodeStock, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':pmCodeStockIDX'=>$pmCodeStockIDX );
					excute("
						UPDATE mt_db_chart_log SET
							  db_cnt = {$pmCodeStock}
							, edit_idx = '{$proc_id}'
							, edit_ip = '{$proc_ip}'
							, edit_date = now()
						WHERE idx = '{$pmCodeStockIDX}'
					");
				}



				# 팀별 분배기록
				$value = array(':insert_team'=>$insert_fc['tm_code'], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx );
				$query = "INSERT INTO mt_db_chart_log
						( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
					VALUES
						( 'tm', :insert_team, 'dist', :idx, :proc_id, :proc_ip )";

				execute_pdo($query, $value);
				
				# 팀원별 분배기록

				$value = array(':insert_fc'=>$insert_fc['m_idx'], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx );
				$query = "INSERT INTO mt_db_chart_log
						( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
					VALUES
						( 'fc', :insert_fc, 'dist', :idx, :proc_id, :proc_ip )";

				execute_pdo($query, $value);

				$value = array( ':fc_cnt'=>$fc_cnt, ':idx'=>$insert_fc['idx']);
				$query = "UPDATE mt_member_pmDist SET dist_cnt_now = :fc_cnt WHERE idx = :idx";
				execute_pdo($query, $value);





				$value = array(''=>'');
				$query = "SELECT count(*) as cnt from mt_member_pmDist MT where (SELECT use_yn FROM mt_member WHERE idx = MT.m_idx) = 'Y' AND dist_cnt != dist_cnt_now AND pm_code = {$pm_code} AND (SELECT tm_code FROM mt_member WHERE idx = MT.m_idx) = {$insert_team}";
				if($insert_team == '0000'){
					$value = array(''=>'');
					$query = "SELECT count(*) as cnt from mt_member_pmDist MT where (SELECT use_yn FROM mt_member WHERE idx = MT.m_idx) = 'Y' AND dist_cnt != dist_cnt_now AND pm_code = {$pm_code}";
				}
				$reset_cnt = view_pdo($query, $value)['cnt'];
				if($reset_cnt == 0){

					excute("UPDATE mt_member_pmDist MT SET dist_cnt_now = 0 WHERE (SELECT use_yn FROM mt_member WHERE idx = MT.m_idx) = 'Y' AND pm_code = {$pm_code}");
				}
					
				


			}else{
				$value = array(':pm_code'=>$pm_code);
				$query = "SELECT auto_dist_team FROM mt_member_cmpy WHERE idx = :pm_code";
				$insert_team = view_pdo($query, $value)['auto_dist_team'];

				if($insert_team){
					$value = array(':insert_team'=>$insert_team);
					$query = "SELECT m_idx FROM mt_member_team WHERE idx = {$insert_team}";
					$insert_fc = view_pdo($query, $value)['m_idx'];

					$value = array(':insert_team'=>$insert_team, ':insert_fc'=>$insert_fc, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx);
					$query = "UPDATE mt_db SET
							  dist_code = '002'
							, dist_date = now()
							, tm_code = :insert_team
							, m_idx = :insert_fc
							, edit_idx = :proc_id
							, edit_ip = :proc_ip
							, edit_date = now()
							, order_by_date = now()
						WHERE idx = :idx";
					execute_pdo($query, $value);

					# 분배기록 등록
					$query = "INSERT INTO mt_db_dist_log
							( tm_code, m_idx, db_idx, reg_idx, reg_ip )
						VALUES
							( :insert_team, :insert_fc, :idx, :proc_id, :proc_ip )";

					execute_pdo($query, $value);


					# 로그등록
					$value = array(':idx'=>$idx);
					$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
					$pmCode = view_pdo($query, $value)['pm_code'];
					if($pmCode){
						$value = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx);
						$query = "INSERT INTO mt_db_chart_log
								( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
							VALUES
								( 'pm', :pmCode, 'dist', :idx, :proc_id, :proc_ip )";
						execute_pdo($query, $value);

						# 생산업체 잔여DB
						$nowDate = date("Y-m-d");

						$value = array(':pmCode'=>$pmCode);
						$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pmCode";
						$pmCodeStock = view_pdo($query, $value)['cnt'];

						$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :pmCode AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
						$pmCodeStockIDX = view_pdo($query, $value)['idx'];
						if(!$pmCodeStockIDX){

							$value = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
							$query = "INSERT INTO mt_db_chart_log
									( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
								VALUES
									( 'pm', :pmCode, 'stock', 0, :proc_id, :proc_ip )";
							$exec2 = execute_pdo($query, $value);

							$pmCodeStockIDX = $exec2['insertIdx'];
						}

						$value = array(':pmCodeStock'=>$pmCodeStock, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':pmCodeStockIDX'=>$pmCodeStockIDX );
						excute("
							UPDATE mt_db_chart_log SET
								  db_cnt = {$pmCodeStock}
								, edit_idx = '{$proc_id}'
								, edit_ip = '{$proc_ip}'
								, edit_date = now()
							WHERE idx = '{$pmCodeStockIDX}'
						");
					}



					# 팀별 분배기록
					$value = array(':insert_team'=>$insert_team, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx );
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'tm', :insert_team, 'dist', :idx, :proc_id, :proc_ip )";

					execute_pdo($query, $value);
					
					# 팀원별 분배기록

					$value = array(':insert_fc'=>$insert_fc, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx );
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'fc', :insert_fc, 'dist', :idx, :proc_id, :proc_ip )";

					execute_pdo($query, $value);
					
				}
			}				
		}

		echo "success";
	}  else {
		echo "fail";
	}

?>