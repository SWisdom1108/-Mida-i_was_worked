<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/my/smsRequestWP" data-callback="close" data-type="요청">
			<div class="tit">요청정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important">이름</th>
						<td><input type="text" class="txtBox" name="sent_name"></td>
						<th class="important">연락처</th>
						<td><input type="text" class="txtBox" name="sent_tel" numonly></td>
					</tr>
					<tr>
						<th>통신가입증명원</th>
						<td class="tl" colspan="3">
							<input type="file" name="file" id="excelFile">
							<label for="excelFile" class="typeBtn btnGreen01"><i class="fas fa-search"></i>파일선택</label>
							<span id="excelFileName">파일을 선택해주세요.</span>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnBlack submitBtn" data-target="write">요청하기</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="write">취소</button>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>