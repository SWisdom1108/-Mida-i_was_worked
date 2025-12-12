<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/my/csStatusWP" data-callback="close" data-type="등록">
			<div class="tit">DB상담구분값 정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important">구분값명</th>
						<td colspan="3"><input type="text" class="txtBox" name="status_name"></td>
					</tr>
					<tr>
						<th class="important">색상</th>
						<td colspan="3"><input type="text" class="txtBox" name="color" value="#666" coloronly ></td>
					</tr>
					<tr>
						<th>사용여부</th>
						<td style="text-align: left;" colspan="3">
							<input type="checkbox" class="toggle" name="use_yn" id="use_yn" checked>
							<label class="toggle" for="use_yn"><div></div></label>
						</td>
					</tr>
				</tbody>
			</table>
			
			<div class="tit">기타구분설정</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th>정산사용여부</th>
						<td style="text-align: left;">
							<input type="checkbox" class="toggle" name="number_yn" id="number_yn">
							<label class="toggle" for="number_yn"><div></div></label>
						</td>
						<th>정산 단위</th>
						<td><input type="text" class="txtBox" name="number_label" placeholder="ex) 원, 개 ..."></td>
					</tr>
					<tr>
						<th>상담완료여부</th>
						<td style="text-align: left;" colspan="3">
							<input type="checkbox" class="toggle" name="finish_yn" id="finish_yn">
							<label class="toggle" for="finish_yn"><div></div></label>
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