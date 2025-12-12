<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 데이터 정보추출
	$value = array(':status_code'=>$_GET['code']);
	$query = "
		SELECT MT.*
		FROM mc_db_cs_status MT
		WHERE status_code = :status_code
	";
	$view = view_pdo($query, $value);

	if(!$view){
		www("/sub/error/popup");
	}

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="modFrm" data-ajax="/ajax/my/csStatusUP" data-callback="close" data-type="수정">
			<input type="hidden" name="code" value="<?=$view['status_code']?>">
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
						<td colspan="3"><input type="text" class="txtBox" name="status_name" value="<?=dhtml($view['status_name'])?>"></td>
					</tr>
					<tr>
						<th class="important">색상</th>
						<td colspan="3"><input type="text" class="txtBox" name="color" value="<?=$view["color"]?>" coloronly ></td>
					</tr>
					<tr>
						<th>사용여부</th>
						<td style="text-align: left;" colspan="3">
							<input type="checkbox" class="toggle" name="use_yn" id="use_yn" <?=($view['use_yn'] == "Y") ? "checked" : ""?>>
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
							<input type="checkbox" class="toggle" name="number_yn" id="number_yn"  <?=($view['number_yn'] == "Y") ? "checked" : ""?>>
							<label class="toggle" for="number_yn"><div></div></label>
						</td>
						<th>정산 단위</th>
						<td><input type="text" class="txtBox" name="number_label" placeholder="ex) 원, 개 ..." value="<?=$view["number_label"]?>"></td>
					</tr>
					<tr>
						<th>상담완료여부</th>
						<td style="text-align: left;" colspan="3">
							<input type="checkbox" class="toggle" name="finish_yn" id="finish_yn" <?=($view['finish_yn'] == "Y") ? "checked" : ""?>>
							<label class="toggle" for="finish_yn"><div></div></label>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnBlack submitBtn" data-target="mod">수정</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="mod">취소</button>
	</div>
	<script>
		$(function(){
			if($("input:text[coloronly]").length > 0){	
				$("input:text[coloronly]").minicolors({});
				$("input:text[coloronly]").attr("maxlength", 7);
			}
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>