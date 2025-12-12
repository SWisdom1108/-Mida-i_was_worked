<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$value = array(':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip);
	$query = "UPDATE mt_db_dent
        SET use_yn = 'N'
        , edit_idx = :proc_id
		, edit_ip = :proc_ip
		, edit_date = now()
        WHERE idx IN ($idx)
	";
	$exec = execute_pdo($query, $value);

	if($exec['data']->rowCount() > 0){
		
		echo "success";
	}  else {
		echo "fail";
	}

?>