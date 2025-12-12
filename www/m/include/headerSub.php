<?php

	# 공지사항 띄우기 여부
	$noticeOpenStatus = false;
	if($user['auth_code'] == "007"){
		if($user['mida_yn'] == "N"){
			$noticeOpenStatus = true;
		}
	} else {
		$noticeOpenStatus = true;
	}

?>
		<!-- 공용 상단영역 -->
		<div id="headerWrap">
			<ul>
				<li class="icon"><i class="fas fa-bars leftMenuOpenBtn"></i></li>
				<li class="logo"><a href="/m/index.php"><span class="point">DB</span>Manager</a></li>
				<li class="icon"><a href="/m/sub/my/myV"><i class="fas fa-cog"></i></a></li>
			</ul>
		</div>
		
		<!-- 공용 메뉴영역 -->
		<div id="leftMenuWrap">
			<div class="titWrap">
				<div class="left">DB MENU</div>
				<div class="right"><i class="fas fa-times-circle leftMenuCloseBtn"></i></div>
			</div>
			
			<ul class="userInfoWrap">
				<li class="icon"><i class="fas fa-user-circle"></i></li>
				<li class="info">
					<span class="name"><?=$user['m_name']?></span>
					<span class="auth"><?=$user['auth_name']?></span>
				</li>
			</ul>
			
			<ul class="mainMenu">
				<li><a href="/m/sub/my/myV">나의정보<i class="fas fa-angle-right"></i></a></li>
				
	 		<?php if($user['auth_code'] == "001" || $user['auth_code'] == "002"){ ?>
				<li><a href="/m/sub/db/dbAllL">DB통합관리<i class="fas fa-angle-right"></i></a></li>
				<li><a href="/m/sub/db/dbTeamL">DB분배관리<i class="fas fa-angle-right"></i></a></li>
	 		<?php } else { ?>
				<li><a href="/m/sub/db/dbL">DB통합관리<i class="fas fa-angle-right"></i></a></li>
				<?php if($user['auth_code'] == "004"){ ?>
				<li><a href="/m/sub/db/dbDistL">DB분배관리<i class="fas fa-angle-right"></i></a></li>
				<?php } ?>
	 		<?php } ?>
			
				<li><a href="/m/sub/bbs/bbs?inc=L">공지사항<i class="fas fa-angle-right"></i></a></li>
			</ul>
			
			<ul class="btmMenu">
				<li><a href="/m/account/logout">로그아웃<i class="fas fa-sign-out-alt"></i></a></li>
			</ul>
		</div>
		
		<!-- 콘텐츠 영역 -->
		<div id="mainContentsWrap">
		
			<?php if($pageTitle){ ?>
				<div id="pageTitleWrap"><a href="/m/index.php"><i class="fas fa-arrow-right"></i></a><?=$pageTitle?></div>
			<?php } ?>
			
			<?php if(count($tabMenuList)){ ?>
				<div id="tabMenuListWrap">
					<ul>
					<?php for($i = 0; $i < count($tabMenuList); $i++){ ?>
					<?php $tabMenuInfo = explode("@", $tabMenuList[$i]); ?>
					<?php $tabMenuClass = ($tabMenuInfo[1] == $tabMenu) ? "active" : ""; ?>
						<li class="<?=$tabMenuClass?>"><a href="/m/sub<?=$tabMenuInfo[2]?>"><?=$tabMenuInfo[0]?></a></li>
					<?php } ?>
					</ul>
				</div>
			<?php } ?>
		
			<div class="contentsWrap">
			<?php $andQuery = "WHERE 1=1 "; # andQuery 초기화 ?>