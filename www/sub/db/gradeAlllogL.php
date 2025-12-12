<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 메뉴설정
	$secMenu = "gradelog";
	
	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "고객등급 변경내역");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	$date = date("Y-m-d");

	$startDate = ($_GET['s_date']) ? $_GET['s_date'] : date("Y-m-d", strtotime("- 7 days"));
	$endDate = ($_GET['e_date']) ? $_GET['e_date'] : date("Y-m-d");

	# 추가 쿼리문
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d')";
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";

	# 고객등급 정렬 2022.08.31(수)
	$value = array(''=>'');
	$query = "SELECT * FROM mc_db_grade_info WHERE use_yn = 'Y' AND del_yn = 'N' ORDER BY grade_code ASC";
	$grade = list_pdo($query, $value);


	if($_GET["gradeCode"]){
		$andQuery .= " AND grade_code = '{$_GET["gradeCode"]}'";
		$andQuerys = " AND grade_code = '{$_GET["gradeCode"]}'";
	}

	# 데이터 간단정리표
	$value = array(''=>'');
	$query ="
		SELECT
			  ( SELECT COUNT(*) FROM mt_db_grade_log {$andQuery} ) AS totalCnt,
			  ( SELECT COUNT(*) FROM mt_db_grade_log {$andQuery} AND date_format(reg_date, '%Y-%m-%d') = date_format('{$date}', '%Y-%m-%d') ) AS todayCnt
		FROM dual";
	$dashboard = view_pdo($query, $value);

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_db_grade_log");


?>
<!-- 데이터 검색영역 -->
	<div class="searchWrap" style="margin-bottom: 50px;">
		<form method="get">
			<ul class="formWrap">
				<li class="drag">
					<span class="label">조회기간</span>
					<input type="hidden" name="setDate" value="reg">
					<input type="text" class="txtBox s_date" name="s_date" value="<?=$startDate?>" dateonly>
					<span class="hypen">~</span>
					<input type="text" class="txtBox e_date" name="e_date" value="<?=$endDate?>" dateonly>
					<span class="dateBtn" data-s="<?=date("Y-m-d")?>" data-e="<?=date("Y-m-d")?>">오늘</span>
					<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 7 days"))?>" data-e="<?=date("Y-m-d")?>">7일</span>
					<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 1 month"))?>" data-e="<?=date("Y-m-d")?>">1개월</span>
					<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 3 month"))?>" data-e="<?=date("Y-m-d")?>">3개월</span>
				</li>
			</ul>
			<div class="btnWrap">
				<button type="submit" class="typeBtn">조회</button>
			</div>
		</form>
	</div>
	<!-- 데이터 간단정리표 -->
	<div class="dataInfoSimpleWrap" style="margin-bottom: 20px;">
		<div>
			<div class="iconWrap">
				<i class="fas fa-chart-pie"></i>
			</div>
			<div class="conWrap">
				<ul class="dataCntList">
					<li>
						<span class="label">전체 변경내역</span>
						<span class="value" style="margin-right:20px;"><?=number_format($dashboard['totalCnt'])?></span>
						<span class="label">오늘 변경내역</span>
						<span class="value"><?=number_format($dashboard['todayCnt'])?></span>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
			<select class="listSet" id="gradeCode"style="margin-left:10px;">
				<option value="">고객등급별 보기</option>
			<?php while($row = $grade->fetch(PDO::FETCH_ASSOC)){ ?>
				<option value="<?=$row["grade_code"]?>" <?=($_GET["gradeCode"] == $row["grade_code"]) ? "selected" : ""?>><?=$row["grade_name"]?></option>
			<?php } ?>
		</select>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="reg_date ASC">변경일시 오름차순</option>
				<option value="reg_date DESC">변경일시 내림차순</option>
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
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="10%">
				<col width="15%">
				<col width="16%">
				<col width="45%">
				<col width="10%">
			</colgroup>
			<thead>
				<tr>
					<th>NO</th>
					<th>회원명</th>
					<th>회원고유번호</th>
					<th>고객등급</th>
					<th>상세설명</th>
					<th>변경일시</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						, ( SELECT cs_name FROM mt_db WHERE idx = MT.db_idx ) AS db_name
						, ( SELECT grade_name FROM mc_db_grade_info WHERE MT.grade_code = grade_code ) AS grade_name
					FROM mt_db_grade_log MT
					{$andQuery}
					ORDER BY db_idx ASC
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=$row['db_name']?></td>
					<td class="lp05"><?=$row['db_idx']?></td>
					<td class="lp05"><?=$row['grade_name']?></td>
					<td class="lp05"><?=$row['ex_memo']?></td>
					<td class="lp05">
						<?=date("Y-m-d", strtotime($row['reg_date']))?>
						<br><span style="font-size: 12px;"><?=date("H:i:s", strtotime($row['reg_date']))?></span>
					</td>
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
	<!-- 페이징 -->
	<?=paging()?>
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>