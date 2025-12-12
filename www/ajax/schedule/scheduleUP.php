<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$value = array(':idx'=>$idx);
	$query = "SELECT s_date FROM mt_schedule WHERE use_yn = 'Y' AND idx = :idx";
	// $date = view_pdo($query, $value)["s_date"];
	// $date = date("Y-m-d", strtotime($date));
	$s_date = "{$date} {$s_time}:00";
	$e_date = "{$date} {$e_time}:00";
	$memo = ehtml($memo);
	$andQuery = "";

	
	switch($user["auth_code"]){
		case "001" :
			case "002" :
				$share_all_yn = ($share_all_yn) ? "Y" : "N";
				
				$andQuery .= ", share_all_yn = '{$share_all_yn}'";
				break;
				case "004" :
					$share_tm_yn = ($share_tm_yn) ? "Y" : "N";
					
			$andQuery .= ", share_tm_yn = '{$share_tm_yn}'";
			break;
		}
		
		if($noti_yn == 'on') {
		$noti_yn = 'Y';
		$noti_send_yn = 'N';
	} else {
		$noti_yn = 'N';
	}
	
	if($call_yn == 'on') {
		$call_yn = 'Y';
	} else {
		$call_yn = 'N';
	}

	$value = array(':type_code'=>$type_code, ':s_date'=>$s_date, ':e_date'=>$e_date, ':memo'=>$memo,':noti_yn'=>$noti_yn,':noti_time'=>$noti_time,':noti_send_yn'=>$noti_send_yn, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx, ':call_yn'=> $call_yn );
	$sql = "
		UPDATE mt_schedule SET
			  type_code = :type_code
			, s_date = :s_date
			, e_date = :e_date
			, memo = :memo
			, noti_yn = :noti_yn
			, noti_time = :noti_time
			, noti_send_yn = :noti_send_yn
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
			, call_yn = :call_yn
			{$andQuery}
		WHERE idx = :idx
	";
	$exec = execute_pdo($sql,$value);

	$value = array(':idx'=>$idx);
	$query2 = "SELECT log_idx FROM mt_schedule WHERE use_yn = 'Y' AND idx = :idx";
	$log_idx = view_pdo($query2, $value)["log_idx"];

	if ($log_idx) {
		$value = array(':log_idx'=>$log_idx, ':memo'=>$memo, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
		$sql2 = "
			UPDATE mt_db_cs_log SET
				  memo = :memo
				, edit_idx = :proc_id
				, edit_ip = :proc_ip
				, edit_date = now()
			WHERE idx = :log_idx
		";
		$exec2 = execute_pdo($sql2, $value);
	}

	if($exec['data']-> rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>