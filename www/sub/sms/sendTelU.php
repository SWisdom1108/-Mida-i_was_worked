<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 데이터 추출
	$value = array(':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
		FROM mt_sms_tel MT
		WHERE idx = :idx
	";
	$view = view_pdo($query, $value);

	if(!$view){
		return false;
	}

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/sms/sendTelUP" data-callback="close" data-type="수정">
			<input type="hidden" name="idx" value="<?=$view["idx"]?>">
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
						<th class="important">이름</th>
						<td><input type="text" class="txtBox" name="sent_name" value="<?=$view["sent_name"]?>"></td>
						<th>연락처</th>
						<td class="lp05"><?=$view["sent_tel"]?></td>
					</tr>
					<tr>
						<th>메인번호여부</th>
						<td style="text-align: left;">
							<input type="checkbox" class="toggle" name="main_yn" id="main_yn" <?=($view['main_yn'] == "Y") ? "checked" : ""?>>
							<label class="toggle" for="main_yn"><div></div></label>
						</td>
						<th>사용여부</th>
						<td style="text-align: left;">
							<input type="checkbox" class="toggle" name="use_yn" id="use_yn" <?=($view['use_yn'] == "Y") ? "checked" : ""?>>
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
	
	<script type="text/javascript">
		$(function(){
			
			$("#main_yn").change(function(){
				if(!$("#use_yn").prop("checked")){
					$(this).prop("checked", false);
				}
			});
			
			$("#use_yn").change(function(){
				if(!$(this).prop("checked")){
					$("#main_yn").prop("checked", false);
				}
			});
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>