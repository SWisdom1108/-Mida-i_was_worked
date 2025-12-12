<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	# 슬롯 검사
	if($site["slot"] < 9999){
		if(($site["slot"] - $site["slot_r"]) <= 0){
			echo "사용가능한 사용자수량이 존재하지 않습니다.";
			return false;
		}
	}

	# 아이디 중복확인
	if(strlen($m_id) < 4){
		echo "아이디는 최소 4자 이상입니다.";
		return false;
	}

	$value1 = array(':m_id' => $m_id);
	$query1 = "SELECT * FROM mt_member WHERE m_id = :m_id";
	$view = view_pdo($query1, $value1);
	if($view['m_id']){
		echo "사용이 불가능한 아이디입니다.";
		return false;
	}

	# 비밀번호 확인
	if(strlen($m_pw) < 4){
		echo "비밀번호는 최소 4자 이상입니다.";
		return false;
	}

	$value = array(':sp_code' => $user['sp_code'], ':m_id' => $m_id, ':m_pw' => $m_pw, ':m_name' => ehtml($m_name), ':m_tel' => $m_tel, ':m_mail' => ehtml($m_mail), ':m_addr' => ehtml($m_addr), ':proc_id' =>  $proc_id, ':proc_ip' => $proc_ip);

	$query = "
		INSERT INTO mt_member
			( sp_code, m_id, m_pw, m_name, m_tel, m_mail, m_addr, auth_code, reg_idx, reg_ip )
		VALUES
			( :sp_code, :m_id, password(:m_pw), :m_name, :m_tel, :m_mail, :m_addr, '002', :proc_id, :proc_ip )
	";

	$exec = execute_pdo($query, $value);
	if($exec['data']->rowCount() > 0){
		$idx = $exec['insertIdx'];
		
		sendNotice("001", $idx, "{$m_name}님 회원가입을 진심으로 축하드립니다! 프로그램 사용으로 원활한 DB관리를 이용해보시길 바랍니다.");
		
		echo "success";
	}  else {
		echo "fail";
	}

?>