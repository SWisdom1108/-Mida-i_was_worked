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
	$contents = ehtml($contents);

	$value = array(':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':title'=> $title, ':contents'=> $contents, ':use_yn'=> $use_yn );

	$sql = "
		INSERT INTO mt_sms_template
			( title, contents, reg_idx, reg_ip, use_yn )
		VALUES
			( :title, :contents, :proc_id, :proc_ip, :use_yn )
	";

	$exec = execute_pdo($sql, $value);

    if($exec['data']->rowCount() > 0){
		echo "success";
	} else {
		echo "fail";
	}

?>