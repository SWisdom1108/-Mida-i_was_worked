<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php"; ?>

	<!-- 404 -->
	<div id="pageNotFound">
		<div class="iconWrap">
			<i class="fas fa-space-shuttle"></i>
		</div>
		<div class="conWrap">
			<b>[ ERROR ]</b>존재하지 않는 페이지이거나<br>비정상적인 경로입니다.
		</div>
	</div>
	
	<script>
		var endStatus = 0;
		var animationTimer = setInterval(function(){
			$("#pageNotFound > .iconWrap > i").toggleClass("active");
			$("#pageNotFound > .iconWrap > i").removeClass("activeEnd");

			if(endStatus == 0){
				if($("#pageNotFound > .iconWrap > i").hasClass("active")){
					endStatus = 1;
				} else {
					endStatus = 0;
				}
			} else {
				$("#pageNotFound > .iconWrap > i").addClass("activeEnd");
				setTimeout(function(){
					$("#pageNotFound > .iconWrap > i").removeClass("activeEnd");
				}, 500);
				endStatus = 0;
			}
		}, 800);
	</script>
	
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>