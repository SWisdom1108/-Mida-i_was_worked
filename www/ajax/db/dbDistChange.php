<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	# 팀코드설정
	$value = array(':idx'=>$fcCode);
	$query = "SELECT tm_code FROM mt_member WHERE idx = :idx";
	$tmCode = view_pdo($query, $value)['tm_code'];

	$value = array(':tmCode'=>$tmCode, ':fcCode'=>$fcCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
	$query =  "
		UPDATE mt_db SET
			  tm_code = :tmCode
			, m_idx = :fcCode
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
			, order_by_date = now()
			, check_yn = 'N'
		WHERE idx IN ( ".implode(",", $idx)." )
	";
	$exce = execute_pdo($query,$value);

	if($exce['data']->rowCount() > 0){
		# 분배기록 등록
		foreach($idx as $val){
			$nowYM = date("Y-m"); # 월별일자
			$value = array(':db_idx'=>$val);
			$query = "SELECT status_code FROM mt_db_dist_log WHERE db_idx = :db_idx ORDER BY idx DESC";
			$dbCS = view_pdo($query, $value)['status_code'];
			$dbCS = ($dbCS) ? $dbCS : "000";
			excute("DELETE FROM mt_db_dist_log WHERE db_idx = '{$val}' AND reg_date LIKE '{$nowYM}%'");
			
			if($fcCode > 0){
				$value = array(':tmCode'=>$tmCode, ':fcCode'=>$fcCode, ':val'=>$val, ':dbCS'=>$dbCS, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
				$query = "
					INSERT INTO mt_db_dist_log
						( tm_code, m_idx, db_idx, status_code, reg_idx, reg_ip )
					VALUES
						( :tmCode, :fcCode, :val, :dbCS, :proc_id, :proc_ip )
				";
				execute_pdo($query, $value);

			}
			
			# 팀별 분배기록
			$value = array(':tmCode'=>$tmCode, ':val'=>$val, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query = "
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'tm', :tmCode, 'dist', :val, :proc_id, :proc_ip )
			";
			execute_pdo($query, $value);


			# 팀원별 분배기록
			$value = array(':fcCode'=>$fcCode, ':val'=>$val, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query = "
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'fc', :fcCode, 'dist', :val, :proc_id, :proc_ip )
			";
			execute_pdo($query, $value);

		}
		
		# 알림전송
		sendNotice("002", $fcCode, "새로운 DB가 분배되었습니다.", "/sub/db/dbMyL");
		
		# 200924 SMS전송
		$value = array(''=>'');
		$query = "SELECT * FROM mt_sms_template WHERE idx = '2'";
		$templateInfo = view_pdo($query, $value);
		if($templateInfo["use_yn"] == "Y"){
			$value = array(':idx'=>$fcCode);
			$query = "SELECT * FROM mt_member WHERE idx = :idx";
			$templateUserInfo = view_pdo($query, $value);
			$templateInfo["contents"] = str_replace("#{COMPANY_NAME}", $mainCmpy["company_name"], $templateInfo["contents"]);
			$templateInfo["contents"] = str_replace("#{NAME}", $templateUserInfo["m_name"], $templateInfo["contents"]);
			$templateInfo["contents"] = str_replace("#{DATE}", date("Y-m-d H:i:s"), $templateInfo["contents"]);

			if($templateUserInfo["m_tel"]){
				$smsResult = smsSend($templateUserInfo["m_tel"], $templateInfo["contents"]);
				if($smsResult["msg"] == "success"){
					excute("
						INSERT INTO mt_sms_log 
							( send_name, send_tel, contents, receive_name, receive_tel, result_code, result_msg, result_id, reg_idx, reg_ip )
						VALUES 
							( '{$smsResult['send_name']}', '{$smsResult['send_tel']}', '{$templateInfo["contents"]}', '{$templateUserInfo["m_name"]}', '{$templateUserInfo["m_tel"]}', '-', '-', '{$smsResult["Msg_Id"]}', '{$proc_id}', '{$proc_ip}'  )
					");
				}
			}
		}
		
		echo "success";
	}  else {
		echo "fail";
	}

?>