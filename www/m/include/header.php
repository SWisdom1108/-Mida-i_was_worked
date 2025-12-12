<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php"; ?>
<?php

	if(!$programDateInfo->invert){
		# 모바일체크
		if(!preg_match($mCheck, $_SERVER['HTTP_USER_AGENT'])){
			www("/");
			return false;
		}

		# 회원정보 존재여부에 따른 리턴 이벤트
		$accountPath = explode("/", $_SERVER['REQUEST_URI'])[2];
		$accountPath = explode("/", $accountPath)[0];
		if($accountPath == "account"){
			if($_SESSION['idx']){
				www("/m/index.php");
			}
		} else {
			if(!$_SESSION['idx']){
				www("/m/account/login");
			}
		}
	}

	$faviconpath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['favicon'];
	$favicon = getEncodedImage($faviconpath);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?=$site['site_name']?></title>
	
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
	<link rel="icon" href="<?=($favicon) ? "data:image/jpg;base64,".$favicon : "/images/favicon.png"?>">
</head>

<style>

	#loadingWrap > #loading { border-top-color: <?=$site['main_color']?>; }
	
<?php if($accountPath != "account"){ ?>
	
	/* 버튼 */
	.btnMain { background-color: <?=$site['main_color']?>; color: #FFF; border: 1px solid rgba(0, 0, 0, 0.15); }
	.listMiniWriteWrap > li > button { background-color: <?=$site['main_color']?>; border: 1px solid <?=$site['main_color']?>; }
	
	#headerWrap { background-color: <?=$site['main_color']?>; }
	#headerWrap > ul > li.logo .point { color: <?=$site['main_color']?>; }
	#dashboardWrap > .background { background-color: <?=$site['main_color']?>; }
	#goToTopWrap > button { background-color: <?=$site['main_color']?>; }
	
	.viewWrap > table th { background-color: #F8F8F8; }
	#pageTitleWrap { background-color: <?=$site['main_color']?>; }
	#dashboardWrap > .dataCntWrap { background-color: <?=$site['main_color']?>; }
	.distCodeListWrap  { background-color: <?=$site['main_color']?>; }
	#tabMenuListWrap > ul > li.active > a { border-bottom: 3px solid <?=$site['main_color']?>; }
	.searchControlBtn.active { border: 1px solid <?=$site['main_color']?>; background-color: <?=$site['main_color']?>; }
	.dataListWrap > ul > li > .csLogBtn.click { border: 1px solid <?=$site['main_color']?>; color: <?=$site['main_color']?>; }
	.dataInfoSimpleWrap > ul { background-color: <?=$site['main_color']?>; }
	.dataViewWrap > .infoWrap > ul { border: 2px solid <?=$site['main_color']?>; }
	
	.distCodeListWrap > ul > li > a { border: 1px solid <?=$site['main_color']?>; color: <?=$site['main_color']?>; }
	.distCodeListWrap > ul > li.active > a { border: 2px solid <?=$site['main_color']?>; }
	
<?php } else { ?>
	#membersWrap { background-color: <?=$site['main_color']?>; }
	#membersBox > .formWrap > .titWrap > span.point { color: <?=$site['main_color']?>; }
	#membersBox > .formWrap > .inputWrap > input:focus { border: 1px solid <?=$site['main_color']?>; }
	#membersBox > .formWrap > .saveWrap > label > i.on { color: <?=$site['main_color']?>; }
	#membersBox > .formWrap > .btnWrap > button { background-color: <?=$site['main_color']?>; }
<?php } ?>
	
</style>

<body>
	<div id="loadingWrap">
		<div id="loading"></div>
	</div>
	
	<div id="wrap">
	<?php # 현재 경로가 멤버스 경로가 아닐경우 서브파일 불러오기 ?>
	<?php ($accountPath != "account") ? include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/headerSub.php" : ""; ?>