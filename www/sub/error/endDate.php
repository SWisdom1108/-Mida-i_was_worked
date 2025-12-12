<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	if(!$programDateInfo->invert){
		www("/");
	}

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

?>
	
	<style>
		html, body { width: 100%; height: 100%; overflow: hidden; }
		
		@media (max-width: 700px){
			#programEndInfoPageWrap { padding: 50px 20px; }
			#programEndInfoPageWrap > div { width: 100%; top: 0; left: 0; margin-left: 0; margin-top: 0; position: relative; float: left; }
		}
	</style>

	<div id="programEndInfoPageWrap">
		<div>
			<div class="logoWrap">
				<img src="/images/programEndLogo.png" alt="">
			</div>
			<div class="titWrap">
				<b>프로그램</b> 서비스 기간이 <b>만료</b>되었습니다.
			</div>
			<div class="conWrap">
				<ul>
					<li>본 프로그램(디비매니저)의 서비스 기간이 만료되었습니다.</li>
					<li>해당 프로그램의 사용기간을 연장하고싶으시다면 고객센터에 문의해주시길 바랍니다.</li>
				</ul>
			</div>
			<div class="btnWrap">
				<span>고객센터 <b class="lp05">1800-7439</b></span>
			</div>
		</div>
	</div>
	
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>