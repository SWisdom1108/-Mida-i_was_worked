<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$block_yn = 'N';

	$value = array(':block_yn' => $block_yn, ':idx' => $idx);
	$sql ="UPDATE mt_login_block_ip SET block_yn = :block_yn, login_cnt = '1' WHERE idx = :idx";
	$exec = execute_pdo($sql, $value);

	if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>