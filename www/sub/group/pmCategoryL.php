<?php

	# 메뉴설정
	$secMenu = "pm";
	$trdMenu = "pmCategory";
	
	# 콘텐츠설정
	$contentsTitle = "생산업체 카테고리";
	$contentsInfo = "DB를 공급할 생산업체 카테고리를 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "생산업체 카테고리관리");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND use_yn = 'Y'";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mc_member_cmpy_category");

?>
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="reg_date ASC">등록일시 오름차순</option>
				<option value="reg_date DESC">등록일시 내림차순</option>
			</select>
			<select class="listSet" id="listCnt">
				<option value="15">15개씩 보기</option>
				<option value="30">30개씩 보기</option>
				<option value="50">50개씩 보기</option>
				<option value="100">100개씩 보기</option>
				<option value="9999999">전체 보기</option>
			</select>
		</div>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="/sub/group/pmCategoryW" class="typeBtn btnMain big"><i class="fas fa-plus-circle"></i>카테고리등록</a>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="24%">
				<col width="24%">
				<col width="24%">
				<col width="24%">
			</colgroup>
			<thead>
				<tr>
					<th >NO</th>
					<th >카테고리명</th>
					<th >카테고리단계</th>
					<th >사용여부</th>
					<th >등록일시</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
					FROM mc_member_cmpy_category MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr class="rowMove" onclick="www('/sub/group/pmCategoryV?category_code=<?=$row['category_code']?>');">
					<td class="lp05"><?=listNo()?></td>
					<td><?=dhtml($row['category_name'])?></td>
					<td><?=$row['category_depth']?></td>
                    <td><?=($row['use_yn'] == "Y") ? "사용중" : "<span style='color: #CCC;'>사용안함</span>"?></td>
					<td class="lp05"><?=date("Y-m-d", strtotime($row['reg_date']))?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="19" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="/sub/group/pmCategoryW" class="typeBtn btnMain big"><i class="fas fa-plus-circle"></i>카테고리등록</a>
		</div>
		<div class="right">
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<!-- 데이터 검색 -->
	<div class="simpleSearchWrap">
		<form method="get">
			<select class="txtBox" name="label">
				<option value="company_name">사업자명</option>
				<option value="company_num">사업자번호</option>
				<option value="company_tel">사업자연락처</option>
				<option value="ceo_name">대표자명</option>
				<option value="ceo_tel">대표자연락처</option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit" class="typeBtn">검색</button>
		</form>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>