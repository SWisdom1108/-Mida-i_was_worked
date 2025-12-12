<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	$value = array(':idx'=>$_POST['idx']);
	$query = "SELECT * FROM mt_bbs_comment WHERE idx = :idx";
	$view = view_pdo($query, $value);

	if($user['idx'] != $view['reg_idx']){
		return false;
	}
	
	$query = "
		UPDATE mt_bbs_comment SET
			use_yn = 'N'
		WHERE idx = :idx
	";

	$exec = execute_pdo($query, $value);
	if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>