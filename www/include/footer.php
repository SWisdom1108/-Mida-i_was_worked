	<?php # 현재 경로가 멤버스 경로가 아닐경우 서브파일 불러오기 ?>
	<?php ($accountPath != "account") ? include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerSub.php" : ""; ?>
	
	</div>
</body>
</html>