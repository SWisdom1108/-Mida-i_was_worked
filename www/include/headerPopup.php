<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php"; ?>
<?php

	# 세션 없을 경우 페이지 로딩 안함
	if(!$_SESSION['idx']){
		www("/sub/error/no");
		die("로그인이 필요합니다.");
	}

	# 메뉴 접근 권한설정
	if(count($menuAuth)){
		# 메뉴 접근 권한설정값이 존재할 경우 체크
		if(!in_array($user['auth_code'], $menuAuth)){
			www("/sub/error/popup");
			return false;
		}
	}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<title><?=$site['site_name']?></title>
	
	<!-- plugin -->
      
      <!-- jquery -->
      <script type="text/javascript" src="/plugin/jquery/jquery.min.js"></script>
      
      <!-- jquery ui -->
      <link rel="stylesheet" type="text/css" href="/plugin/jquery-ui/jquery-ui.css">
      <script type="text/javascript" src="/plugin/jquery-ui/jquery-ui.js"></script>
       
		<!-- fontawesome -->
		<link rel="stylesheet" type="text/css" href="/plugin/fontawesome/all.min.css">
		
		<!-- se2 -->
		<script type="text/javascript" src="/plugin/se2/js/HuskyEZCreator.js"></script>

		<!-- spectrum color picker -->
		<link rel="stylesheet" type="text/css" href="/plugin/spectrum/spectrum.css">
		<script type="text/javascript" src="/plugin/spectrum/spectrum.js"></script>
		
	<!-- script -->
	<script type="text/javascript" src="/js/common.js?v=200928"></script>
	<script type="text/javascript" src="/js/guide.js?v=200928"></script>

		<!-- jquery minicolors -->
		<script src="/plugin/jquery-minicolors/jquery.minicolors.min.js"></script>
	<link rel="stylesheet" href="/plugin/jquery-minicolors/jquery.minicolors.css">
	
	<!-- stylesheet -->
	<link type="text/css" rel="stylesheet" href="/css/common.css?v=200928">
	<link type="text/css" rel="stylesheet" href="/css/style.css?v=200928">
</head>

<style>

	html, body { min-width: 100%; background-color: #FFF; }
	#loadingWrap > #loading { border-top-color: <?=$site['main_color']?>; }
	
	/* 나의 쪽지함 */
	#myNoteWrap > .listWrap > .tabWrap > ul > li.active { color: <?=$site['main_color']?>; }
	#myNoteWrap > .listWrap > .viewWrap > ul > li.active { border: 1px solid <?=$site['main_color']?> !important; }
	#myNoteWrap > .listWrap .sendListWrap > li:hover { border: 1px solid <?=$site['main_color']?>; }
	
	/* 버튼 */
	.btnMain { background-color: <?=$site['main_color']?>; color: #FFF; border: 1px solid rgba(0, 0, 0, 0.15); }
	
	#excelResultInfoWrap > ul > li.label { background-color: <?=$site['main_color']?>; }
	
</style>

<body>
	<div id="loadingWrap">
		<div id="loading"></div>
	</div>
	
	<div id="popupWrap">