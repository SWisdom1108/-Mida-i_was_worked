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
	
	$m_name = ehtml($m_name);
	$m_mail = ehtml($m_mail);
	$m_addr = ehtml($m_addr);

	# 슬롯 검사
	if($use_yn == "Y"){
		if($site["slot"] < 9999){
			if(($site["slot"] - $site["slot_r"]) <= 0){
				echo "사용가능한 사용자수량이 존재하지 않습니다.";
				return false;
			}
		}
	}

	# 아이디 중복확인
	if(strlen($m_id) < 4){
		echo "아이디는 최소 4자 이상입니다.";
		return false;
	}

	$value3 = array(':m_id' => $m_id);
	$query3 = "SELECT * FROM mt_member WHERE m_id = :m_id";
	$view = view_pdo($query3, $value3);
	if($view['m_id']){
		echo "사용이 불가능한 아이디입니다.";
		return false;
	}

	# 비밀번호 확인
	if(strlen($m_pw) < 4){
		echo "비밀번호는 최소 4자 이상입니다.";
		return false;
	}
	$value2 = array(':sp_code' => $user['sp_code'], ':team_code' => $team_code, ':m_id' => $m_id, ':m_pw' => $m_pw, ':m_name' => $m_name, ':m_tel' => $m_tel, ':m_mail' => $m_mail, ':authCode' => $authCode, ':m_addr' => $m_addr, ':proc_id' =>  $proc_id, ':proc_ip' => $proc_ip, ':use_yn' => $use_yn);
	$query2 = "
			INSERT INTO mt_member
				( sp_code, tm_code, m_id, m_pw, m_name, m_tel, m_mail, m_addr, auth_code, dist_sort, dist_cnt, reg_idx, reg_ip, use_yn )
			VALUES
				( :sp_code, :team_code, :m_id, password(:m_pw), :m_name, :m_tel, :m_mail, :m_addr, :authCode, '0', '0', :proc_id, :proc_ip, :use_yn )
	";
	$exec2 = execute_pdo($query2, $value2);
	if($exec2['data']->rowCount() > 0){
		$midx = $exec2['insertIdx'];
		sendNotice("001", $midx, "{$m_name}님 회원가입을 진심으로 축하드립니다! 프로그램 사용으로 원활한 DB관리를 이용해보시길 바랍니다.");

		excute("INSERT INTO mt_member_pmDist (m_idx, pm_code, reg_idx, reg_ip, reg_date) SELECT {$midx}, idx, '{$proc_id}', '{$proc_ip}', now() FROM mt_member_cmpy WHERE idx != 0001");

		
		
		if($mg_yn == "Y"){
			$value1 = array(':midx' => $midx, ':team_code' => $team_code);
			$query1 = "
			UPDATE mt_member_team SET
				m_idx = :midx
			WHERE idx = :team_code
			";
			$query0 = "
				UPDATE mt_member SET
					auth_code = 005
				WHERE tm_code = :team_code AND auth_code = 004 AND idx != :midx
			";
			execute_pdo($query1, $value1);
			execute_pdo($query0, $value1);
		}
		
		echo "success";
	}  else {
		echo "fail";
	}

?>