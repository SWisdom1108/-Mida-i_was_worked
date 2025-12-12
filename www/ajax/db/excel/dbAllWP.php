<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php include $_SERVER['DOCUMENT_ROOT']."/plugin/excel/aaaa.php"; ?>
<?php
	set_time_limit(0);	
	ini_set('memory_limit','-1');
	ini_set('max_execution_time',0);
	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	$file = $_FILES['file'];

	$value = array(''=>'');
	$query = "SELECT * FROM mt_site_info WHERE idx = 1";
	$site_info = view_pdo($query, $value);

	if(!$file['type']){
		echo "return upload";
		return false;
	}

	# 컬럼 정리
	$lastCnt = 4;
	$columnArr = [];
	$value = array(''=>'');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = 'Y'
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);

	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$lastCnt++;
		
		$thisdatas = [];
		$thisdatas['code'] = $row['column_code'];
		
		array_push($columnArr, $thisdatas);
	}

	$success = 0;
	$fail = 0;
	$result = [];
	
	$failedData = [];

	makeDir("/excelLog/");
	$fileExt = explode(".", $file['name']);
	$fileName = date("YmdHis")."_{$user['idx']}_{$user['m_name']}.{$fileExt[count($fileExt)-1]}";
	$excelFile = $_SERVER['DOCUMENT_ROOT']."/excelLog/{$fileName}";
	if(!move_uploaded_file($file['tmp_name'], $excelFile)){
		echo "return upload";
		return false;
	}

	$objPHPExcel = PHPExcel_IOFactory::load($excelFile);
	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);



	$i = 1;
	foreach ( $sheetData as $row ){
		if($i++ == 1){
			continue;
		}
		$failed = [];

		$andColumns = "";
		$andValues = "";
		$lastColumns = 4;


		$pmCode = trim($row['A']);
		$made_date = trim($row['B']);
		$made_date = ($made_date) ? $made_date : date("Y-m-d");
		$cs_name = trim($row['C']);
		$cs_tel = trim($row['D']);

		$failed['cs_name'] = $cs_name;
		$failed['cs_tel'] = $cs_tel;

		foreach($columnArr as $index => $val){
			${$val['code']} = ehtml(trim($row[etExcelColumnString($lastColumns++)]));
			$failed[$val['code']] = ${$val['code']};
			
			$andColumns .= ", {$val['code']}";
			$andValues .= ", '{${$val['code']}}'";
		}
		
		# 한 행 데이터 체크
		$forStatus = true;
		for($ii = 0; $ii < ($lastCnt + 1); $ii++){
			if( trim($row[etExcelColumnString($ii)]) ){
				# 한 행에서 데이터가 1개라도 존재할 경우 for문 진행
				break;
			} else {
				if($ii == $lastCnt){
					# 마지막 행에서도 없으면 다음 for문 진행
					$forStatus = false;
				}
			}
		}
		
		if(!$forStatus){
			continue;
		}
		
		# 이름
		if(!$cs_name){
			$failed['reason'] = "{$customLabel["cs_name"]}없음";
			array_push($failedData, $failed);
			$fail++;
			continue;
		}
		
		# 연락처
		if(!$cs_tel){
			$failed['reason'] = "{$customLabel["cs_tel"]}없음";
			array_push($failedData, $failed);
			$fail++;
			continue;
		}

		# 중복검사
		$overlap_yn = "N";
		if($site['overlap_yn'] == "Y"){
			if($site['overlap_days'] > 0){
				$checkAndQuery = ($site['overlap_days']) ? " AND made_date > DATE_ADD(date_format('{$made_date}', '%Y-%m-%d'), INTERVAL - {$site['overlap_days']} day)" : "";
			}
			$checkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);
			$checkTel2 = preg_replace('/-(\d{4})-/', '-$1', $cs_tel);
			$checkTel3 = preg_replace('/-/', '', $cs_tel, 1);
			$checkTel4 = $cs_tel;

		    // (A) 휴대폰: 010-1234-1234
		    if (strlen($checkTel) === 11 && substr($checkTel, 0, 3) === '010') {
		        $checkTel4 = substr($checkTel, 0, 3) . '-' . substr($checkTel, 3, 4) . '-' . substr($checkTel, 7, 4);
		    }
		    // (B) 서울 번호 (02)
		    elseif (substr($checkTel, 0, 2) === '02') {
		        if (strlen($checkTel) === 9) { // 02-123-4567
		            $checkTel4 = substr($checkTel, 0, 2) . '-' . substr($checkTel, 2, 3) . '-' . substr($checkTel, 5);
		        } elseif (strlen($checkTel) === 10) { // 02-1234-5678
		            $checkTel4 = substr($checkTel, 0, 2) . '-' . substr($checkTel, 2, 4) . '-' . substr($checkTel, 6);
		        }
		    }
		    // (C) 나머지 지역번호 (3자리: 031, 051, 062 등)
		    elseif (preg_match('/^0\d{2}/', $checkTel)) {
		        $areaCode = substr($checkTel, 0, 3);
		        if (strlen($checkTel) === 10) { // 031-123-4567
		            $checkTel4 = $areaCode . '-' . substr($checkTel, 3, 3) . '-' . substr($checkTel, 6);
		        } elseif (strlen($checkTel) === 11) { // 031-1234-5678
		            $checkTel4 = $areaCode . '-' . substr($checkTel, 3, 4) . '-' . substr($checkTel, 7);
		        }
		    }

			$value = array(''=>'');
			$query = "SELECT * FROM mt_db WHERE use_yn = 'Y' AND cs_tel in ('{$cs_tel}', '{$checkTel}', '{$checkTel2}', '{$checkTel3}', '{$checkTel4}') {$checkAndQuery}";
			$overDB = view_pdo($query, $value);
			if($overDB){
				$overlap_yn = "Y";
			}

		}
		
		$chkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);
		$value = array(''=>'');
		$query = "SELECT * FROM mt_block_tel";
		$blackData = list_pdo($query, $value);
		foreach($blackData as $row){
			if($row['block_tel'] == $chkTel){
				$failed['reason'] = "블랙리스트";
				array_push($failedData, $failed);
				$fail++;
				continue 2;
			}
		}
		
		# 생산업체
		if($pmCode){
			$pmCode = str_replace("PM", "", $pmCode);
			$pmCode = str_replace("pm", "", $pmCode);
			
			$value = array(':idx'=>$pmCode);
			$query = "SELECT idx FROM mt_member_cmpy WHERE use_yn = 'Y' AND auth_code = '003' AND idx = :idx";
			$pmCode = view_pdo($query, $value)["idx"];
		}
		$pmCode = ($pmCode) ? $pmCode : 0000;
		$pm_code = $pmCode;

		
		$proc_ip = "1.1.1.1";
		$value = array(''=>'');
		$sql = "
			INSERT INTO mt_db
				( save_type, pm_code, made_date, cs_name, cs_tel, reg_idx, reg_ip {$andColumns}, overlap_yn )
			VALUES
				( 'excel', '{$pmCode}', '{$made_date}', '{$cs_name}', '{$cs_tel}', '{$proc_id}', '{$proc_ip}' {$andValues}, '{$overlap_yn}' )
		";
		$exec = execute_pdo($sql, $value);
		if($exec['data']->rowCount() > 0){

			$idx = $exec['insertIdx'];
			
			if ( $_SERVER['REMOTE_ADDR'] != "118.45.184.18" || 1 ){
				# 로그등록
				excute("
					INSERT INTO mt_db_chart_log
						( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
					VALUES
						( 'pm', '{$pmCode}', 'upload', '{$idx}', '{$proc_id}', '{$proc_ip}' )
				");
				
				# 생산업체 잔여DB
				$nowDate = date("Y-m-d");
				$value = array(':pm_code'=>$pmCode);
				$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pm_code";
				$pmCodeStock = view_pdo($query, $value)['cnt'];
				$value = array(':code_value'=>$pmCode);
				$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
				$pmCodeStockIDX = view_pdo($query, $value)['idx'];
				if(!$pmCodeStockIDX){
					$value = array(''=>'');
					$sql ="
						INSERT INTO mt_db_pm_log
							( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
						VALUES
							( 'pm', '{$user['pm_code']}', 'stock', 0, '{$proc_id}', '{$proc_ip}' )
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


				if($site_info['auto_dist_yn'] == 'T'){
					$value = array(''=>'');
					$query = "select * from mt_member_team where use_yn = 'Y' AND dist_cnt != dist_cnt_now ORDER BY dist_cnt_now, dist_sort, idx limit 0,1";
					$insert_team = view_pdo($query, $value);

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
					$value = array(':idx'=>$idx);
					$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
					$pmCode = view_pdo($query, $value)['pm_code'];
					if($pmCode){
						excute("
							INSERT INTO mt_db_chart_log
								( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
							VALUES
								( 'pm', '{$pmCode}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
						");

						# 생산업체 잔여DB
						$nowDate = date("Y-m-d");
						$value = array(':pm_code'=>$pmCode);
						$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pm_code";
						$pmCodeStock = view_pdo($query, $value)['cnt'];
						$value = array(':code_value'=>$pmCode);
						$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
						$pmCodeStockIDX = view_pdo($query, $value)['idx'];
						if(!$pmCodeStockIDX){
							$value = array(''=>'');
							$sql="
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

					$value = array(''=>'');
					$query = "select count(*) as cnt from mt_member_team where use_yn = 'Y' AND dist_cnt != dist_cnt_now";
					$reset_cnt = view_pdo($query, $value)['cnt'];
					if($reset_cnt == 0){
						excute("UPDATE mt_member_team SET dist_cnt_now = 0 WHERE use_yn = 'Y'");
					}
				}

				if($site_info['auto_dist_yn'] == 'F'){
					$insert_team = $site_info['auto_dist_team'];
					$value = array(':tm_code'=>$insert_team);
					$query = "SELECT * FROM mt_member where use_yn = 'Y' AND tm_code = :tm_code AND dist_cnt != dist_cnt_now ORDER BY dist_cnt_now, dist_sort, idx limit 0,1";

					if($insert_team == '0000'){
						$value = array(''=>'');
						$query = "SELECT * FROM mt_member where use_yn = 'Y' AND dist_cnt != dist_cnt_now AND auth_code > 3 ORDER BY dist_cnt_now, dist_sort, idx limit 0,1";
					}

					$insert_fc = view_pdo($query, $value);

					$fc_cnt = $insert_fc['dist_cnt_now'] + 1;

					excute("
						UPDATE mt_db SET
							  dist_code = '002'
							, dist_date = now()
							, tm_code = '{$insert_team}'
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
							( '{$insert_team}', '{$insert_fc['idx']}', '{$idx}', '{$proc_id}', '{$proc_ip}' )
					");

					# 로그등록
					$value = array(':idx'=>$idx);
					$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
					$pmCode = view_pdo($query, $value)['pm_code'];
					if($pmCode){
						excute("
							INSERT INTO mt_db_chart_log
								( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
							VALUES
								( 'pm', '{$pmCode}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
						");

						# 생산업체 잔여DB
						$nowDate = date("Y-m-d");
						$value = array(':pm_code'=>$pmCode);
						$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pm_code";
						$pmCodeStock = view_pdo($query, $value)['cnt'];
						$value = array(':code_value'=>$pmCode);
						$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
						$pmCodeStockIDX = view_pdo($query, $value)['idx'];
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
							( 'tm', '{$insert_team}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
					");
					
					# 팀원별 분배기록
					excute("
						INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'fc', '{$insert_fc['idx']}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
					");	



					excute("UPDATE mt_member SET dist_cnt_now = '{$fc_cnt}' WHERE idx = '{$insert_fc['idx']}'");

					$value = array(':tm_code'=>$insert_team);
					$query = "SELECT count(*) as cnt from mt_member where use_yn = 'Y' AND tm_code = :tm_code AND dist_cnt != dist_cnt_now";
					if($insert_team == '0000'){
						$value = array(''=>'');
						$query = "SELECT count(*) as cnt from mt_member where use_yn = 'Y' AND auth_code > 3 AND dist_cnt != dist_cnt_now";
					}
					$reset_cnt = view_pdo($query, $value)['cnt'];
					if($reset_cnt == 0){
						excute("UPDATE mt_member SET dist_cnt_now = 0 WHERE use_yn = 'Y'");
					}
				}
				

				if($site_info['auto_dist_yn'] == 'P' && $pm_code){

					if($pm_module == 'Y'){


						$value = array(':pm_code'=>$pmCode);
						$query = "SELECT auto_dist_team FROM mt_member_cmpy WHERE idx = :pm_code";
						$insert_team = view_pdo($query, $value)['auto_dist_team'];


						if($insert_team == '0000'){
							$value = array(''=>'');
							$query = "SELECT *, (SELECT tm_code FROM mt_member WHERE idx = MT.m_idx) as tm_code FROM mt_member_pmDist MT where (SELECT use_yn FROM mt_member WHERE idx = MT.m_idx) = 'Y' AND dist_cnt != dist_cnt_now AND pm_code = {$pmCode} ORDER BY dist_cnt_now, dist_sort, idx limit 0,1";
						}else{
							$value = array(''=>'');
							$query = "SELECT *, (SELECT tm_code FROM mt_member WHERE idx = MT.m_idx) as tm_code FROM mt_member_pmDist MT where (SELECT use_yn FROM mt_member WHERE idx = MT.m_idx) = 'Y' AND dist_cnt != dist_cnt_now AND pm_code = {$pmCode} AND (SELECT tm_code FROM mt_member WHERE idx = MT.m_idx) = {$insert_team} ORDER BY dist_cnt_now, dist_sort, idx limit 0,1";
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
						$query = "SELECT count(*) as cnt from mt_member_pmDist MT where (SELECT use_yn FROM mt_member WHERE idx = MT.m_idx) = 'Y' AND dist_cnt != dist_cnt_now AND pm_code = {$pmCode} AND (SELECT tm_code FROM mt_member WHERE idx = MT.m_idx) = {$insert_team}";
						if($insert_team == '0000'){
							$value = array(''=>'');
							$query = "SELECT count(*) as cnt from mt_member_pmDist MT where (SELECT use_yn FROM mt_member WHERE idx = MT.m_idx) = 'Y' AND dist_cnt != dist_cnt_now AND pm_code = {$pmCode}";
						}
						$reset_cnt = view_pdo($query, $value)['cnt'];
						if($reset_cnt == 0){

							excute("UPDATE mt_member_pmDist MT SET dist_cnt_now = 0 WHERE (SELECT use_yn FROM mt_member WHERE idx = MT.m_idx) = 'Y' AND pm_code = {$pmCode}");
						}
							

					}else{
						$value = array(':idx'=>$pmCode);
						$query = "SELECT auto_dist_team FROM mt_member_cmpy WHERE idx = :idx";
						$insert_team = view_pdo($query, $value)['auto_dist_team'];
						$value = array(':idx'=>$insert_team);
						$query = "SELECT m_idx FROM mt_member_team WHERE idx = :idx";
						$insert_fc = view_pdo($query, $value)['m_idx'];

						if($insert_team){

							excute("
								UPDATE mt_db SET
									  dist_code = '002'
									, dist_date = now()
									, tm_code = '{$insert_team}'
									, m_idx = '{$insert_fc}'
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
									( '{$insert_team}', '{$insert_fc}', '{$idx}', '{$proc_id}', '{$proc_ip}' )
							");

							# 로그등록
							$value = array(':idx'=>$idx);
							$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
							$pmCode = view_pdo($query, $value)['pm_code'];
							if($pmCode){
								excute("
									INSERT INTO mt_db_chart_log
										( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
									VALUES
										( 'pm', '{$pmCode}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
								");

								# 생산업체 잔여DB
								$nowDate = date("Y-m-d");
								$value = array(':pm_code'=>$pmCode);
								$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pm_code";
								$pmCodeStock = view_pdo($query, $value)['cnt'];
								$value = array(':code_value'=>$pmCode);
								$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
								$pmCodeStockIDX = view_pdo($query, $value)['idx'];
								if(!$pmCodeStockIDX){
									$value = array(''=>'');
									$sql = "
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
									( 'tm', '{$insert_team}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
							");
							
							# 팀원별 분배기록
							excute("
								INSERT INTO mt_db_chart_log
									( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
								VALUES
									( 'fc', '{$insert_fc}', 'dist', '{$idx}', '{$proc_id}', '{$proc_ip}' )
							");	
						}
					}				
				}
			}

			$success++;
		} else {
			$failed['reason'] = "DB오류";
			// $failed['reason'] = $made_date;
			$failed['sql'] = $sql;
			array_push($failedData, $failed);
			$fail++;
		}
	
	}

	$result['data'] = $failedData;
	$result['success'] = number_format($success);
	$result['fail'] = number_format($fail);
	$result['total'] = number_format($success + $fail);

	header('Content-Type: application/json');
	echo json_encode($result);

?>