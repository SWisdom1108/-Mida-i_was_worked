<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$value = array(':noti_send_yn'=>'Y');
	
	$sql = "
		UPDATE mt_schedule SET
			  noti_send_yn = :noti_send_yn
			, edit_date = now()
		WHERE idx IN ({$idx}) 
	";
	$exec = execute_pdo($sql, $value);

	if($exec['data']-> rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>