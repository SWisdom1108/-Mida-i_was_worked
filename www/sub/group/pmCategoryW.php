<?php

	# 메뉴설정
	$secMenu = "pm";
	$trdMenu = "pmCategory";
	
	# 콘텐츠설정
	$contentsTitle = "생산업체 카테고리 등록";
	$contentsInfo = "DB를 공급할 생산업체 카테고리를 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "생산업체 카테고리관리");
	array_push($contentsRoots, "등록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

?>
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/group/pmCategoryWP" data-callback="/sub/group/pmCategoryL" data-type="등록">
			<div class="tit"><i class="fas fa-user-circle"></i>카테고리정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important">카테고리명</th>
						<td><input type="text" name="category_name" class="txtBox"></td>
					</tr>
					<tr>
						<th>카테고리단계</th>
						<td class="lp05">
                            <select class="txtBox" name="category_depth">
			                	<option value="1">1</option>
			                	<option value="2">2</option>
                                <option value="3">3</option>
			                	<option value="4">4</option>
                                <option value="5">5</option>
			                </select>
                        </td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="<?=$_SESSION['prevURL']?>" class="typeBtn btnGray02" title="취소"><i class="fas fa-arrow-left"></i>취소</a> 
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnBlack submitBtn" data-target="write"><i class="far fa-check-circle"></i>완료</button>
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>