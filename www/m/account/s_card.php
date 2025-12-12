<?php
	include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/header.php";
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/visit_log.php";
	
	$membersLogoPath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['members_logo'];
	$membersLogo = getEncodedImage($membersLogoPath);

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
		<script type="text/javascript" src="/m/js/members.js"></script>
		<div id="membersWrap">
			<div>
				<div class="logoWrap">
					<img src="<?=($membersLogo) ? "data:image/jpg;base64,".$membersLogo : "/images/membersLogo.png"?>" alt="DBMG">
				</div>
				<div id="membersBox" class="login">
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
						<div class="saveWrap drag" style="opacity: 0;">
							<input type="checkbox" id="saveUserInfo" <?=($_COOKIE['username']) ? "checked" : ""?>>
							<label for="saveUserInfo" class="ch">
								<i class="fas fa-check-circle on"></i>
								<i class="far fa-circle off"></i>
								<span>로그인정보 저장</span>
							</label>
						</div>
					</div>
				</div>
				
				<div class="copyrightWrap">
					<span>ⓒ2020 Midaworks solution Ltd.</span>
				</div>
			</div>
		</div>
		
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/footer.php"; ?>