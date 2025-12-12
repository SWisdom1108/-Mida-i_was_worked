<style>
	.bbsNavigationWrap { display: none; }
</style>

<div class="bbsNoDataWrap">
	<div class="iconWrap">
		<i class="fas fa-space-shuttle"></i>
	</div>
	<div class="conWrap">
		<b>[ ERROR ]</b>삭제되었거나 존재하지 않는 게시글입니다.
	</div>
</div>

<script>
	var endStatus = 0;
	var animationTimer = setInterval(function(){
		$(".bbsNoDataWrap > .iconWrap > i").toggleClass("active");
		$(".bbsNoDataWrap > .iconWrap > i").removeClass("activeEnd");
		
		if(endStatus == 0){
			if($(".bbsNoDataWrap > .iconWrap > i").hasClass("active")){
				endStatus = 1;
			} else {
				endStatus = 0;
			}
		} else {
			$(".bbsNoDataWrap > .iconWrap > i").addClass("activeEnd");
			setTimeout(function(){
				$(".bbsNoDataWrap > .iconWrap > i").removeClass("activeEnd");
			}, 500);
			endStatus = 0;
		}
	}, 800);
</script>