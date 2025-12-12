<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 데이터 정보추출
	$value = array(':type_code'=>$_GET['code']);
	$query = "
		SELECT MT.*
		FROM mc_schedule_type MT
		WHERE type_code = :type_code
	";
	$view = view_pdo($query, $value);

	if(!$view){
		www("/sub/error/popup");
	}

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="modFrm" data-ajax="/ajax/my/scheduleTypeUP" data-callback="close" data-type="수정">
			<input type="hidden" name="code" value="<?=$view['type_code']?>">
			<div class="tit">일정구분값 정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important">구분값명</th>
						<td><input type="text" class="txtBox" name="type_name" value="<?=dhtml($view['type_name'])?>"></td>
					</tr>
					<tr>
						<th>우선순위</th>
						<td style="text-align: left;"><input type="text" class="txtBox" name="sort" value="<?=$view["sort"]?>"></td>
					</tr>
					<tr>
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
		<button type="button" class="typeBtn btnBlack submitBtn" data-target="mod">수정</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="mod">취소</button>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>