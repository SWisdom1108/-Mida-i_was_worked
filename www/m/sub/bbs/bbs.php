<?php

	# 콘텐츠설정
	$pageTitle = "공지사항";

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/header.php";

?>

	<div class="dataSectionWrap" style="padding: 20px;">
		<?php

			# 게시판모듈
			$bbsCode = "001"; # 게시판코드
			$incType = $_GET['inc']; # 게시판형태
			$pmType = "m"; # 피씨 모바일 구분
			$userType = "user";
			$userTable = "mt_member"; # 유저 테이블
			$userNameColum = "m_name"; # 유저이름 컬럼명
			include_once $_SERVER['DOCUMENT_ROOT']."/mida/module/bbs/bbs.php";

		?>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/footer.php"; ?>