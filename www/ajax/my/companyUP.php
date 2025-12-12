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

	$andQuery = "";

	# 권한에 따른 whereQuery
	switch($user['auth_code']){
		case "001" :
			$whereQuery = "WHERE idx = '{$user['sp_code']}'";
			break;
		case "002" :
			$whereQuery = "WHERE idx = '{$user['sp_code']}'";
			break;
		case "003" :
			$whereQuery = "WHERE idx = '{$user['pm_code']}'";
			break;
	}


	$value = array(':company_num'=>$company_num, ':company_name'=>ehtml($company_name), ':company_tel'=>$company_tel, ':company_fax'=>$company_fax, ':company_mail'=>ehtml($company_mail), ':company_type'=>ehtml($company_type), ':company_sec'=>ehtml($company_sec), ':company_addr'=>ehtml($company_addr), ':tax_mail'=>ehtml($tax_mail), ':ceo_name'=>ehtml($ceo_name), ':ceo_tel'=>ehtml($ceo_tel), ':ceo_mail'=>ehtml($ceo_mail), ':bank_code'=>ehtml($bank_code), ':bank_num'=>ehtml($bank_num), ':bank_holder'=>ehtml($bank_holder), ':memo'=>$memo, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);


	$query ="
			UPDATE mt_member_cmpy SET
			  company_num = :company_num
			, company_name = :company_name
			, company_tel = :company_tel
			, company_fax = :company_fax
			, company_mail = :company_mail
			, company_type = :company_type
			, company_sec = :company_sec
			, company_addr = :company_addr
			, tax_mail = :tax_mail
			, ceo_name = :ceo_name
			, ceo_tel = :ceo_tel
			, ceo_mail = :ceo_mail
			, bank_code = :bank_code
			, bank_num = :bank_num
			, bank_holder = :bank_holder
			, memo = :memo
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
			{$andQuery}
		{$whereQuery}
	";

	$exec = execute_pdo($query, $value);
	if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>