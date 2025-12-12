<?php

	# 메뉴설정
	$secMenu = "pm";
	$trdMenu = "pmCategory";
	
	# 콘텐츠설정
	$contentsTitle = "생산업체 카테고리 정보";
	$contentsInfo = "DB를 공급할 생산업체 카테고리를 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "생산업체 카테고리관리");
	array_push($contentsRoots, "정보");

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

?>
	
	<div class="viewWrap">
		<div class="tit">생산업체 카테고리 정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="80%">
			</colgroup>
			<tbody>
				<tr>
					<th>카테고리명</th>
					<td class="lp05"><?=$view['category_name']?></td>
				</tr>
				<tr>
				<tr>
					<th>카테고리명단계</th>
					<td><?=$view['category_depth']?></td>
				</tr>
				<tr>
					<th>사용여부</th>
					<td><?=($view['use_yn'] == "Y") ? "사용중" : "<span style='color: #CCC;'>사용안함</span>"?></td>
				</tr>
			</tbody>
		</table>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="<?=$_SESSION['prevURL']?>" class="typeBtn btnGray02" title="이전"><i class="fas fa-arrow-left"></i>이전</a> 
		</div>
		<div class="right">
			<a href="/sub/group/pmCategoryU?category_code=<?=$view['category_code']?>" class="typeBtn btnMain" title="수정"><i class="fas fa-edit"></i>수정</a> 
		</div>
	</div>
	

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>