<?php

$_SERVER['DOCUMENT_ROOT'] =  $_SERVER['DOCUMENT_ROOT']."/"; // cafe24웹 호스팅용, 그외 호스팅은 주석 혹은 삭제처리

	$customLabel = [];
	$customLabel["tm"] = "팀"; # 커스텀 팀 라벨명
	$customLabel["fc"] = "팀원"; # 커스텀 팀원 라벨명
	$customLabel["cs_name"] = "이름"; # 커스텀 이름 라벨명
	$customLabel["cs_tel"] = "연락처"; # 커스텀 연락처 라벨명

	$setupStatus = true; # 설치여부
	$dbConn = "dbconn_b"; # DB연결타입
	$date = date("Y-m-d");
	session_cache_expire(25920000);
	session_start();
	date_default_timezone_set("Asia/Seoul");
	header('Content-Type: text/html; charset=UTF-8');

	//7.3 버전 이슈
	if (!function_exists('mysql_insert_id')) {
		function mysql_insert_id() {
			global $mysqli;
			if ($mysqli instanceof mysqli) {
				return mysqli_insert_id($mysqli);
			} elseif (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
				return $GLOBALS['pdo']->lastInsertId();
			}
			return 0; // 기본값 반환
		}
	}
	//7.3 버전 이슈

	$proc_ip = $_SERVER['REMOTE_ADDR'];
	if(!$setupStatus){
		header("Location: /setup/01");
	} else {
		include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/sys_info_r.php";	//get system account
		include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/{$dbConn}/sys_info.php";	//get system account
		include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/{$dbConn}/db_conn.php";	//get db connection & func.

		include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/function.php";	//get db connection & func.

		mysqli_query($conn, "set session character_set_connection=utf8;");
		mysqli_query($conn, "set session character_set_results=utf8;");
		mysqli_query($conn, "set session character_set_client=utf8;");

		$mCheck = '/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS)/i';  
		# prepare mysql 객체생성
		$mysql = new PDO("mysql:host={$dbHost};dbname={$dbName}",$dbUser,$dbPasswd);
		$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$mysql->exec("set names utf8");

		# ip
		$allPermitYN = view_sql("SELECT * FROM mt_permit")['all_permit_yn'];
		$ipChk = view_sql("SELECT * FROM mt_permit_ip WHERE use_yn = 'Y' AND permit_ip = '{$proc_ip}'");
		# ip 확인 (N: 전체 IP 허용 / Y: 등록한 IP만 허용)
		if($allPermitYN == 'Y' && strpos($_SERVER['REQUEST_URI'], '/api/') === false) {
			if(!$ipChk['permit_ip']) {
				die();
			}
		}

		#아이피 체크 (블랙 리스트)
		$value = array(':use_yn'=> 'Y', ':proc_ip'=> $proc_ip);
		$query = "
			SELECT *
			FROM mt_block_ip
			WHERE use_yn = :use_yn
			AND block_ip = :proc_ip
		";
		$blockipChk = view_pdo($query,$value);
		if($blockipChk){
			if(strpos($_SERVER['REQUEST_URI'], '/api/') === false && strpos($_SERVER['REQUEST_URI'], '/sub/error/') === false){
				www("/sub/error/security"); // 리다이렉션할 페이지 경로
   				die(); // 스크립트 실행 중지
			}
		}

		# 한 아이피에서 다회 로그인시 차단 
		$value = array(':proc_ip'=>$proc_ip, ':date'=>$date);
		$query = "
			SELECT * 
			FROM mt_login_block_ip
			WHERE login_ip = :proc_ip
			AND DATE_FORMAT(reg_date, '%Y-%m-%d') = :date 
		";
		$loginipBlock = view_pdo($query, $value);
		if($loginipBlock['block_yn'] == 'Y'){
			if(strpos($_SERVER['REQUEST_URI'], '/api/') === false && strpos($_SERVER['REQUEST_URI'], '/sub/error/') === false){
				www("/sub/error/security"); // 리다이렉션할 페이지 경로
   				die(); // 스크립트 실행 중지
			}
		}

		if($_SESSION['idx']){
			# 기본정보
			$value = array(':SESSION'=>$_SESSION['idx']);
			$query = "
				SELECT MT.*
					, ( SELECT auth_name FROM mc_member_auth WHERE auth_code = MT.auth_code ) AS auth_name
				FROM mt_member MT
				WHERE idx = :SESSION
				AND use_yn = 'Y'
			";
			$user = view_pdo($query, $value);

			# 업체정보
			$cmpyQuery = "";
			switch($user['auth_code']){
				case "001" :
					# 최고관리자일 경우
					$cmpyQuery .= " AND auth_code = '001' AND idx = '{$user['sp_code']}'";
					break;
				case "002" :
					# 관리자일 경우
					$cmpyQuery .= " AND auth_code = '001' AND idx = '{$user['sp_code']}'";
					break;
				case "003" :
					# 생산마스터일 경우
					$cmpyQuery .= " AND auth_code = '003' AND idx = '{$user['pm_code']}'";
					break;
				default :
					# 그 외
					$cmpyQuery .= " AND auth_code = '001' AND idx = '{$user['sp_code']}'";
					break;
			}

			$cmpy = view_sql("
				SELECT MT.*
					, ( SELECT bank_name FROM mc_bank WHERE bank_code = MT.bank_code ) AS bank_name
				FROM mt_member_cmpy MT
				WHERE use_yn = 'Y' 
				{$cmpyQuery}
			");
			
			$mainCmpy = view_sql("
				SELECT MT.*
					, ( SELECT bank_name FROM mc_bank WHERE bank_code = MT.bank_code ) AS bank_name
				FROM mt_member_cmpy MT
				WHERE use_yn = 'Y' 
				AND auth_code = '001'
			");

			if(!$user['idx'] || !$user['auth_name']){
				www("/account/logout");
			}
		}

		# 작성자 정보
		$proc_id = $user['idx'];

		# 현재 경로
		$nowPath = preg_replace("`\/[^/]*\.php$`i", "", $_SERVER['PHP_SELF']);

		# 사이트 정보
		$site = view_sql("SELECT * FROM mt_site_info ORDER BY idx DESC");
		$site_info = view_sql("SELECT * FROM mt_site_info ORDER BY idx DESC");

		# 메인 회사정보
		$mainCmpy = view_sql("
			SELECT MT.*
				, ( SELECT bank_name FROM mc_bank WHERE bank_code = MT.bank_code ) AS bank_name
			FROM mt_member_cmpy MT
			WHERE use_yn = 'Y' 
			AND auth_code = '001'
		");
		
		# 사용 사용자수
		$site['slot_r'] = view_sql("SELECT COUNT(*) AS cnt FROM mt_member WHERE use_yn = 'Y' AND auth_code not in ( 001, 003, 007)")['cnt'];
		
		# 프로그램 사용 만료일
		$site["n_date_r"] = new DateTime(date("Y-m-d"));
		$site["e_date_r"] = new DateTime(date("Y-m-d", strtotime($site['e_date'])));
		$programDateInfo = $site["n_date_r"]->diff($site["e_date_r"]);

		if($programDateInfo->invert){
			if($_SERVER['REQUEST_URI'] != "/sub/error/endDate" && strpos($_SERVER['REQUEST_URI'], '/api/') === false){
				www("/sub/error/endDate");
				die();
			}
		}
	}


	if ( !$user['idx'] ){
		if ( strpos($_SERVER["PHP_SELF"], "excel/" ) ){
			golink("로그인 세션이 존재하지 않습니다.\\n로그인후 이용 부탁드립니다.","/account/login");
			die();
		}
	}
	
	$pm_module = 'N';
	$alarm_module = 'N';
?>
