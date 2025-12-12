<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$contents = ehtml($contents);

	$value = array(':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':title'=> $title, ':contents'=> $contents, ':use_yn'=> $use_yn, ':idx'=> $idx );

	$sql = "
		UPDATE mt_sms_template SET
			  title = :title
			, contents = :contents
			, use_yn = :use_yn
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx = :idx
	";

	$exec = execute_pdo($sql, $value);

    if($exec['data']->rowCount() > 0){
		echo "success";
	} else {
		echo "fail";
	}

?>