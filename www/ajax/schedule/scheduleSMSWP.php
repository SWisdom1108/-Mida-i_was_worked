<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$s_date = "{$date} {$s_time}:00";
	$e_date = "{$date} {$s_time}:00";
	$memo = str_replace("#{고객명}", $cs_name, $memo);
	$memo_r = ehtml($memo);
	$value = array(''=>'');
	$query = "SELECT sent_tel FROM mt_sms_tel WHERE use_yn = 'Y' AND main_yn = 'Y'";
	$send_tel = view_pdo($query, $value)["sent_tel"];

	if((strtotime($s_date) - strtotime(date("Y-m-d H:i:00"))) < 300){
		echo "발송시간은 현재시간 기준으로 하여 5분이상부터 등록이 가능합니다.";
		return false;
	}

	$value = array(':s_date'=>$s_date, ':e_date'=>$e_date, ':cs_name'=>$cs_name, ':cs_tel'=>$cs_tel, ':memo_r'=>$memo_r, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);

	$query = "		INSERT INTO mt_schedule
			( schedule_type, s_date, e_date, cs_name, cs_tel, memo, reg_idx, reg_ip )
		VALUES
			( 'sms', :s_date, :e_date, :cs_name, :cs_tel, :memo_r, :proc_id, :proc_ip )
	";

	$exec = execute_pdo($query,$value);


	if($exec['data']-> rowCount() > 0){
		$idx = $exec['insertIdx'];

		$smsResult = smsSend($cs_tel, $memo, $send_tel, $s_date);
		if($smsResult["msg"] == "success"){


			$value = array(':memo_r'=>$memo_r, ':cs_name'=>$cs_name, ':cs_tel'=>$cs_tel, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query = "
				INSERT INTO mt_sms_log 
					( send_name, send_tel, contents, receive_name, receive_tel, result_code, result_msg, result_id, reg_idx, reg_ip )
				VALUES 
					( '{$smsResult['send_name']}', '{$smsResult['send_tel']}', :memo_r, :cs_name, :cs_tel, '-', '-', '{$smsResult["Msg_Id"]}', :proc_id, :proc_ip  )
			";
			execute_pdo($query, $value);


			$value2 = array(':idx'=>$idx);
			$query2 = "
				UPDATE mt_schedule SET	
					msg_id = '{$smsResult["Msg_Id"]}'
				WHERE idx = :idx
			";
			execute_pdo($query2, $value2);

			# 210201 SMS 상담기록 남기도록
			$smsCheckTel = str_replace("-", "", $cs_tel);
			$value = array(''=>'');
			$query = "select idx from mt_db where replace(cs_tel, '-', '') = '{$smsCheckTel}' limit 0,1";
			$dbInfo = view_pdo($query, $value);
			$value = array(''=>'');
			$query = "select status_code from mc_db_cs_status where sms_yn = 'Y' order by status_code desc limit 0,1";
			$status_code = view_pdo($query, $value)['status_code'];
			if ( $dbInfo['idx'] ){

				$value3 = array(':status_code'=>$status_code, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
				$query3 = "					
					INSERT INTO mt_db_cs_log
						( db_idx, status_code, memo, reg_idx, reg_ip )
					VALUES
						( '{$dbInfo['idx']}', :status_code, 'SMS예약발송', :proc_id, :proc_ip )
				";
				execute_pdo($query3, $value3);
			}

			echo "success";
		} else {
			echo "일정 등록이 완료되었습니다. \n발신번호 혹은 잔여 문자수가 존재하지 않아 SMS 전송에는 실패하였습니다.";
		}
	} else {
		echo "fail";
	}

?>