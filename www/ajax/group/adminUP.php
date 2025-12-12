<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$andQuery = "";

	# 비밀번호 확인
	if($m_pw){
		if(strlen($m_pw) < 4){
			echo "비밀번호는 최소 4자 이상입니다.";
			return false;
		} else {
			$andQuery .= ", m_pw = password(:m_pw)";
		}
		$value = array(':m_name' => $m_name, ':m_tel' => $m_tel, ':m_mail' => $m_mail, ':m_addr' => $m_addr, ':m_pw' => $m_pw, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip, ':idx' => $idx);
	}else {
		$value = array(':m_name' => $m_name, ':m_tel' => $m_tel, ':m_mail' => $m_mail, ':m_addr' => $m_addr, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip, ':idx' => $idx);
	}

	$query = "
		UPDATE mt_member SET
			  m_name = :m_name
			, m_tel = :m_tel
			, m_mail = :m_mail
			, m_addr = :m_addr
			{$andQuery}
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx = :idx
	";
	$exec = execute_pdo($query, $value);
	if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>