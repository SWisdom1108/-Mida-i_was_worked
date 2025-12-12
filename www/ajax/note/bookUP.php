<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$memo = ehtml($memo);
	$value = array(':idx'=>$idx);
	$query = "SELECT * FROM mt_note_book WHERE idx = :idx";
	$view = view_pdo($query, $value);

	if(!$memo){
		$value = array(':idx'=>$view['m_idx']);
		$query = "SELECT m_name FROM mt_member WHERE idx = :idx";
		$memo = view_pdo($query, $value)['m_name'];
	}

	$value = array(':memo'=>$memo, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx, ':reg_idx'=>$user['idx'] );

	$query = "
		UPDATE mt_note_book SET
			  memo = :memo
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx = :idx
		AND reg_idx = :reg_idx
	";
	$exec = execute_pdo($query, $value);

	if($exec['data']->rowCount() > 0){
		echo dhtml($memo);
	} else {
		echo "fail";
	}

?>