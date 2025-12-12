<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$use_yn = ($use_yn) ? "Y" : "N";
	$andQuery = "";



	$value1 = array(':idx' => $idx);
	$query1 = "SELECT use_yn FROM mt_member WHERE idx = :idx";
	$use = view_pdo($query1, $value1)['use_yn'];

	$view = view_sql("SELECT * FROM mt_member WHERE idx = '{$idx}'");
   
	# 슬롯 검사
	// if($use_yn == "Y" && $view['use_yn'] == "N"){
	//    if($site["slot"] < 9999){
	// 	  if(($site["slot"] - $site["slot_r"]) <= 0){
	// 		 echo "사용가능한 사용자수량이 존재하지 않습니다.";
	// 		 return false;
	// 	  }
	//    }
	// }


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
	
	$query5 = "
		UPDATE mt_member SET
			m_name = :m_name
		, m_tel = :m_tel
		, m_mail = :m_mail
		, m_addr = :m_addr
		{$andQuery}
		, edit_idx = :proc_id	
		, edit_ip = :proc_ip
		, edit_date = now()
		, use_yn = :use_yn
		WHERE idx = :idx
	";

	$exec = execute_pdo($query5, $value3);

	if($exec['data']->rowCount() > 0){
		
		echo "success";
	}  else {
		echo "fail";
	}

?>