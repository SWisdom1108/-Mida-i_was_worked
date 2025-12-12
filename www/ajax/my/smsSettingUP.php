<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$use_yn_01 = ($use_yn_01) ? "Y" : "N";
	$use_yn_02 = ($use_yn_02) ? "Y" : "N";

	# 메인 발신번호
	$value = array(':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip );
	$query = "
		UPDATE mt_sms_tel SET
			  main_yn = 'N'
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()";
	execute_pdo($query, $value);

	if($mainTel){
		$value = array(':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':mainTel'=> $mainTel );
		$query = "
			UPDATE mt_sms_tel SET
				  main_yn = 'Y'
				, edit_idx = :proc_id
				, edit_ip = :proc_ip
				, edit_date = now()
			WHERE idx = :mainTel
			";
		execute_pdo($query, $value);

	}

	# 생산업체DB유입 관리자 템플릿정보
	$value = array(':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':use_yn_01'=> $use_yn_01 );
	$query = "
		UPDATE mt_sms_template SET
			  use_yn = :use_yn_01
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx = '1'
		";
	execute_pdo($query, $value);


	# 담당자DB분배 담당자 템플릿정보
	$value = array(':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':use_yn_02'=> $use_yn_02 );
	$query = "
		UPDATE mt_sms_template SET
			  use_yn = :use_yn_02
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx = '2'
		";
	execute_pdo($query, $value);

	echo "success";

?>