<?php

	# 메뉴설정
	$secMenu = "schedule";
	$trdMenu = "type";
	
	# 콘텐츠설정
	$contentsTitle = "일정 구분설정";
	$contentsInfo = "일정관리 시 사용할 구분을 설정하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "일정설정");
	array_push($contentsRoots, "구분설정");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mc_schedule_type");

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
			<button type="button" class="typeBtn btnMain big" onclick='popupControl("open", "write", "/sub/my/scheduleTypeW", "일정구분값 등록하기")'><i class="fas fa-plus-circle"></i>일정구분값 등록</button>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="36%">
				<col width="15%">
				<col width="15%">
				<col width="15%">
				<col width="15%">
			</colgroup>
			<thead>
				<tr>
					<th>NO</th>
					<th>상태값명</th>
					<th>우선순위</th>
					<th>사용여부</th>
					<th>최초등록일시</th>
					<th>최종수정일시</th>
				</tr>
			</thead>
			<tbody>
			<?php
				// $sql = list_sql("
				// 	SELECT MT.*
				// 	FROM mc_schedule_type MT
				// 	{$andQuery}
				// 	{$orderQuery}
				// 	{$limitQuery}
				// ");

				$value = array(''=>'');
				$query = "
					SELECT MT.*
					FROM mc_schedule_type MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";

				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
					# 사용여부 색상지정
					$useColor = ($row['use_yn'] == "Y") ? "666" : "CCC";
			?>
				<tr class="rowMove" onclick='popupControl("open", "mod", "/sub/my/scheduleTypeU?code=<?=$row['type_code']?>", "일정구분값 수정하기")'>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=dhtml($row['type_name'])?></td>
					<td class="lp05"><?=number_format($row["sort"])?></td>
					<td style="color: #<?=$useColor?>;"><?=($row['use_yn'] == "Y") ? "사용중" : "미사용"?></td>
					<td class="lp05"><?=$row['reg_date']?></td>
					<td class="lp05"><?=($row['edit_date']) ? $row['edit_date'] : "-"?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="6">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnMain big" onclick='popupControl("open", "write", "/sub/my/scheduleTypeW", "일정구분값 등록하기")'><i class="fas fa-plus-circle"></i>일정구분값 등록</button>
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
				<option value="type_name">구분값명</option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit" class="typeBtn">검색</button>
		</form>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>