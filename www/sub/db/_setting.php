<?php

	# 메인메뉴 [ DB관리 ] 설정파일

	# 메뉴설정
	$mainMenu = "db";

	# 콘텐츠 경로설정
	array_unshift($contentsRoot, "DB관리");

	if (strpos($_SERVER['REQUEST_URI'], 'dbRecallL') !== false) {
    	$mainMenu = "db_recall";		
		# 콘텐츠 경로설정
		array_unshift($contentsRoot, "회수 DB관리");

	} 
?>