<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$name = $_POST["name"];
	$tel = $_POST["tel"];
	$types = $_POST["type"];
	$result = [];


	$view = view_sql("SELECT * FROM mt_sms_tel WHERE idx = '{$send_tel}' AND use_yn = 'Y'");
	// $value = array(':send_tel'=>$send_tel);
	// $query = "SELECT sent_tel FROM mt_sms_tel WHERE idx = :send_tel AND use_yn = 'Y'";

	// $view = view_pdo($query, $value);


	

	$result["totalCnt"] = 0;
	$result["successCnt"] = 0;
	$result["failCnt"] = 0;

	foreach($tel as $key => $receive_tel){
		$result["totalCnt"]++;
		$receive_name = $name[$key];
		$cs_memo = $types[$key];
		
		$contents = str_replace("#{고객명}", $receive_name, $_POST["contents"]);
		$contents_r = ehtml($contents);
		$smsResult = smsSend($receive_tel, $contents, $view['sent_tel']);
		if($smsResult["msg"] == "success"){

			$value = array(':contents_r'=>$contents_r,':receive_name'=>$receive_name,':receive_tel'=>$receive_tel,':proc_id'=>$proc_id,':proc_ip'=>$proc_ip);
			$query = "
				INSERT INTO mt_sms_log 
					( send_name, send_tel, contents, receive_name, receive_tel, result_code, result_msg, result_id, reg_idx, reg_ip )
				VALUES 
					( '{$smsResult['send_name']}', '{$smsResult['send_tel']}', :contents_r, :receive_name, :receive_tel, '-', '-', '{$smsResult["Msg_Id"]}', :proc_id, :proc_ip  )

			";
			execute_pdo($query, $value);


			# 210201 SMS 상담기록 남기도록
			$smsCheckTel = str_replace("-", "", $receive_tel);
			$dbInfo = view_sql("select idx from mt_db where replace(cs_tel, '-', '') = '{$smsCheckTel}' limit 0,1");
			$status_code = view_sql("select status_code from mc_db_cs_status where sms_yn = 'Y' order by status_code desc limit 0,1")['status_code'];
			if ( $dbInfo['idx'] ){

				$value = array(':status_code'=>$status_code,':cs_memo'=>$cs_memo,':proc_id'=>$proc_id,':proc_ip'=>$proc_ip);
				$sql = "
					INSERT INTO mt_db_cs_log
						( db_idx, status_code, memo, reg_idx, reg_ip )
					VALUES
						( '{$dbInfo['idx']}', :status_code, :cs_memo, :proc_id, :proc_ip )
				";
				execute_pdo($sql, $value);
			}

			
			$result["successCnt"]++;
		} else {
			$result["failCnt"]++;
		}
	}

	# 결과추출
	$result["totalCnt"] = number_format($result["totalCnt"]);
	$result["successCnt"] = number_format($result["successCnt"]);
	$result["failCnt"] = number_format($result["failCnt"]);

	header('Content-Type: application/json');
	echo json_encode($result);

?>