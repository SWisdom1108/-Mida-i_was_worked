<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 메뉴설정
	$secMenu = "excel";
	$trdMenu = "excel";
	
	# 콘텐츠설정
	$contentsTitle = "엑셀다운로드 현황";
	$contentsInfo = "엑셀다운로드 현황를 보실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "엑셀다운로드 현황");
	array_push($contentsRoots, "다운현황");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 통계 일시 지정
	$startDate = ($_GET['s_date']) ? $_GET['s_date'] : date("Y-m-d", strtotime("- 7 days"));
	$endDate = ($_GET['e_date']) ? $_GET['e_date'] : date("Y-m-d");

	# 추가 쿼리문
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d')";
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";

	# 데이터 간단정리표
	$value = array(''=>'');
	$query = "
		SELECT
			  ( SELECT COUNT(*) FROM mt_excel_log {$andQuery} ) AS totalCnt
		FROM dual
	";
	$dashboard = view_pdo($query, $value);

	# 페이징 정리
	paging("mt_excel_log");

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
						<span class="label">전체 다운로드 수</span>
						<span class="value"><?=number_format($dashboard['totalCnt'])?></span>
					</li>
				</ul>
			</div>
		</div>
	</div>
	

	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="reg_date ASC">다운일시 오름차순</option>
				<option value="reg_date DESC">다운일시 내림차순</option>
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
				<col width="10%">
				<col width="16%">
				<col width="45%">
			</colgroup>
			<thead>
				<tr>
					<th>NO</th>
					<th>이름</th>
					<th>아이디</th>
					<th>아이피</th>
					<th>다운로드날짜</th>					
					<th>다운로드경로</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
					FROM mt_excel_log MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=$row['m_name']?></td>
					<td class="lp05"><?=$row['m_id']?></td>
					<td class="lp05"><?=$row['reg_ip']?></td>

					<td class="lp05">
						<?=date("Y-m-d", strtotime($row['reg_date']))?>
						<br><span style="font-size: 12px;"><?=date("H:i:s", strtotime($row['reg_date']))?></span>
					</td>
					<td class="lp05"><?=$row['down_url']?></td>
					
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