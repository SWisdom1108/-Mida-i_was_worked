<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$v_value = array(':idx'=>$idx);
	$query =  "SELECT grade_code FROM mt_db WHERE grade_code = :idx";
	$db_check = view_pdo($query, $v_value);
	
	if($db_check){
		echo "고객등급이 부여된 고객이 존재해서 삭제가 불가합니다.";
		return false;
	}

	$value = array(':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx);
	$query = "
		UPDATE mc_db_grade_info SET
			  use_yn = 'N'
			,  del_yn = 'Y'
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE grade_code = :idx
	";
	$exec = execute_pdo($query, $value);

	if($exec['data'] -> rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>