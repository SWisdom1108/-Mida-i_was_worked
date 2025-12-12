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
	$e_date = "{$date} {$e_time}:00";
	$memo = ehtml($memo);
	$andColumn = "";
	$andValue = "";

	$tm_code = ($user["tm_code"]) ? $user["tm_code"] : 0;

	switch($user["auth_code"]){
		case "001" :
		case "002" :
			$share_all_yn = ($share_all_yn) ? "Y" : "N";
			
			$andColumn .= ", share_all_yn";
			$andValue .= ", '{$share_all_yn}'";
			break;
		case "004" :
			$share_tm_yn = ($share_tm_yn) ? "Y" : "N";
			
			$andColumn .= ", share_tm_yn";
			$andValue .= ", '{$share_tm_yn}'";
			break;
	}

	if(isset($cs_name)) {
		$andColumn .= ", cs_name";
		$andValue .= ", '{$cs_name}'";
	}

	if(isset($cs_tel)) {
		$andColumn .= ", cs_tel";
		$andValue .= ", '{$cs_tel}'";
	}

	if(!empty($db_idx)) {
		$andColumn .= ", db_idx";
		$andValue .= ", '{$db_idx}'";
	}

	if($noti_yn == 'on') {
		$noti_yn = 'Y';
		$noti_send_yn = 'N';
	} else {
		$noti_yn = 'N';
	}

	$value = array(':type_code'=>$type_code, ':tm_code'=>$tm_code, ':s_date'=>$s_date, ':e_date'=>$e_date, ':memo'=>$memo,':noti_yn'=>$noti_yn,':noti_time'=>$noti_time,':noti_send_yn'=>$noti_send_yn, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
	$sql = "
		INSERT INTO mt_schedule
			( schedule_type, type_code, tm_code, s_date, e_date, memo, noti_yn, noti_time, noti_send_yn, reg_idx, reg_ip {$andColumn} )
		VALUES
			( 'basic', :type_code, :tm_code, :s_date, :e_date, :memo, :noti_yn, :noti_time, :noti_send_yn, :proc_id, :proc_ip {$andValue} )
	";
	$exec = execute_pdo($sql, $value);

	if($exec['data'] > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>