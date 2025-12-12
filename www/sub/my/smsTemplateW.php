<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/my/smsTemplateWP" data-callback="close" data-type="등록">
			<div class="tit">기본정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important">템플릿명</th>
						<td colspan="3"><input type="text" class="txtBox" name="title"></td>
					</tr>
					<tr>
						<th>내용</th>
						<td class="tl" colspan="3">
							<textarea class="txtBox" name="contents" id="contents"></textarea>
							<p id="contentsByte" style="width: 100%; float: left; margin-top: 5px; text-align: left; letter-spacing: -0.5px;">0 Byte</p>
						</td>
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
		</form>
	</div>
	
	<div class="guideWrap">
		<div>
			<div class="iconWrap">
				<i class="fas fa-info-circle"></i>
			</div>
			<ul class="conWrap">
				<li class="basic">- 템플릿 내용의 길이가 <b>90 Byte</b>보다 클 경우 MMS로 전송이 됩니다.</li>
				<li class="basic">- MMS의 내용길이 제한은 <b>2000 Byte</b>입니다.</li>
				<li class="basic">- 템플릿 내용에 <b>#{고객명}</b>을 입력하시면 발송시 수신자명으로 바뀌어 전송됩니다.</li>
			</ul>
		</div>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnBlack submitBtn" data-target="write">완료</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="write">취소</button>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			$(document).on("focus focusin focusout change keyup keydown", "#contents", function(){
				$("#contentsByte").text(byteCheck($(this)) + " Byte");
			});
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>