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
	$hidden_yn = ($hidden_yn) ? "Y" : "N";
	$depth1 = ($depth1) ? $depth1 : null;
	$depth2 = ($depth2) ? $depth2 : null;
	$depth3 = ($depth3) ? $depth3 : null;
	$depth4 = ($depth4) ? $depth4 : null;
	$depth5 = ($depth5) ? $depth5 : null;
	$andQuery = "";

	# 비밀번호 확인
	if($m_pw){
		if(strlen($m_pw) < 4){
			echo "비밀번호는 최소 4자 이상입니다.";
			return false;
		} else {
			$andQuery .= ", m_pw = password(:m_pw)";
		}
		$value = array(':m_name' => $m_name, ':m_tel' => $m_tel, ':m_mail' => $m_mail, ':m_addr' =>  $m_addr, ':m_pw' => $m_pw, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip, ':m_idx' => $m_idx);
	}else {
		$value = array(':m_name' => $m_name, ':m_tel' => $m_tel, ':m_mail' => $m_mail, ':m_addr' =>  $m_addr, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip, ':m_idx' => $m_idx);
	}

	$value1 = array(':company_name' => $company_name, ':company_num' => $company_num, ':company_tel' => $company_tel, ':company_fax' => $company_fax, ':company_mail' => $company_mail, ':hidden_yn' => $hidden_yn, ':company_type' => $company_type, ':company_sec' => $company_sec, ':company_addr' => $company_addr, ':tax_mail' => $tax_mail, ':ceo_name' => $ceo_name, ':ceo_tel' => $ceo_tel, ':ceo_mail' => $ceo_mail, ':bank_code' => $bank_code, ':bank_holder' => $bank_holder, ':bank_num' => $bank_num, ':memo' => $memo, ':depth1' => $depth1, ':depth2' => $depth2, ':depth3' => $depth3, ':depth4' => $depth4, ':depth5' => $depth5,':proc_id' => $proc_id, ':proc_ip' => $proc_ip, ':idx' => $idx);  
	$query1 = "
		UPDATE mt_member_cmpy SET
			  company_name = :company_name
			, company_num = :company_num
			, company_tel = :company_tel
			, company_fax = :company_fax
			, company_mail = :company_mail
			, hidden_yn = :hidden_yn
			, company_type = :company_type
			, company_sec = :company_sec
			, company_addr = :company_addr
			, tax_mail = :tax_mail
			, ceo_name = :ceo_name
			, ceo_tel = :ceo_tel
			, ceo_mail = :ceo_mail
			, bank_code = :bank_code
			, bank_holder = :bank_holder
			, bank_num = :bank_num
			, memo = :memo
			, depth1 = :depth1
			, depth2 = :depth2
			, depth3 = :depth3
			, depth4 = :depth4
			, depth5 = :depth5
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx = :idx
	";
	$exec1 = execute_pdo($query1, $value1);
	if($exec1['data']->rowCount() > 0){
		// $value = array(':m_name' => $m_name, ':m_tel' => $m_tel, ':m_mail' => $m_mail, ':m_addr' =>  $m_addr, ':m_pw' => $m_pw, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip, ':m_idx' => $m_idx);
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
			WHERE idx = :m_idx
		";
		execute_pdo($query, $value);		
		echo "success";
	}  else {
		echo "fail";
	}

?>