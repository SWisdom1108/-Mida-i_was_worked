<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/group/teamWP" data-callback="close" data-type="등록">
			<div class="tit">기본정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important"><?=$customLabel["tm"]?>명</th>
						<td><input type="text" class="txtBox" name="team_name"></td>
					</tr>
					<tr>
						<th>비고</th>
						<td><input type="text" class="txtBox" name="memo"></td>
					</tr>
					<tr>
						<th>사용여부<div class="miniGuideWrap" data-class="groupTeamUse"></div></th>
						<td>
							<input type="checkbox" class="toggle" name="use_yn" id="use_yn" checked>
							<label class="toggle" for="use_yn"><div></div></label>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnBlack submitBtn" data-target="write">완료</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="write">취소</button>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>