<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$ip = $_SERVER['REMOTE_ADDR']; #현재 아이피
	$date = date("Y-m-d"); #오늘날짜 (하루 기준 5회 로그인 틀릴시 차단되기 때문)

	# 아이피 차단 로그인 체크 
	$value = array (':ip' => $ip, ':date'=> $date);
	$query = "SELECT * FROM mt_login_block_ip WHERE login_ip = :ip AND DATE_FORMAT(reg_date , '%Y-%m-%d') = :date";
	$ipCheck = view_pdo($query, $value);

	# 로그인차단리스트 체크
	$value = array(':username'=>$username);
	$query = "SELECT * FROM mt_member WHERE use_yn = 'Y' AND m_id = :username";
	$blockInfo = view_pdo($query, $value);
    if($blockInfo['login_block'] == "Y"){
		echo "out";
		return false;
	}
    
    # 계정정보
	$value = array(':username'=>$username,':proc_ip'=>$proc_ip);
	$query = "SELECT MT.*
			, ( SELECT auth_name FROM mc_member_auth WHERE auth_code = MT.auth_code ) AS auth_name
			, ( SELECT permit_ip FROM mt_permit_ip WHERE permit_ip = :proc_ip) AS permitip
		FROM mt_member MT
		WHERE m_id = :username";
	$view = view_pdo($query, $value);

	# 아이디가 존재하지않은 경우
    if(!$view['m_id']){
		echo "return user";
		return false;
	}
	$snum01 = $view['snum_01'];
	$snum02 = $view['snum_02'];
	if($snum01 < 10){
		$snum01 = '0'.$snum01;
	}
	if($snum02 < 10){
		$snum02 = '0'.$snum02;
	}

	$snum = view_sql("SELECT * FROM mt_member_snum WHERE m_idx = {$view['idx']}");
	$s_num_part1 = substr($snum['s_num'.$snum01], 0, 2); 
	$s_num_part2 = substr($snum['s_num'.$snum02], 2, 2);

	# 비밀번호가 일치하지 않을 경우
	if($s_num_part1 != $first || $s_num_part2 != $second){
			#로그인 ip차단기능 (비밀번호 틀릴시 동일 아이피가 없으면 추가 있으면 업데이트)
			// if(!$ipCheck['login_ip']){
			// 	$value = array(':ip'=>$ip);
			// 	$query = "INSERT INTO mt_login_block_ip (login_ip) VALUES (:ip)";
			// 	execute_pdo($query, $value);
			// }else{
			// 	$value = array(':ip'=>$ip, ':date' => $date);
			// 	$query = "UPDATE mt_login_block_ip SET login_cnt = login_cnt+1 WHERE login_ip = :ip AND DATE_FORMAT(reg_date , '%Y-%m-%d') = :date";
			// 	execute_pdo($query, $value);
			// }
	
			if(!$ipCheck || ($ipCheck['block_yn'] == 'N' && $ipCheck['login_cnt'] <5 && $blockInfo['login_cnt'] < '5')){
				echo "보안카드를 비밀번호를 ({$blockInfo['login_cnt']})회 틀렸습니다.\n";
			}
	
			
			if($ipCheck['login_cnt'] == "5"){
				$value = array(':ip'=>$ip, ':date'=>$date, ':block_yn' => 'Y');
				$query = "UPDATE mt_login_block_ip SET
								block_yn = :block_yn
							   ,block_date = now()
							WHERE login_ip = :ip
							   AND DATE_FORMAT(reg_date , '%Y-%m-%d') = :date";
				execute_pdo($query, $value);
			}
	
			if($blockInfo['login_cnt'] < 5 ){
				$value = array(':username'=>$username);
				$query = "UPDATE mt_member SET
								 login_cnt = login_cnt+1
						  WHERE m_id = :username";
				execute_pdo($query, $value);
			}
	
			if($blockInfo['login_cnt'] == 5){
			$value = array(':proc_ip'=>$proc_ip,':username'=>$username);
			$query = "
					UPDATE mt_member SET
						   login_block = 'Y'
						 , login_block_date = now()
						 , login_block_ip = :proc_ip
					WHERE m_id = :username
				";
				execute_pdo($query, $value);
			}
	
			if($ipCheck['block_yn'] == "N" && $ipCheck['login_cnt'] == '5'){
				die("5회 이상 로그인 실패하여 해당 아이피가 차단됩니다.\n관리자에게 문의해주시기 바랍니다.") ;
			}
	
			if($ipCheck['block_yn'] == 'Y' && $ipCheck['login_cnt'] > '5'){
				die("이미 로그인 실패횟수가 초과하여 해당 아이피에서 접속이 차단상태입니다.\n관리자에게 문의해주시기 바랍니다.");
			}
	
			if($blockInfo['login_block'] == "N" && $blockInfo['login_cnt'] == '5'){
				die("5회 이상 로그인 실패하여 계정이 잠금처리됩니다.\n관리자에게 문의해주시기 바랍니다.");
				
			}
	
			if($blockInfo['login_block'] == "Y" && $blockInfo['login_cnt'] > '5'){
				die("이미 로그인 실패횟수가 초과하여 계정이 잠금상태입니다.\n관리자에게 문의해주시기 바랍니다.");
			}
			
			die();
		
	}



	$_SESSION['idx'] = $view['idx'];

	if($_SESSION['idx']){
		$value = array(':username'=>$username);
		$query = "UPDATE mt_member SET
			 			login_cnt = 1,
			 			fail_time = null
				  WHERE m_id = :username";
		execute_pdo($query, $value);
	}
	
	# 로그인기록
	$value = array(':SESSION'=>$_SESSION['idx'],':proc_ip'=>$proc_ip);
	$query = "INSERT INTO mt_login_log
					( reg_idx, reg_ip )
			  VALUES
					( :SESSION, :proc_ip )";
	execute_pdo($query, $value);
	
	echo "success";

?>