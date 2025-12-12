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

	if(!$file['type']){
		echo "return upload";
		return false;
	}

	# 컬럼 정리
	$lastCnt = 3;
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
		
		$made_date = dhtml_script(trim($datas->val($i,2)));
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

			$value = array(':pm_code'=>$user['pm_code']);
			$query = "SELECT * FROM mt_db WHERE use_yn = 'Y' AND cs_tel in ('{$cs_tel}', '{$checkTel}', '{$checkTel2}', '{$checkTel3}', '{$checkTel4}') AND pm_code = :pm_code {$checkAndQuery}";
			$overDB = view_pdo($query, $value);
			if($overDB){
				$overlap_yn = "Y";
			}

			// $overDB = view_sql("SELECT * FROM mt_db WHERE use_yn = 'Y' AND cs_tel = '{$cs_tel}' AND pm_code = '{$user['pm_code']}' {$checkAndQuery}");
			// if($overDB){
			// 	$failed['reason'] = "중복{$customLabel["cs_tel"]}";
			// 	array_push($failedData, $failed);
			// 	$fail++;
			// 	continue;
			// }
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
		$sql = "
			INSERT INTO mt_db
				( save_type, made_date, pm_code, cs_name, cs_tel, reg_idx, reg_ip {$andColumns}, overlap_yn )
			VALUES
				( 'excel', '{$made_date}', '{$user['pm_code']}', '{$cs_name}', '{$cs_tel}', '{$proc_id}', '{$proc_ip}' {$andValues}, '{$overlap_yn}' )
		";

		if(excute($sql) > 0){
			$idx = mysqli_insert_id($conn);
			
			# 로그등록
			excute("
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'pm', '{$user['pm_code']}', 'upload', '{$idx}', '{$proc_id}', '{$proc_ip}' )
			");
			
			# 생산업체 잔여DB
			$nowDate = date("Y-m-d");
			$value = array(':pm_code'=>$user['pm_code']);
			$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pm_code";
			$pmCodeStock = view_pdo($query, $value)['cnt'];
			$value = array(':code_value'=>$user['pm_code']);
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
			
			$success++;
		} else {
			$failed['reason'] = "DB오류";
			array_push($failedData, $failed);
			$fail++;
		}
	}

	$result['data'] = $failedData;
	$result['success'] = number_format($success);
	$result['fail'] = number_format($fail);
	$result['total'] = number_format($success + $fail);

	# 200924 SMS전송
	if($success){
		$value = array(''=>'');
		$query = "SELECT * FROM mt_sms_template WHERE idx = '1'";
		$templateInfo = view_pdo($query, $value);
		if($templateInfo["use_yn"] == "Y"){
			$value = array(''=>'');
			$query = "SELECT * FROM mt_member WHERE use_yn = 'Y' AND auth_code IN ( 001, 002 )";
			$sql = list_pdo($query, $value);
			while($row = $sql->fetch(PDO::FETCH_ASSOC)){
				$templateInfo["contents"] = str_replace("#{COMPANY_NAME}", $mainCmpy["company_name"], $templateInfo["contents"]);
				$templateInfo["contents"] = str_replace("#{PM_NAME}", $cmpy["company_name"], $templateInfo["contents"]);
				$templateInfo["contents"] = str_replace("#{CNT}", $result['success'], $templateInfo["contents"]);
				$templateInfo["contents"] = str_replace("#{DATE}", date("Y-m-d H:i:s"), $templateInfo["contents"]);

				if($row["m_tel"]){
					$smsResult = smsSend($row["m_tel"], $templateInfo["contents"]);
					if($smsResult["msg"] == "success"){
						excute("
							INSERT INTO mt_sms_log 
								( send_name, send_tel, contents, receive_name, receive_tel, result_code, result_msg, result_id, reg_idx, reg_ip )
							VALUES 
								( '{$smsResult['send_name']}', '{$smsResult['send_tel']}', '{$templateInfo["contents"]}', '{$row["m_name"]}', '{$row["m_tel"]}', '-', '-', '{$smsResult["Msg_Id"]}', '{$proc_id}', '{$proc_ip}'  )
						");
					}
				}
			}
		}
	}

	header('Content-Type: application/json');
	echo json_encode($result);

?>