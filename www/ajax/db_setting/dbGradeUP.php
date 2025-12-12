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
	$grade_name = ehtml($grade_name);
	$ex_memo = ehtml($ex_memo);


	$v_value = array(':grade_name'=>$grade_name);
	$query = "SELECT grade_name FROM mc_db_grade_info WHERE use_yn = 'Y' AND grade_name = :grade_name AND grade_code NOT IN ('{$grade_code}')";
	$overlap = view_pdo($query, $v_value);

	if( $overlap['grade_name'] ){
		echo "이미 존재하는 등급명입니다. 다른 등급명을 입력해주시기 바랍니다.";
		return false;
	}	
	$value = array(':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':grade_name'=>$grade_name, ':ex_memo'=>$ex_memo, ':use_yn'=>$use_yn, ':grade_code'=>$grade_code);
	$query = "
		UPDATE mc_db_grade_info SET
			  grade_name = :grade_name
			, ex_memo = :ex_memo
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
			, use_yn = :use_yn
		WHERE grade_code = :grade_code
	";
	$exec = execute_pdo($query, $value);


	if($exec['data']-> rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>