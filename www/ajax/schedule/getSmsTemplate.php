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
	$query = "SELECT * FROM mt_sms_template WHERE idx = :idx";
	$view = view_pdo($query, $value);

	echo $view["contents"];

?>