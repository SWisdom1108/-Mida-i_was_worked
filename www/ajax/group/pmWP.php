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
	$hidden_yn = ($hidden_yn) ? "Y" : "N";
	$m_name = ehtml($m_name);
	$m_mail = ehtml($m_mail);
	$m_addr = ehtml($m_addr);
	$company_name = ehtml($company_name);
	$company_mail = ehtml($company_mail);
	$company_type = ehtml($company_type);
	$company_sec = ehtml($company_sec);
	$company_addr = ehtml($company_addr);
	$tax_mail = ehtml($tax_mail);
	$ceo_name = ehtml($ceo_name);
	$ceo_mail = ehtml($ceo_mail);
	$bank_holder = ehtml($bank_holder);

	$depth1 = ($depth1 == '') ? null : $depth1;
	$depth2 = ($depth2 == '') ? null : $depth2;
	$depth3 = ($depth3 == '') ? null : $depth3;
	$depth4 = ($depth4 == '') ? null : $depth4;
	$depth5 = ($depth5 == '') ? null : $depth5;

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


	$value2 = array(':sp_code' => $user['sp_code'], ':hidden_yn' => $hidden_yn, ':company_name' => $company_name, ':company_num' => $company_num, ':company_tel' => $company_tel, ':company_fax' => $company_fax, ':company_mail' => $company_mail, ':company_type' => $company_type, ':company_sec' => $company_sec, ':company_addr' => $company_addr, ':tax_mail' => $tax_mail, ':ceo_name' => $ceo_name, ':ceo_tel' => $ceo_tel, ':ceo_mail' => $ceo_mail, ':bank_code' => $bank_code, ':bank_num' => $bank_num, ':bank_holder' => $bank_holder, ':memo' => $memo, ':depth1' => $depth1, ':depth2' => $depth2, ':depth3' => $depth3, ':depth4' => $depth4, ':depth5' => $depth5, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip);
	$query2 = "
		INSERT INTO mt_member_cmpy
			( sp_code, hidden_yn, auth_code, company_name, company_num, company_tel, company_fax, company_mail, company_type, company_sec, company_addr, tax_mail, ceo_name, ceo_tel, ceo_mail, bank_code, bank_num, bank_holder, memo, depth1, depth2, depth3, depth4, depth5, reg_idx, reg_ip )
		VALUES
			( :sp_code, :hidden_yn, '003', :company_name, :company_num, :company_tel, :company_fax, :company_mail, :company_type, :company_sec, :company_addr, :tax_mail, :ceo_name, :ceo_tel, :ceo_mail, :bank_code, :bank_num, :bank_holder, :memo, :depth1, :depth2, :depth3, :depth4, :depth5, :proc_id, :proc_ip )
	";

	$exec2 = execute_pdo($query2, $value2);


	if($exec2['data']->rowCount() > 0){
		$cmpy = $exec2['insertIdx'];

		
		# sp_code 변경
		$value4 = array(':cmpy' => $cmpy);
		$query4 = "
			UPDATE mt_member_cmpy SET
				pm_code = :cmpy
			WHERE idx = :cmpy
		";
		execute_pdo($query4, $value4);

		# 계정추가
		$value5 = array(':sp_code' => $user['sp_code'], ':cmpy' => $cmpy, ':m_id' => $m_id, ':m_pw' => $m_pw, ':m_name' => $m_name, ':m_tel' => $m_tel, ':m_mail' => $m_mail, ':m_addr' => $m_addr, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip);
		$query5 = "
			INSERT INTO mt_member
				( sp_code, pm_code, m_id, m_pw, m_name, m_tel, m_mail, m_addr, auth_code, reg_idx, reg_ip )
			VALUES
				( :sp_code, :cmpy, :m_id, password(:m_pw), :m_name, :m_tel, :m_mail, :m_addr, '003', :proc_id, :proc_ip )
		";
		$exec3 = execute_pdo($query5, $value5);
		
		$midx = $exec3['insertIdx'];
		sendNotice("001", $midx, "{$m_name}님 회원가입을 진심으로 축하드립니다! 프로그램 사용으로 원활한 DB관리를 이용해보시길 바랍니다.");

		$value6 = array(':midx' => $midx, ':cmpy' => $cmpy);
		$query6 = "
			UPDATE mt_member_cmpy SET
				m_idx = :midx
			WHERE idx = :cmpy
		";
		execute_pdo($query6, $value6);

		excute("INSERT INTO mt_member_pmDist (m_idx, pm_code, reg_idx, reg_ip, reg_date) SELECT idx, '{$cmpy}', '{$proc_id}', '{$proc_ip}', now() FROM mt_member WHERE auth_code > 3");

		echo "success";
	}  else {
		echo "fail";
	}

?>