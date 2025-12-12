<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$team_name = ehtml($team_name);
	$memo = ehtml($memo);
	$use_yn = ($use_yn) ? "Y" : "N";

	# 팀(부서)명 중복확인
	$value2 = array(':idx' => $idx);
	$query2 = "SELECT team_name FROM mt_member_team WHERE idx = :idx";
	$oldTeamname = view_pdo($query2, $value2);
	if($oldTeamname['team_name'] != $team_name){
		$value1 = array(':team_name' => $team_name);
		$query1 = "SELECT * FROM mt_member_team WHERE team_name = :team_name";
		$view = view_pdo($query1, $value1);
		if($view['team_name']){
			echo "이미 존재하는 팀(부서)명입니다.";
			return false;
		}
	}
	$value0 = array(':team_name' => $team_name, ':memo' => $memo, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip, ':use_yn' => $use_yn, ':idx' => $idx);
	$query0 = "
		UPDATE mt_member_team SET
			team_name = :team_name
		, memo = :memo
		, edit_idx = :proc_id
		, edit_ip = :proc_ip
		, edit_date = now()
		, use_yn = :use_yn
		WHERE idx = :idx
	";
	$exce0 = execute_pdo($query0, $value0);
	if($exce0['data']->rowCount() > 0){
		
		# 팀(부서)원 사용여부 컨트롤
		$value = array(':use_yn' => $use_yn, ':idx' => $idx);
		$query = "
			UPDATE mt_member SET
				use_yn = :use_yn
			WHERE tm_code = :idx
		";
		execute_pdo($query, $value);		
		echo "success";
	}  else {
		echo "fail";
	}

?>