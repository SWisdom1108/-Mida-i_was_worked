<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 메뉴설정
	$secMenu = "theme";
	
	# 콘텐츠설정
	$contentsTitle = "테마설정";
	$contentsInfo = "홈페이지의 전체적인 테마를 설정하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "테마설정");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	$membersLogoPath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['members_logo'];
	$membersLogo = getEncodedImage($membersLogoPath);
	$membersBackgroundPath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['members_bg'];
	$membersBackground = getEncodedImage($membersBackgroundPath);
	$topLogoPath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['top_logo'];
	$topLogo = getEncodedImage($topLogoPath);
	$faviconpath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['favicon'];
	$favicon = getEncodedImage($faviconpath);

?>
	
	<style>
		td > img { z-index: 2; }
		td > .bgWrap { position: absolute; left: 15px; top: 10px; width: 440px; height: 487px; }
		td > .bgDarkWrap { position: absolute; left: 15px; top: 10px; width: 440px; height: 487px; background-color: #000; opacity: 0.6; z-index: 3; }
	</style>
	
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="modFrm" data-ajax="/ajax/my/themeUP" data-callback="/sub/my/themeU" data-type="수정">
			<div class="tit">테마정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important">홈페이지명</th>
						<td><input type="text" class="txtBox" name="site_name" value="<?=dhtml($site['site_name'])?>"></td>
						<th class="important">메인색상</th>
						<td><input type="text" class="txtBox" name="main_color" value="<?=$site['main_color']?>" coloronly></td>
					</tr>
					<tr>
						<th>로그인 로고</th>
						<td colspan="3">
							<img src="<?=($membersLogo) ? "data:image/jpg;base64,".$membersLogo : "/images/membersLogo.png" ?>" id="imagePreview_loginLogo" style="background-color: #000;">
							<br><br>
							<input type="file" accept="image/*" id="loginLogo" name="loginLogo" class="imageUploader" data-target="loginLogo">
							<label for="loginLogo" class="typeBtn btnOrange"><i class="fas fa-upload"></i>파일업로드</label>
						</td>
					</tr>
					<tr>
						<th>로그인 배경</th>
						<td colspan="3">
							<img src="<?=($site['members_bg']) ? "data:image/jpg;base64,".$membersBackground : "/images/loginVisual.png"?>" id="imagePreview_loginBg" style="opacity: <?=($site['members_bg']) ? "1" : "0.4"?>; width: 440px; height: 487px;">
							<?php if(!$site['members_bg']){ ?>
								<div class="bgWrap" style="background-color: <?=$site['main_color']?>;"></div>
								<div class="bgDarkWrap"></div>
							<?php } ?>
							<br><br>
							<input type="file" accept="image/*" id="loginBg" name="loginBg" class="imageUploader" data-target="loginBg">
							<label for="loginBg" class="typeBtn btnOrange"><i class="fas fa-upload"></i>파일업로드</label>
						</td>
					</tr>
					<tr>
						<th>메인 로고</th>
						<td colspan="3">
							<img src="<?=($site['top_logo']) ? "data:image/jpg;base64,".$topLogo : "/images/topLogo_01.png"?>" id="imagePreview_mainLogo">
							<br><br>
							<input type="file" accept="image/*" id="mainLogo" name="mainLogo" class="imageUploader" data-target="mainLogo">
							<label for="mainLogo" class="typeBtn btnOrange"><i class="fas fa-upload"></i>파일업로드</label>
						</td>
					</tr>
					<tr>
						<th>파비콘</th>
						<td colspan="3">
							<img src="<?=($site['favicon']) ? "data:image/jpg;base64,".$favicon : "/images/favicon.png"?>" id="imagePreview_favicon">
							<br><br>
							<input type="file" accept="image/*" id="favicon" name="favicon" class="imageUploader" data-target="favicon">
							<label for="favicon" class="typeBtn btnOrange"><i class="fas fa-upload"></i>파일업로드</label>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnBlack submitBtn" data-target="mod"><i class="far fa-check-circle"></i>완료</button>
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>