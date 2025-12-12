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
	$value = array(':password'=>$userpassword,':username'=>$username,':proc_ip'=>$proc_ip);
	$query = "SELECT MT.*
			, ( SELECT auth_name FROM mc_member_auth WHERE auth_code = MT.auth_code ) AS auth_name
			, ( SELECT password(:password) ) AS userpassword
			, ( SELECT permit_ip FROM mt_permit_ip WHERE permit_ip = :proc_ip) AS permitip
		FROM mt_member MT
		WHERE m_id = :username";
	$view = view_pdo($query, $value);

	# 아이디가 존재하지않은 경우
    if(!$view['m_id']){
		echo "return user";
		return false;
	}

	$fail_time = strtotime($view['fail_time']);
	$current_time = time(); // 현재 시간의 타임스탬프
	// fail_time과 현재 시간 사이의 차이를 계산 (초 단위)
	$time_difference = ($fail_time + 5 * 60) - $current_time;
	if ($time_difference > 0 && $view['snum_use_yn'] == 'Y') {
	    // 남은 시간 계산 (분과 초)
	    $minutes = floor($time_difference / 60);
	    $seconds = $time_difference % 60;
	    die("보안카드 인증에 실패하였습니다. 로그인은 {$minutes}분 {$seconds}초 후에 가능합니다.");
	}

	# 비밀번호가 일치하지 않을 경우
	if($view['m_pw'] != $view['userpassword']){
			#로그인 ip차단기능 (비밀번호 틀릴시 동일 아이피가 없으면 추가 있으면 업데이트)
			if(!$ipCheck['login_ip']){
				$value = array(':ip'=>$ip);
				$query = "INSERT INTO mt_login_block_ip (login_ip) VALUES (:ip)";
				execute_pdo($query, $value);
			}else{
				$value = array(':ip'=>$ip, ':date' => $date);
				$query = "UPDATE mt_login_block_ip SET login_cnt = login_cnt+1 WHERE login_ip = :ip AND DATE_FORMAT(reg_date , '%Y-%m-%d') = :date";
				execute_pdo($query, $value);
			}
	
			if(!$ipCheck || ($ipCheck['block_yn'] == 'N' && $ipCheck['login_cnt'] <5 && $blockInfo['login_cnt'] < '5')){
				echo "비밀번호를 ({$blockInfo['login_cnt']})회 틀렸습니다.\n";
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

	# ip 확인 (N: 전체 IP 허용 / Y: 등록한 IP만 허용)
	$value = array(''=>'');
	$query = "SELECT * FROM mt_permit";
	$allPermitYN = view_pdo($query, $value)['all_permit_yn'];
	if($allPermitYN == 'Y') {
		if(!$view['permitip']) {
			echo "return ip";
			return false;
		}
	}

	# 권한이 존재하지 않거나 이용이 중단된 계정일 경우
	if(!$view['auth_name'] || $view['use_yn'] == "N"){
		echo "return auth";
		return false;
	}

	if($_POST['saveUserInfo'] == "true"){
		setcookie("username", $username, time() + 86400 * 3650, "/");
		setcookie("userpassword", $userpassword, time() + 86400 * 3650, "/");
	} else {
		setcookie("username", "", time() + 86400 * 3650, "/");
		setcookie("userpassword", "", time() + 86400 * 3650, "/");
	}

	if($view['snum_use_yn'] == 'Y'){
		echo "s_card";
		$numbers = range(1, 30);
		shuffle($numbers);
		$value = array(':username'=>$username);
		$query = "UPDATE mt_member SET
			 			snum_01 = '{$numbers[0]}',
			 			snum_02 = '{$numbers[1]}',
			 			fail_time = now()
				  WHERE m_id = :username";
		execute_pdo($query, $value);
		return false;
	}



	$_SESSION['idx'] = $view['idx'];

	if($_SESSION['idx']){
		$value = array(':username'=>$username);
		$query = "UPDATE mt_member SET
			 			login_cnt = 1
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