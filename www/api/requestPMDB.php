<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php"; ?>
<?php

	# 헤더설정
	header('Content-Type: application/json');

	# 변수검사
	if(!$_POST){
		$result["msg"] = "fail";
		echo json_encode($result);
		return false;
	}

	$proc_id = 0;

	foreach($_POST as $key => $value) {
		$_POST[$key] = preg_replace("[\<></\s]", "", $value);
	}

	# 변수설정
	$result = [];
	$api = $_POST["apiKey"];
	$cs_name = $_POST["csName"];
	$cs_tel = $_POST["csTel"];
	$etc = $_POST["etc"];
	$andColumns .= "";
	$andValues .= "";

	$site_info = view_sql("SELECT * FROM mt_site_info WHERE idx = 1");

	# 생산업체 API 검사
	$pmCode = view_sql("SELECT code_idx FROM mt_api WHERE use_yn = 'Y' AND auth_code = 'pm' AND api_key = '{$api}'")['code_idx'];
	if(!$pmCode){
		$result["msg"] = "fail";
		echo json_encode($result);
		return false;
	}
	$pm_code = $pmCode;

	# 생산업체 사용유무 검사
	$pmOverlap = view_sql("SELECT idx FROM mt_member_cmpy WHERE use_yn = 'Y' AND idx = '{$pmCode}'")['idx'];
	if(!$pmOverlap){
		$result["msg"] = "fail";
		echo json_encode($result);
		return false;
	}

	$chkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);
	$query = "SELECT * FROM mt_block_tel";
	$blackData = list_pdo($query, $value);
	while($row = $blackData->fetch(PDO::FETCH_ASSOC)){
		if($row['block_tel'] == $chkTel){
			$result["msg"] = "fail4";
			excute("INSERT INTO mt_api_log2 (prev_url, api_log, reg_idx, reg_ip) VALUES ( '{$prevURL}', 'fail4', 1, '{$proc_ip}'  )");
			echo json_encode($result);
			return false;
		}
	}
	# 중복검사
	$overlap_yn = "N";
	if($site['overlap_yn'] == "Y"){
		if($site['overlap_days'] > 0){
			$checkAndQuery = ($site['overlap_days']) ? " AND made_date > DATE_ADD(date_format(now(), '%Y-%m-%d'), INTERVAL - {$site['overlap_days']} day)" : "";
		}
		$checkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);
		$overDB = view_sql("SELECT * FROM mt_db WHERE use_yn = 'Y' AND replace(cs_tel, '-', '') = '{$checkTel}' {$checkAndQuery}");
		if($overDB){
			// $result["msg"] = "return tel";
			// echo json_encode($result);
			// return false;
			$overlap_yn = "Y";
		}
		
		$overDBt = view_sql("SELECT * FROM mt_db WHERE use_yn = 'Y' AND cs_tel = '{$cs_tel}' {$checkAndQuery}");
		if($overDBt){
			// $result["msg"] = "return tel";
			// echo json_encode($result);
			// return false;
			$overlap_yn = "Y";
		}
	}

	# 기타항목
	$value = array(''=> '');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = 'Y'
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);

	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){	
		$andColumns .= ", {$row['column_code']}";
		$andValues .= ", '{$etc[$row['column_name']]}'";
	}

	# 저장

	$value = array(''=>'');
	$sql = "
		INSERT INTO mt_db
			( save_type, made_date, pm_code, cs_name, cs_tel, overlap_yn, reg_idx, reg_ip {$andColumns} )
		VALUES
			( 'API', now(), '{$pmCode}', '{$cs_name}', '{$cs_tel}', '{$overlap_yn}', '0', '{$proc_ip}' {$andValues} )
	";
	$exec = execute_pdo($sql, $value);
	if($exec['data']->rowCount() > 0){
		$idx = $exec['insertIdx'];

		$api_idx = view_sql("SELECT idx FROM mt_api WHERE use_yn = 'Y' AND auth_code = 'pm' AND api_key = '{$api}'")['idx'];
		$prevURL = ($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "not found";
		
		excute("
			INSERT INTO mt_api_log
				( api_idx, db_idx, prev_url, reg_idx, reg_ip )
			VALUES
				( '{$api_idx}', '{$idx}', '{$prevURL}', 0, '{$proc_ip}' )
		");

		$cmpy = view_sql("
			SELECT MT.*
			FROM mt_member_cmpy MT
			WHERE use_yn = 'Y' 
			AND auth_code = '003'
			AND idx = '{$pmCode}'
		");
		# 메인 회사정보
		$mainCmpy = view_sql("SELECT * FROM mt_member_cmpy WHERE idx = '0001'");

		# 200924 SMS전송
		$templateInfo = view_sql("SELECT * FROM mt_sms_template WHERE idx = '1'");
		if($templateInfo["use_yn"] == "Y"){
			$sql = list_sql("SELECT * FROM mt_member WHERE use_yn = 'Y' AND auth_code IN ( 001, 002 )");
			// $sql = list_sql("SELECT * FROM mt_member WHERE use_yn = 'Y' AND m_id = 'jeon'");
			while($row = mysqli_fetch_array($sql, MYSQLI_ASSOC)){
				$templateInfo["contents"] = str_replace("#{COMPANY_NAME}", $mainCmpy["company_name"], $templateInfo["contents"]);
				$templateInfo["contents"] = str_replace("#{PM_NAME}", $cmpy["company_name"], $templateInfo["contents"]);
				$templateInfo["contents"] = str_replace("#{CNT}", 1, $templateInfo["contents"]);
				$templateInfo["contents"] = str_replace("#{DATE}", date("Y-m-d H:i:s"), $templateInfo["contents"]);
				
				if($row["m_tel"]){
					$smsResult = smsSend($row["m_tel"], $templateInfo["contents"]);
					if($smsResult["msg"] == "success"){
						$query = "
							INSERT INTO mt_sms_log 
								( send_name, send_tel, contents, receive_name, receive_tel, result_code, result_msg, result_id, reg_idx, reg_ip )
							VALUES 
								( '{$smsResult['send_name']}', '{$smsResult['send_tel']}', '{$templateInfo["contents"]}', '{$row["m_name"]}', '{$row["m_tel"]}', '-', '-', '{$smsResult["Msg_Id"]}', '0', '{$proc_ip}'  )
						";
						$aa = ehtml($query);
						excute("INSERT INTO mt_execute_log ( `query` ) VALUES ( '{$aa}' ) ");
						excute($query);
					}
				}
			}
		}

		if($site_info['auto_dist_yn'] == 'T'){
			$insert_team = view_sql("select * from mt_member_team where use_yn = 'Y' AND dist_cnt != dist_cnt_now ORDER BY dist_cnt_now, dist_sort, idx limit 0,1");

			$team_cnt = $insert_team['dist_cnt_now'] + 1;

			excute("
				UPDATE mt_db SET
					  dist_code = '002'
					, dist_date = now()
					, tm_code = '{$insert_team['idx']}'
					, m_idx = '{$insert_team['m_idx']}'
					, edit_idx = '{$proc_id}'
					, edit_ip = '{$proc_ip}'
					, edit_date = now()
					, order_by_date = now()
				WHERE idx = '{$idx}'
			");

			# 분배기록 등록
			excute("
				INSERT INTO mt_db_dist_log
					( tm_code, m_idx, db_idx, reg_idx, reg_ip )
				VALUES
					( '{$insert_team['idx']}', '{$insert_team['m_idx']}', '{$idx}', '{$proc_id}', '{$proc_ip}' )
			");


			# 로그등록
			$pmCode = view_sql("SELECT pm_code FROM mt_db WHERE idx = '{$idx}'")['pm_code'];
			if($pmCode){
				excute("
					INSERT INTO mt_db_chart_log
						( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
					VALUES
						( 'pm', '{$pmCode}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
				");

				# 생산업체 잔여DB
				$nowDate = date("Y-m-d");
				$pmCodeStock = view_sql("SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = '{$pmCode}'")['cnt'];
				$pmCodeStockIDX = view_sql("SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = '{$pmCode}' AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'")['idx'];
				if(!$pmCodeStockIDX){
					$value = array(''=>'');
					$sql ="
						INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
						VALUES
							( 'pm', '{$pmCode}', 'stock', 0, '{$proc_id}', '{$proc_ip}' )
					";
					$exec = execute_pdo($sql, $value);
					$pmCodeStockIDX = $exec['insertIdx'];
				}

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
			excute("
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'tm', '{$insert_team['idx']}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
			");
			
			# 팀원별 분배기록
			excute("
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'fc', '{$insert_team['m_idx']}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
			");	

			excute("UPDATE mt_member_team SET dist_cnt_now = '{$team_cnt}' WHERE idx = '{$insert_team['idx']}'");

			$reset_cnt = view_sql("select count(*) as cnt from mt_member_team where use_yn = 'Y' AND dist_cnt != dist_cnt_now")['cnt'];
			if($reset_cnt == 0){
				excute("UPDATE mt_member_team SET dist_cnt_now = 0 WHERE use_yn = 'Y'");
			}
		}

		if($site_info['auto_dist_yn'] == 'F'){
			$insert_team = $site_info['auto_dist_team'];
			$insert_fc = view_sql("SELECT * FROM mt_member where use_yn = 'Y' AND tm_code = '{$insert_team}' AND dist_cnt != dist_cnt_now ORDER BY dist_cnt_now, dist_sort, idx limit 0,1");
			if($insert_team == '0000'){
				$insert_fc = view_sql("SELECT * FROM mt_member where use_yn = 'Y' AND auth_code > 3 AND dist_cnt != dist_cnt_now ORDER BY dist_cnt_now, dist_sort, idx limit 0,1");
			}

			$fc_cnt = $insert_fc['dist_cnt_now'] + 1;

			excute("
				UPDATE mt_db SET
					  dist_code = '002'
					, dist_date = now()
					, tm_code = '{$insert_fc['tm_code']}'
					, m_idx = '{$insert_fc['idx']}'
					, edit_idx = '{$proc_id}'
					, edit_ip = '{$proc_ip}'
					, edit_date = now()
					, order_by_date = now()
				WHERE idx = '{$idx}'
			");

			# 분배기록 등록
			excute("
				INSERT INTO mt_db_dist_log
					( tm_code, m_idx, db_idx, reg_idx, reg_ip )
				VALUES
					( '{$insert_fc['tm_code']}', '{$insert_fc['idx']}', '{$idx}', '{$proc_id}', '{$proc_ip}' )
			");

			# 로그등록
			$pmCode = view_sql("SELECT pm_code FROM mt_db WHERE idx = '{$idx}'")['pm_code'];
			if($pmCode){
				excute("
					INSERT INTO mt_db_chart_log
						( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
					VALUES
						( 'pm', '{$pmCode}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
				");

				# 생산업체 잔여DB
				$nowDate = date("Y-m-d");
				$pmCodeStock = view_sql("SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = '{$pmCode}'")['cnt'];
				$pmCodeStockIDX = view_sql("SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = '{$pmCode}' AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'")['idx'];
				if(!$pmCodeStockIDX){
					$value = array(''=>'');
					$sql ="
						INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
						VALUES
							( 'pm', '{$pmCode}', 'stock', 0, '{$proc_id}', '{$proc_ip}' )
					";
					$exec = execute_pdo($sql, $value);
					$pmCodeStockIDX = $exec['insertIdx'];
				}

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
			excute("
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'tm', '{$insert_fc['tm_code']}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
			");
			
			# 팀원별 분배기록
			excute("
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'fc', '{$insert_fc['idx']}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
			");	



			excute("UPDATE mt_member SET dist_cnt_now = '{$fc_cnt}' WHERE idx = '{$insert_fc['idx']}'");

			
			$reset_cnt = view_sql("SELECT count(*) as cnt from mt_member where use_yn = 'Y' AND tm_code = '{$insert_team}' AND dist_cnt != dist_cnt_now")['cnt'];
			if($insert_team == '0000'){
				$reset_cnt = view_sql("SELECT count(*) as cnt from mt_member where use_yn = 'Y' AND auth_code > 3 AND dist_cnt != dist_cnt_now")['cnt'];
			}
			if($reset_cnt == 0){
				excute("UPDATE mt_member SET dist_cnt_now = 0 WHERE use_yn = 'Y'");
			}
		}

		if($site_info['auto_dist_yn'] == 'P' && $pm_code){
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

				if($insert_team && $insert_team != '0000'){
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


		$result["msg"] = "success";
	} else {
		$result["msg"] = "fail";
	}

	# 결과추출
	echo json_encode($result);

?>