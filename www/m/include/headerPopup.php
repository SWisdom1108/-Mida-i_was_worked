<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php"; ?>
<?php

	# 세션 없을 경우 페이지 로딩 안함
	if(!$_SESSION['idx']){
		www("/m/sub/error/popup");
	}

	# 메뉴 접근 권한설정
	if(count($menuAuth)){
		# 메뉴 접근 권한설정값이 존재할 경우 체크
		if(!in_array($user['auth_code'], $menuAuth)){
			www("/m/sub/error/popup");
		}
	}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title>DB Manager</title>
	
	<!-- plugin -->
      
      <!-- jquery -->
      <script type="text/javascript" src="/plugin/jquery/jquery.min.js"></script>
      
      <!-- jquery ui -->
      <link rel="stylesheet" type="text/css" href="/plugin/jquery-ui/jquery-ui.css">
      <script type="text/javascript" src="/plugin/jquery-ui/jquery-ui.js"></script>

		<!-- jquery Billboard -->
		<link rel="stylesheet" href="/plugin/billboard/billboard.css">
		<script type="text/javascript" src="/plugin/billboard/d3.js"></script>
		<script type="text/javascript" src="/plugin/billboard/billboard.js"></script>
       
		<!-- fontawesome -->
		<link rel="stylesheet" type="text/css" href="/plugin/fontawesome/all.min.css">
		
		<!-- se2 -->
		<script type="text/javascript" src="/plugin/se2/js/HuskyEZCreator.js"></script>
		
	<!-- script -->
	<script type="text/javascript" src="/m/js/common.js"></script>
	
	<!-- stylesheet -->
	<link type="text/css" rel="stylesheet" href="/m/css/common.css">
	<link type="text/css" rel="stylesheet" href="/m/css/style.css">
	
	<!-- icon -->
	<link rel="icon" href="/images/favicon.png">
</head>

<style>

	html, body { height: auto; }
	.listMiniWriteWrap > li > button { background-color: <?=$site['main_color']?>; border: 1px solid <?=$site['main_color']?>; }
	#loadingWrap > #loading { border-top-color: <?=$site['main_color']?>; }
	.dbCheckDataInfoWrap > .titWrap .point { color: <?=$site['main_color']?>; }
	.dbCheckDataInfoWrap > .infoWrap { border-color: <?=$site['main_color']?>; }
	.miniListWrap > ul > li.con > b { color: <?=$site['main_color']?>; }
	
</style>

<body>
	<div id="loadingWrap">
		<div id="loading"></div>
	</div>
	
	<div id="popupWrap">