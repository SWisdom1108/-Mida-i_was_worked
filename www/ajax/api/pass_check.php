<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();


	$value = array(':password'=> $password);
	$query ="
		select * from mt_member where m_pw = password(:password) AND idx = {$user['idx']}
	";
	$view = view_pdo($query, $value);

	if($view['idx']){
		echo "success";
	}  else {
		echo "비밀번호가 일치하지 않습니다.";
	}

?>