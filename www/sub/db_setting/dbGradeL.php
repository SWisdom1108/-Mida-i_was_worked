<?php

	# 메뉴설정
	$secMenu = "grade";
	
	# 콘텐츠설정
	$contentsTitle = "DB고객등급 설정";
	$contentsInfo = "DB고객등급 구분값을 설정 하실 수 있습니다.<br>설정된 항목은 분배관리에서 고객등급을 지정할 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "DB고객등급");
	array_push($contentsRoots, "설정");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	$andQuery .= " AND del_yn = 'N'";

	$value = array(''=>'');
	$query = "
		SELECT MT.*
		FROM mc_db_grade_info MT
		{$andQuery}
		{$orderQuery}
		{$limitQuery}
	";
	$sql = list_pdo($query, $value);

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mc_db_grade_info");
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
			<button type="button" class="typeBtn btnMain big" onclick='popupControl("open", "write", "/sub/db_setting/dbGradeW", "고객등급구분값 등록하기")'><i class="fas fa-plus-circle"></i>등급구분값 등록</button>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="26%">
				<col width="30%">
				<col width="10%">
				<col width="15%">
				<col width="15%">
			</colgroup>
			<thead>
				<tr>
					<th>NO</th>
					<th>구분값명 (고객등급코드)</th>
					<th>상세설명</th>
					<th>사용여부</th>
					<th>최초등록일시</th>
					<th>최종수정일시</th>
				</tr>
			</thead>
			<tbody>
			<?php
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){

				?>
				<tr class="rowMove" onclick='popupControl("open", "mod", "/sub/db_setting/dbGradeU?code=<?=$row['grade_code']?>", "고객등급구분값 수정하기")'>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=dhtml($row['grade_name'])?> (<?=dhtml($row['grade_code'])?>)</td>
					<td class="lp05"><?=dhtml($row['ex_memo'])?></td>
					<td class="lp05"style="<?=($row['del_yn'] == 'Y') ? "color:#ccc;" :"color:#666;"?>"><?=($row['use_yn'] == 'Y') ? "사용중" : "미사용"?></td>
					<td class="lp05"><?=$row['reg_date']?></td>
					<td class="lp05"><?=($row['edit_date']) ? $row['edit_date'] : "-"?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="7">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnMain big" onclick='popupControl("open", "write", "/sub/db_setting/dbGradeW", "고객등급구분값 등록하기")'><i class="fas fa-plus-circle"></i>등급구분값 등록</button>
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
				<option value="grade_name">구분값명</option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit" class="typeBtn">검색</button>
		</form>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>