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
	$query = "
		UPDATE mt_db SET
			  dist_code = '002'
			, dist_date = now()
			, tm_code = :tmCode
			, m_idx = :fcCode
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
			, order_by_date = now()
		WHERE idx IN ( ".implode(",", $idx)." )
	";
	$exec = execute_pdo($query, $value);



	if($exec['data']->rowCount() > 0){
		
		# 분배기록 등록
		foreach($idx as $val){

			$value2 = array(':tmCode'=>$tmCode, ':fcCode'=>$fcCode, ':val'=>$val, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query2 = "
				INSERT INTO mt_db_dist_log
					( tm_code, m_idx, db_idx, reg_idx, reg_ip )
				VALUES
					( :tmCode, :fcCode, :val, :proc_id, :proc_ip )
			";
			execute_pdo($query2, $value2);
			
			$value = array(':idx'=>$val);
			$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
			$pmCode = view_pdo($query, $value)['pm_code'];
			if($pmCode){

				$value3 = array(':pmCode'=>$pmCode, ':val'=>$val, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
				$query3 = "
					INSERT INTO mt_db_chart_log
						( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
					VALUES
						( 'pm', :pmCode, 'dist', :val, :proc_id, :proc_ip )
				";
				execute_pdo($query3, $value3);



				
				# 생산업체 잔여DB
				$nowDate = date("Y-m-d");
				$value = array(':pm_code'=>$pmCode);
				$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pm_code";
				$pmCodeStock = view_pdo($query, $value)['cnt'];
				$value = array(':code_value'=>$pmCode);
				$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
				$pmCodeStockIDX = view_pdo($query, $value)['idx'];
				if(!$pmCodeStockIDX){
					$value4 = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
					$query4 = "
						INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
						VALUES
							( 'pm', '{$pmCode}', 'stock', 0, '{$proc_id}', '{$proc_ip}' )
					";
					
					$pmCodeStockIDX = $exec['insertIdx'];


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
			}
			
			# 팀별 분배기록
			$value5 = array(':tmCode'=>$tmCode, ':val'=>$val, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query5 = "
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'tm', :tmCode, 'dist', :val, :proc_id, :proc_ip )
			";
			execute_pdo($query5, $value5);
			

			# 팀원별 분배기록
			$value6 = array(':fcCode'=>$fcCode, ':val'=>$val, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query6 = "
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'fc', :fcCode, 'dist', :val, :proc_id, :proc_ip )
			";
			execute_pdo($query6, $value6);
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