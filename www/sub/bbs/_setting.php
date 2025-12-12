<?php

	# 메인메뉴 [ 고객센터 ] 설정파일

	# 메뉴설정
	$mainMenu = "bbs";

	# 콘텐츠 경로설정
	array_unshift($contentsRoot, "고객센터");

	# r_date가 stop일 경우 return
	if($cmpy['r_date'] == "stop"){
		www("/sub/error/");
	}

?>