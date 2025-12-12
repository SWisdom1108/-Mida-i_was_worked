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
	$snum_use_yn = ($snum_use_yn) ? "Y" : "N";
	# 비밀번호 확인
	if($m_pw || $m_pw_check){
		if($m_pw != $m_pw_check){
			echo "비밀번호를 확인해주시길 바랍니다.";
			return false;
		} else {
			if(strlen($m_pw) < 4){
				echo "비밀번호는 최소 4자 이상입니다.";
				return false;
			} else {
				$andQuery .= ", m_pw = password('{$m_pw}')";
			}
		}
	}

	$snum = view_sql("SELECT * FROM mt_member_snum WHERE m_idx = {$user['idx']}");

	if(!$snum && $snum_use_yn == 'Y'){
		echo "보안카드는 발급 후에 사용 가능합니다.";
		return false;
	}

	$value = array(':m_name'=>ehtml($m_name), ':m_tel'=>$m_tel, ':m_mail'=>ehtml($m_mail), ':m_addr'=>ehtml($m_addr), ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':snum_use_yn'=>$snum_use_yn);
	$query = "
		UPDATE mt_member SET
			  m_name = :m_name
			, m_tel = :m_tel
			, m_mail = :m_mail
			, m_addr = :m_addr
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
			, snum_use_yn = :snum_use_yn
			{$andQuery}
		WHERE idx = '{$user['idx']}'
	";
	
	$exec = execute_pdo($query, $value);
	if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>