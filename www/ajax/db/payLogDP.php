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
	$query = "UPDATE mt_pay_log SET use_yn = 'N' WHERE idx = :idx";
	$exec = execute_pdo($query, $value);

	if( $exec['data']->rowCount() > 0 ){
		echo "success";
	}  else {
		echo "fail";
	}

?>