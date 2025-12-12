<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 변수설정
	$date = ($_GET["date"]) ? $_GET["date"] : date("Y-m-d");
	$year = date("Y", strtotime($date));
	$month = date("m", strtotime($date));
	$day = date("d", strtotime($date));

?>

	<script type="text/javascript">
		window.parent.location.href = "/sub/schedule/scheduleL?year=<?=$year?>&month=<?=$month?>&day=<?=$day?>";
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>