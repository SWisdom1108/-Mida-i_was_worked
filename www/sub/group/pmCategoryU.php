<?php

	# 메뉴설정
	$secMenu = "pm";
	$trdMenu = "pmCategory";
	
	# 콘텐츠설정
	$contentsTitle = "생산업체 카테고리 수정";
	$contentsInfo = "DB를 공급할 생산업체 카테고리를 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "생산업체 카테고리관리");
	array_push($contentsRoots, "수정");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 데이터 추출
	$value = array(':category_code'=>$_GET['category_code']);
	$query = "
		SELECT MT.*
		FROM mc_member_cmpy_category MT
		WHERE use_yn = 'Y'
		AND category_code = :category_code
	";
	$view = view_pdo($query, $value);

	if(!$view){
		include_once "{$_SERVER['DOCUMENT_ROOT']}/sub/error/index.php";
		return false;
	}

?>
	
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="modFrm" data-ajax="/ajax/group/pmCategoryUP" data-callback="/sub/group/pmCategoryV?category_code=<?=$view['category_code']?>" data-type="수정">
			<input type="hidden" name="category_code" value="<?=$view['category_code']?>">
			
			<div class="tit">생산업체 카테고리 정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important">카테고리명</th>
						<td><input type="text" name="category_name" class="txtBox" value="<?=$view['category_name']?>"></td>
					</tr>
					<tr>
						<th>카테고리단계</th>
						<td class="lp05">
                            <select class="txtBox" name="category_depth">
			                	<option value="1" <?=($view['category_depth'] == "1") ? "selected" : '' ?>>1</option>
			                	<option value="2" <?=($view['category_depth'] == "2") ? "selected" : '' ?>>2</option>
                                <option value="3" <?=($view['category_depth'] == "3") ? "selected" : '' ?>>3</option>
			                	<option value="4" <?=($view['category_depth'] == "4") ? "selected" : '' ?>>4</option>
                                <option value="5" <?=($view['category_depth'] == "5") ? "selected" : '' ?>>5</option>
			                </select>
                        </td>
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
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="/sub/group/pmCategoryV?category_code=<?=$view['category_code']?>" class="typeBtn btnGray02" title="취소"><i class="fas fa-arrow-left"></i>취소</a> 
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnBlack submitBtn" data-target="mod"><i class="far fa-check-circle"></i>완료</button>
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>