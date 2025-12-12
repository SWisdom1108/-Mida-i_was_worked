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

	$value = array(':type_name'=> $type_name, ':sort'=> $sort, ':use_yn'=> $use_yn, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip );

	$sql = "
		INSERT INTO mc_schedule_type
			( type_name, sort, reg_idx, reg_ip, use_yn )
		VALUES
			( :type_name, :sort, :proc_id, :proc_ip, :use_yn )
	";

	$exec = execute_pdo($sql, $value);

    if($exec['data']->rowCount() > 0){
		echo "success";
	} else {
		echo "fail";
	}

?>