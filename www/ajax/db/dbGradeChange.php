<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$value = array(':grade_code'=>$grade_code, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
	$query = "
		UPDATE mt_db SET
			  grade_code = :grade_code
			, grade_date = now()
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx IN ( ".implode(",", $idx)." )
	";
	$exec = execute_pdo($query, $value);

	$v_value = array(':grade_code'=>$grade_code);
	$v_query = "SELECT ex_memo, grade_name FROM mc_db_grade_info WHERE use_yn = 'Y' AND grade_code = :grade_code";
	$grade = view_pdo($v_query, $v_value);

	if($exec['data']->rowCount() > 0 ){		

		foreach($idx as $db_idx){
			$value2 = array(':db_idx'=>$db_idx, ':grade_code'=>$grade_code, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query2 = "
				INSERT INTO mt_db_grade_log
					( db_idx, grade_code, grade_name, ex_memo, reg_idx, reg_ip, reg_date )
				VALUES
					( :db_idx, :grade_code, '{$grade['grade_name']}', '{$grade['ex_memo']}', :proc_id, :proc_ip, now() )	
			";
			execute_pdo($query2, $value2);
		}
		echo "success";
	}  else {
		echo "fail";
	}

?>