<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$md_idx = (!empty($md_idx)) ? $md_idx : null;
	$dr_idx = (!empty($dr_idx)) ? $dr_idx : null; 

	$value = array(':md_idx'=>$md_idx, ':dr_idx'=>$dr_idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx);
	$sql = "
		UPDATE mt_db_dent SET
            md_idx = :md_idx
            , dr_idx = :dr_idx
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx =:idx
	";

	$exec = execute_pdo($sql, $value);

	if( $exec['data']->rowCount() > 0 ){
		echo "success";
	}  else {
		echo "fail";
	}

?>