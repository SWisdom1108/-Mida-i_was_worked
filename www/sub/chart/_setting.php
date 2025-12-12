<?php

	# 메인메뉴 [ 통계 ] 설정파일

	# 메뉴설정
	$mainMenu = "chart";

	# 콘텐츠 경로설정
	array_unshift($contentsRoot, "통계");

	# r_date가 stop일 경우 return
	if($cmpy['r_date'] == "stop"){
		www("/sub/error/");
	}

?>