<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$use_yn = ($use_yn) ? "Y" : "N";
	$finish_yn = ($finish_yn) ? "Y" : "N";
	$number_yn = ($number_yn) ? "Y" : "N";

	$value = array(':status_name'=>$status_name, ':edit_idx'=>$proc_id, ':edit_ip'=>$proc_ip, ':use_yn'=>$use_yn, ':finish_yn'=>$finish_yn, ':number_yn'=>$number_yn, ':number_label'=>$number_label, ':color'=>$color, ':status_code'=>$code);
	$query = "
		UPDATE mc_db_cs_status SET
			  status_name = :status_name
			, edit_idx = :edit_idx
			, edit_ip = :edit_ip
			, edit_date = now()
			, use_yn = :use_yn
			, finish_yn = :finish_yn
			, number_yn = :number_yn
			, number_label = :number_label
			, color = :color
		WHERE status_code = :status_code
	";

	$exec = execute_pdo($query, $value);
	if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>