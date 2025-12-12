<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$use_yn = ($use_yn) ? "Y" : "N";

	$value = array(':type_name'=> $type_name, ':sort'=> $sort, ':use_yn'=> $use_yn, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':code'=> $code);

	$sql = "
		UPDATE mc_schedule_type SET
			  type_name = :type_name
			, sort = :sort
			, use_yn = :use_yn
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE type_code = :code
	";

	$exec = execute_pdo($sql, $value);

    if($exec['data']->rowCount() > 0){
		echo "success";
	} else {
		echo "fail";
	}

?>