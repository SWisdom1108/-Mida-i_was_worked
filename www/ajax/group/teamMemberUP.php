<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$mg_yn = ($mg_yn) ? "Y" : "N";
	$use_yn = ($use_yn) ? "Y" : "N";
	$authCode = ($mg_yn == "Y") ? "004" : "005";
	$andQuery = "";



	$value1 = array(':idx' => $idx);
	$query1 = "SELECT use_yn FROM mt_member WHERE idx = :idx";
	$use = view_pdo($query1, $value1)['use_yn'];

	$view = view_sql("SELECT * FROM mt_member WHERE idx = '{$idx}'");
   
	# 슬롯 검사
	if($use_yn == "Y" && $view['use_yn'] == "N"){
	   if($site["slot"] < 9999){
		  if(($site["slot"] - $site["slot_r"]) <= 0){
			 echo "사용가능한 사용자수량이 존재하지 않습니다.";
			 return false;
		  }
	   }
	}



	$value2 = array(':idx' => $idx);
	$query2 = "SELECT tm_code FROM mt_member WHERE idx = :idx";
	$team_code = view_pdo($query2, $value2)['tm_code'];
	
	$value2 = array(':idx' => $idx);
	$query3 = "SELECT m_idx FROM mt_member_team WHERE m_idx = :idx";
	$mm_idx = view_pdo($query3, $value2)['mm_idx'];
	$mg_idx = ($mg_idx) ? $mg_idx : 0;

	// $value2 = array(':idx' => $idx);
	// print_r($value2);
	// $query4 = "SELECT COUNT(m_idx) FROM mt_member_team WHERE m_id = :idx";
	// $count_idx = view_pdo($query4, $value2)['count_idx'];

	# 비밀번호 확인
	if($m_pw){
		if(strlen($m_pw) < 4){
			echo "비밀번호는 최소 4자 이상입니다.";
			return false;
		} else {
			$andQuery .= ", m_pw = password(:m_pw)";
		}
		$value3 = array(':m_name' => $m_name, ':m_tel' => $m_tel, ':m_mail' => $m_mail, ':m_addr' => $m_addr, ':m_pw' => $m_pw, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip, ':use_yn' => $use_yn, ':idx' => $idx);
	}else{
		$value3 = array(':m_name' => $m_name, ':m_tel' => $m_tel, ':m_mail' => $m_mail, ':m_addr' => $m_addr, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip, ':use_yn' => $use_yn, ':idx' => $idx);
	}

	if($team_code != $tm_code) {
		$value10 = array('tm_code' => $tm_code, ':idx' => $idx);
		$query10 = "
			UPDATE mt_db SET
			tm_code = :tm_code
			WHERE m_idx = :idx
		";
		execute_pdo($query10, $value10);

		$value11 = array('tm_code' => $tm_code, ':idx' => $idx);
		$query11 = "
			UPDATE mt_db_dist_log SET
			tm_code = :tm_code
			WHERE m_idx = :idx
		";
		execute_pdo($query11, $value11);
	}

	
	$query5 = "
		UPDATE mt_member SET
			m_name = :m_name
		, m_tel = :m_tel
		, m_mail = :m_mail
		, m_addr = :m_addr
		, auth_code = '{$authCode}'
		, tm_code = '{$tm_code}'
		{$andQuery}
		, edit_idx = :proc_id	
		, edit_ip = :proc_ip
		, edit_date = now()
		, use_yn = :use_yn
		WHERE idx = :idx
	";

	$exec = execute_pdo($query5, $value3);

	if($exec['data']->rowCount() > 0){
		
		if($mg_yn == "Y"){

			$value7 = array(':team_code' => $team_code);
			$query8 = "
				UPDATE mt_member_team SET
					m_idx = null
				WHERE idx = :team_code
			";
			execute_pdo($query8, $value7);
			
			$value5 = array(':team_code' => $team_code);
			$query6 = "
				UPDATE mt_member SET
					auth_code = 005
					WHERE tm_code = :team_code AND auth_code = 004
			";
			execute_pdo($query6, $value5);

			$value6 = array(':idx' => $idx);
			$query7 = "
				UPDATE mt_member SET
					auth_code = 004
				WHERE idx = :idx
			";
			execute_pdo($query7, $value6);
					
			$value4 = array(':idx' => $idx, ':team_code' => $team_code);
			$query6 = "
				UPDATE mt_member_team SET
					m_idx = :idx
				WHERE idx = :team_code
			";
			execute_pdo($query6, $value4);


		} else {
			$value8 = array(':idx' => $idx);
			$query9 = "
				UPDATE mt_member_team SET
					m_idx = '0'
				WHERE m_idx = :idx
			";
			execute_pdo($query9, $value8);

		}
		
		echo "success";
	}  else {
		echo "fail";
	}

?>