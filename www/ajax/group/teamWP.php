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
	$team_name = ehtml($team_name);
	$use_yn = ($use_yn) ? "Y" : "N";

	# 팀(부서)명 중복확인
	$value1 = array(':team_name' => $team_name);
	$query1 = "SELECT * FROM mt_member_team WHERE team_name = :team_name";
	$view = view_pdo($query1, $value1);
	if($view['team_name']){
		echo "이미 존재하는 팀(부서)명입니다.";
		return false;
	}




	$value = array(':team_name' => $team_name, ':memo' => $memo, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip, ':use_yn' => $use_yn);
	$query = "
		INSERT INTO mt_member_team
			( team_name, memo, dist_sort, dist_cnt, reg_idx, reg_ip, use_yn )
		VALUES
			( :team_name, :memo, 0, 0, :proc_id, :proc_ip, :use_yn )
	";
	$exec = execute_pdo($query, $value);

	if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>