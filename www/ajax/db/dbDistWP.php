<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$andColumns = "";
	$andValues = "";
	$cs_name = ehtml($_POST['cs_name']);

	# 컬럼 정리
	$value = array(''=>'');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = 'Y'
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);
	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		if ( gettype($_POST[$row['column_code']]) == "array" ){
			$data = implode(",", $_POST[$row['column_code']]);

			if ( $row['column_code'] == "cs_etc03" ){
				$data = implode("@", $_POST[$row['column_code']]);
			}

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
	$value = array(''=>'');
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
		$value = array(':pm_code'=>$user['pm_code']);
		$query = "SELECT * FROM mt_db WHERE use_yn = 'Y' AND replace(cs_tel, '-', '') = '{$checkTel}' AND pm_code = :pm_code {$checkAndQuery}";
		$overDB = view_pdo($query, $value);
		if($overDB){
			$overlap_yn = "Y";
		}
		
		// $overDB = view_sql("SELECT * FROM mt_db WHERE use_yn = 'Y' AND cs_tel = '{$cs_tel}' AND pm_code = '{$user['pm_code']}' {$checkAndQuery}");
		// if($overDB){
		// 	echo "이미 등록된 {$customLabel["cs_tel"]}입니다.";
		// 	return false;
		// }
	}

	$value = array(':made_date'=>$made_date, ':cs_name'=>$cs_name, ':cs_tel'=>$cs_tel, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':overlap_yn'=>$overlap_yn);
	$query = "
		INSERT INTO mt_db
			( made_date, pm_code, cs_name, cs_tel, reg_idx, reg_ip {$andColumns}, overlap_yn )
		VALUES
			( :made_date, '{$user['pm_code']}', :cs_name, :cs_tel, :proc_id, :proc_ip {$andValues}, :overlap_yn )
	";
	$exec = execute_pdo($query, $value);


	if($exec['data']->rowCount() > 0){
		$idx = $exec['insertIdx'];

		# 로그등록
		$value2 = array(':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
		$query2 = "
			INSERT INTO mt_db_chart_log
				( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
			VALUES
				( 'pm', '{$user['pm_code']}', 'upload', :idx, :proc_id, :proc_ip )
		";
		execute_pdo($query2, $value2);

		# 생산업체 잔여DB
		$nowDate = date("Y-m-d");
		$value = array(':pm_code'=>$user['pm_code']);
		$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pm_code";
		$pmCodeStock = view_pdo($query, $value)['cnt'];
		// $value = array(':pm_code'=>$user['pm_code']); // pm_code가 테이블에 없음
		$query = "SELECT idx FROM mt_db_chart_log WHERE type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
		$pmCodeStockIDX = view_pdo($query, $value)['idx'];
		if(!$pmCodeStockIDX){
			$value3 = array(':proc_id'=>$proc_id,':proc_ip'=>$proc_ip);
			$query3 = "
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
				VALUES
					( '{$user['pm_code']}', 'stock', 0, :proc_id, :proc_ip )
			";
			$exec2 = execute_pdo($query3, $value3);
			$pmCodeStockIDX = $exec2['insertIdx'];
		}


			$value4 = array(':pmCodeStock'=>$pmCodeStock, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':pmCodeStockIDX'=>$pmCodeStockIDX);
			$query4 = "
				UPDATE mt_db_chart_log SET
					  db_cnt = :pmCodeStock
					, edit_idx = :proc_id
					, edit_ip = :proc_ip
					, edit_date = now()
				WHERE idx = :pmCodeStockIDX
			";
			execute_pdo($query4, $value4);

		# 200924 SMS전송
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
				$templateInfo["contents"] = str_replace("#{CNT}", 1, $templateInfo["contents"]);
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
		
		echo "success";
	}  else {
		echo "fail";
	}

?>