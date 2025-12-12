<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php include $_SERVER['DOCUMENT_ROOT']."/plugin/excel/excel_reader2.php"; ?>
<?php

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
	$datas = new Spreadsheet_Excel_Reader($excelFile);

	$rowcount = $datas->rowcount($sheet_index=0);
	$colcount = $datas->colcount($sheet_index=0);
	for($i = 2; $i <= $rowcount; $i++){
		$failed = [];

		$andColumns = "";
		$andValues = "";
		
		$pmCode = dhtml_script(trim($datas->val($i,1)));
		$made_date = dhtml_script(trim($datas->val($i,2)));
		// $made_date = PHPExcel_Style_NumberFormat::toFormattedString($made_date, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
		$made_date = ($made_date) ? $made_date : date("Y-m-d");
		$cs_name = dhtml_script(trim($datas->val($i,3)));
		$cs_tel = dhtml_script(trim($datas->val($i,4)));
		
		# 실패데이터 미리 저장
		$failed['made_date'] = $made_date;
		$failed['cs_name'] = $cs_name;
		$failed['cs_tel'] = $cs_tel;
		
		foreach($columnArr as $index => $val){
			${$val['code']} = trim($datas->val($i,5 + $index));
			$failed[$val['code']] = ${$val['code']};
			
			$andColumns .= ", {$val['code']}";
			$andValues .= ", '{${$val['code']}}'";
		}
		
		# 한 행 데이터 체크
		$forStatus = true;
		for($ii = 1; $ii < ($lastCnt + 1); $ii++){
			if(trim($datas->val($i, $ii))){
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
			$value = array(''=>'');
			$query = "SELECT * FROM mt_db WHERE use_yn = 'Y' AND replace(cs_tel, '-', '') = '{$checkTel}' {$checkAndQuery}";
			$overDB = view_pdo($query, $value);
			if($overDB){
				$overlap_yn = "Y";
			}

		}
		
		$chkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);
		$value = array(''=>'');
		$query = "SELECT * FROM mt_block_tel";
		$blackData = list_pdo($query, $value);
		while($row = $blackData->fetch(PDO::FETCH_ASSOC)){
			if($row['block_tel'] == $chkTel){
				$failed['reason'] = "블랙리스트";
				array_push($failedData, $failed);
				$fail++;
				continue;
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

		

		$sql = "
			INSERT INTO mt_db
				( pm_code, made_date, cs_name, cs_tel, reg_idx, reg_ip {$andColumns}, overlap_yn )
			VALUES
				( '{$pmCode}', '{$made_date}', '{$cs_name}', '{$cs_tel}', '{$proc_id}', '{$proc_ip}' {$andValues}, '{$overlap_yn}' )
		";

		if(excute($sql) > 0){

			$idx = mysqli_insert_id($conn);
			
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
				excute("
					INSERT INTO mt_db_pm_log
						( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
					VALUES
						( 'pm', '{$user['pm_code']}', 'stock', 0, '{$proc_id}', '{$proc_ip}' )
				");

				$pmCodeStockIDX = mysqli_insert_id($conn);
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
						excute("
							INSERT INTO mt_db_chart_log
								( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
							VALUES
								( 'pm', '{$pmCode}', 'stock', 0, '{$proc_id}', '{$proc_ip}' )
						");

						$pmCodeStockIDX = mysqli_insert_id($conn);
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
						excute("
							INSERT INTO mt_db_chart_log
								( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
							VALUES
								( 'pm', '{$pmCode}', 'stock', 0, '{$proc_id}', '{$proc_ip}' )
						");

						$pmCodeStockIDX = mysqli_insert_id($conn);
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
				$reset_cnt = view_pdo($query, $value)['cnt'];
				if($reset_cnt == 0){
					excute("UPDATE mt_member SET dist_cnt_now = 0 WHERE use_yn = 'Y'  AND tm_code = '{$insert_team}'");
				}
			}

			if($site_info['auto_dist_yn'] == 'P'){
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
							excute("
								INSERT INTO mt_db_chart_log
									( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
								VALUES
									( 'pm', '{$pmCode}', 'stock', 0, '{$proc_id}', '{$proc_ip}' )
							");

							$pmCodeStockIDX = mysqli_insert_id($conn);
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