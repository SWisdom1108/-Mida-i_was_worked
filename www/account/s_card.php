<?php
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/visit_log.php";


	$membersLogoPath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['members_logo'];
	$membersLogo = getEncodedImage($membersLogoPath);
	$membersBackgroundPath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['members_bg'];
	$membersBackground = getEncodedImage($membersBackgroundPath);

	$username = $_POST['id'];
	$userpw = $_POST['pw'];
	$view = view_sql("SELECT * FROM mt_member WHERE use_yn = 'Y' AND m_id = '{$username}' AND password('{$userpw}') = m_pw");
	if(!$view){
		return false;
	}

?>
<script>
	var mainTel = "<?=$mainCmpy['ceo_tel']?>";
</script>
	
		<!-- 로그인 영역 -->
		<script type="text/javascript" src="/js/members.js"></script>
		<div id="membersWrap">
			<div class="background"></div>
			<div>
				<div id="membersBox" class="login">
					<div class="visualWrap">
						<img src="<?=($membersLogo) ? "data:image/jpg;base64,".$membersLogo : "/images/membersLogo.png"?>" alt="DBMG" class="logo">
						<img src="<?=($membersBackground) ? "data:image/jpg;base64,".$membersBackground : "/images/loginVisual.png"?>" alt="" class="bg" style="opacity: <?=($site['members_bg']) ? "1" : "0.4"?>;">
						<?php if(!$site['members_bg']){ ?>
							<div class="bgWrap" style="background-color: <?=$site['main_color']?>;"></div>
							<div class="bgDarkWrap"></div>
						<?php } ?>
					</div>
					
					<div class="formWrap">
						<div class="titWrap">
							<span class="point">MEMBERS</span>
							<span>LOGIN</span>
						</div>
						<div class="inputWrap">
							<input type="hidden" id="username" value="<?=$view['m_id']?>">
							<input type="password" id="first" placeholder="<?=$view['snum_01']?>번 앞 2숫자" value="" maxlength='2'>
							<input type="password" id="second" placeholder="<?=$view['snum_02']?>번 뒤 2숫자" value="" maxlength='2'>
						</div>
						<div class="btnWrap">
							<button type="button" id="loginBtn2">LOGIN</button>
						</div>
						<div class="saveWrap drag">
							<span style="text-align: left; float: left; font-size: 13px; color : #cc3333; font-weight: 500;">*해당 페이지를 인증없이 벗어나시면 5분간 로그인이 제한됩니다.</span>
						</div>
						<div class="copyrightWrap lp05">
							ⓒ<?=Date("Y")?> Midaworks solution Ltd.
						</div>
					</div>
				</div>
			</div>
		</div>
		
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>