<?php

	# 메인메뉴 [ 조직관리 ] 설정파일

	# 메뉴설정
	$mainMenu = "group";

	# 콘텐츠 경로설정
	array_unshift($contentsRoot, "조직관리");

	if($secMenu != "myTeamMember"){
		# 메뉴 접근 권한설정
		# 001(최고관리자) 002(관리자) 003(생산마스터)
		# 004(팀마스터) 005(영업자)
		$menuAuth = ["001", "002"];
	}

?>