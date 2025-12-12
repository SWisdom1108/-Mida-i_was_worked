<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$value = array(':login_block' => 'N', ':login_cnt' => '1', ':listCheckData' => $_COOKIE['listCheckData']);
	$query = "
		UPDATE mt_member SET
			  login_block = :login_block
			, login_cnt = :login_cnt
		WHERE idx IN ( :listCheckData )
	";

	$exec = execute_pdo($query, $value);

	if($exec['data']->rowCount() > 0){		
		echo "success";
	}  else {
		echo "fail";
	}

?>