<?php 

	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	$bbs_code = $_GET['bbs'];
	$secMenu = "bbs{$bbs_code}";

	# 콘텐츠설정
	$value = array(':bbs_code'=>$username);
	$query = "SELECT * FROM mc_bbs_code WHERE bbs_code = :bbs_code";
	$thisBoardInfo = view_pdo($query, $value);

	$contentsTitle = $thisBoardInfo['bbs_kr_name'];
	$contentsInfo = $thisBoardInfo['bbs_memo'];

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, $thisBoardInfo['bbs_kr_name']);
	switch($_GET['inc']){
		case "L" :
			array_push($contentsRoots, "목록");
			break;
		case "W" :
			array_push($contentsRoots, "등록");
			break;
		case "V" :
			array_push($contentsRoots, "자세히보기");
			break;
		case "U" :
			array_push($contentsRoots, "수정");
			break;
	}

	# 페이지 설정
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";
	
?>
	
	<div class="section bbsModuleWrap">
		
		<?php
		
			# 게시판모듈
			$bbsCode = $_GET['bbs']; # 게시판코드
			$incType = $_GET['inc']; # 게시판형태
			$pmType = "pc"; # 피씨 모바일 구분
			$userType = ($user['auth_code'] == "001" || $user['auth_code'] == "002") ? "admin" : "user"; # 관리자 및 일반유저 구분
			$userTable = "mt_member"; # 유저 테이블
			$userNameColum = "m_name"; # 유저이름 컬럼명
			include_once $_SERVER['DOCUMENT_ROOT']."/mida/module/bbs/bbs.php";
		
		?>
		
	</div>

<?php include_once $_SERVER["DOCUMENT_ROOT"]."/include/footer.php"; ?>