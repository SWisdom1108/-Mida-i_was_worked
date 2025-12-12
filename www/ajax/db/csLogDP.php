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

	$query2 = view_sql("SELECT status_code FROM mt_db_cs_log WHERE idx = {$idx}");
	$query3 = "DELETE FROM mt_schedule WHERE log_idx = :idx";
	$exec2 = execute_pdo($query3, $value);

	$query = "DELETE FROM mt_db_cs_log WHERE idx = :idx";
	$exec = execute_pdo($query, $value);

	if( $exec['data']->rowCount() > 0 ){
		echo "success";
	}  else {
		echo "fail";
	}

?>