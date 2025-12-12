<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	$value = array(':use_yn'=>'N', ':idx'=>$_POST['idx'], ':reg_idx'=>$user['idx'], ':bbs_code'=>$_POST['bbs']);
	$query = "
		UPDATE mt_bbs SET
			use_yn = :use_yn
		WHERE idx = :idx
		AND reg_idx = :reg_idx
		AND bbs_code = :bbs_code
	";

	$exec = execute_pdo($query, $value);
	if($exec['data']->rowCount() > 0){
		echo "success";
	} else{
		echo "fail";
	}

?>