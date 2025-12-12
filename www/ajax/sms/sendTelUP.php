<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$main_yn = ($main_yn) ? "Y" : "N";
	$use_yn = ($use_yn) ? "Y" : "N";
	$sent_name = ehtml($sent_name);

	$value = array(':sent_name'=>$sent_name, ':main_yn'=>$main_yn, ':use_yn'=>$use_yn, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx);
	$sql = "
		UPDATE mt_sms_tel SET
			  sent_name = :sent_name
			, main_yn = :main_yn
			, use_yn = :use_yn
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx = :idx
	";
	$exec = execute_pdo($sql, $value);


	if($exec['data']->rowCount() > 0 ){
		
		if($main_yn == "Y"){
			$value = array(':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx);
			$query ="
				UPDATE mt_sms_tel SET
					  main_yn = 'N'
					, edit_idx = :proc_id
					, edit_ip = :proc_ip
					, edit_date = now()
				WHERE idx != :idx
			";
			execute_pdo($query, $value);
		}
		
		echo "success";
	} else {
		echo "fail";
	}

?>