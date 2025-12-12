<?php

	# 알림 개수 추출
	$myNoticeCnt = view_sql("SELECT COUNT(*) AS cnt FROM mt_notification WHERE use_yn = 'Y' AND read_date IS NULL AND m_idx = '{$user['idx']}'")['cnt'];

	# 쪽지 개수 추출
	$myNoteCnt = view_sql("SELECT COUNT(*) AS cnt FROM mt_note WHERE use_yn = 'Y' AND view_date IS NULL AND receive_idx = '{$user['idx']}'")['cnt'];

	$topLogoPath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['top_logo'];
	$topLogo = getEncodedImage($topLogoPath);

?>
		<script type="text/javascript" src="/js/guide.js"></script>
		<!-- 공용 상단영역 -->
		<div id="headerWrap"<?=($_COOKIE["leftSideSizeStatus"]) ? ' class="active"' : ""?>>
			
				<div class="logoWrap">
					<a href="/" title="홈"><img src="<?=($topLogo) ? "data:image/jpg;base64,".$topLogo : "/images/topLogo_01.png"?>" alt="DBMG"></a>
					<div class="background"></div>
				</div>

			<div class="conWrap">
				<div class="topWrap">
					<ul class="left">
						<li class="icon"><a href="#" title="메뉴컨트롤" class="leftSideSizeControlBtn"><i class="fas fa-bars" style="color: #FFF;"></i></a></li>
						<li class="icon"><a href="/" title="홈"><i class="fas fa-home"></i></a></li>
						<?php
						if($user['auth_code'] != '006' && $user['auth_code'] != '007'){
						?>
						<li class="icon">
							<a href="#" title="알림" id="myNotificationBtn">
								<i class="far fa-bell"></i>
							</a>
						<?php if($myNoticeCnt){ ?>
							<span class="cnt"><?=$myNoticeCnt?></span>
						<?php } ?>
						</li>
						<?php }?>
					<?php
						$noticeItem = view_sql("
							SELECT MT.*
							FROM mt_bbs MT
							WHERE use_yn = 'Y'
							AND bbs_code = '001'
							AND date_format(now(), '%Y-%m-%d') >= date_format(etc3, '%Y-%m-%d')
							AND date_format(now(), '%Y-%m-%d') <= date_format(etc4, '%Y-%m-%d')
							ORDER BY idx DESC
							LIMIT 0, 1
						");
						
						if($noticeItem){
					?>
						<li class="notice">
							<a href="/sub/bbs/bbs?bbs=001&inc=V&idx=<?=$noticeItem['idx']?>">
								<b>[공지]</b> <?=dhtml($noticeItem['title'])?>
								<span class="lp05"><?=date("Y-m-d", strtotime($noticeItem['reg_date']))?></span>
							</a>
						</li>
					<?php } ?>
					</ul>
					<ul class="right">
						<?php if($user['auth_code'] == "001" || $user['auth_code'] == "002"){ ?>
							<li class="total">
								<div class="myTotal">
									<span class="label"><i class="fas fa-power-off"></i>MY TOTAL</span>
									<span class="value"><?=($site['slot'] >= 9999) ? '<i class="fas fa-infinity"></i>' : number_format($site['slot'])?></span>
								</div>
								<div class="etc">
									<span class="label">잔여사용자수</span>
									<span class="value">
										<?=($site['slot'] >= 9999) ? '<i class="fas fa-infinity"></i>' : number_format($site['slot'] - $site['slot_r'])."개"?>
									</span>
								</div>
							</li>
						<?php } ?>
						<li class="basic">
							<a href="#" title="쪽지함" id="myNoteBtn">
								<i class="far fa-comment-dots"></i>
								<?php if($myNoteCnt){ ?>
									<span class="cnt"><?=$myNoteCnt?></span>
								<?php } ?>
							</a>
							<div class="background"></div>
						</li>
						<li class="basic"><a href="/sub/bbs/bbs?bbs=001&inc=L" title="공지사항"><i class="far fa-file-alt"></i></a><div class="background"></div></li>
						<li class="user lp05">
							<span class="user_id"><?=$user['m_id']?></span>
							<span class="user_name"><?=$user['m_name']?>(<?=$user['auth_name']?>)!</span>
							<a href="/account/logout" title="로그아웃"><i class="fas fa-sign-out-alt"></i></a>
							<div class="background"></div>
						</li>
					</ul>
				</div>
				<div class="mainMenuWrap">
					<ul>
						<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerMainMenu.php"; ?>
					</ul>
				</div>
				<script type="text/javascript">
					$("#mainMenu_<?=$mainMenu?>").addClass("active");
				</script>
			</div>
			
		</div>
		
		<!-- 공용 좌측영역 -->
		<div id="leftSideWrap"<?=($_COOKIE["leftSideSizeStatus"]) ? ' class="active"' : ""?>>
			<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerLeftSide.php"; ?>
		</div>
		
		<!-- 콘텐츠 영역 -->
		<div id="mainContentsWrap"<?=($_COOKIE["leftSideSizeStatus"]) ? ' class="active"' : ""?>>

			<?php if($mainMenu){ ?>
				<div class="contentsTitleWrap">
					<div class="conWrap">
						<div class="left">
							<span class="sec"><?=$contentsTitle?></span>
						</div>
						<div class="right">
							<span>HOME</span>
							<?php for($i = 0; $i < count($contentsRoot); $i++){ ?>
							<?php $class = (count($contentsRoot) - 1 == $i) ? "active" : ""; ?>
								<i class="fas fa-angle-right"></i>
								<span class="<?=$class?>"><?=$contentsRoot[$i]?></span>
							<?php } ?>
						</div>
					</div>
					<?php if($contentsInfo){ ?>
						<div class="exWrap">
							<div class="background"></div>
							<i class="far fa-caret-square-right"></i><span><?=$contentsInfo?></span>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
			
			<div class="contentsWrap">
			<?php $andQuery = "WHERE 1=1 "; # andQuery 초기화 ?>