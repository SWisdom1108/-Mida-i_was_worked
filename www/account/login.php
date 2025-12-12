<?php
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/visit_log.php";

	$membersLogoPath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['members_logo'];
	$membersLogo = getEncodedImage($membersLogoPath);
	$membersBackgroundPath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['members_bg'];
	$membersBackground = getEncodedImage($membersBackgroundPath);
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
							<input type="text" id="username" placeholder="ID" value="<?=$_COOKIE['username']?>">
							<input type="password" id="userpassword" placeholder="PW" value="<?=$_COOKIE['userpassword']?>">
						</div>
						<div class="btnWrap">
							<button type="button" id="loginBtn">LOGIN</button>
						</div>
						<div class="saveWrap drag">
							<input type="checkbox" id="saveUserInfo" <?=($_COOKIE['username']) ? "checked" : ""?>>
							<label for="saveUserInfo" class="ch">
								<i class="fas fa-check-circle on"></i>
								<i class="far fa-circle off"></i>
								<span>로그인정보 저장</span>
							</label>
						</div>
						<div class="copyrightWrap lp05">
							ⓒ<?=Date("Y")?> Midaworks solution Ltd.
						</div>
					</div>
				</div>
			</div>
		</div>
		
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>