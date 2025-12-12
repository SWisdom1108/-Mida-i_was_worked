<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["004", "005"];

	# 메뉴설정
	$secMenu = "dbMy";
	
	# 콘텐츠설정
	$contentsTitle = "나의 DB통계";
	$contentsInfo = "나에게 분배된 DB통계를 확인하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "나의DB통계");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 통계 일시 지정
	$startDate = ($_GET['s_date']) ? $_GET['s_date'] : date("Y-m-d", strtotime("- 7 days"));
	$endDate = ($_GET['e_date']) ? $_GET['e_date'] : date("Y-m-d");

	# 추가 쿼리문
	$distLogData = [];
	$value = array(':tm_code'=>$user['tm_code']);
	$query = "SELECT db_idx FROM mt_db_dist_log WHERE tm_code = :tm_code AND date_format(reg_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d') AND date_format(reg_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";
	$distLogDataSQL = list_pdo($query, $value);


	while($row = $distLogDataSQL->fetch(PDO::FETCH_ASSOC)){
		array_push($distLogData, $row["db_idx"]);
	}
	$distLogData = implode(",", $distLogData);

	if(!$distLogData){
		$andQuery .= " AND tm_code = '{$user['tm_code']}' AND dist_code = '002' AND m_idx = '{$user['idx']}'";
	}else{
		$andQuery .= " AND tm_code = '{$user['tm_code']}' AND dist_code = '002' AND m_idx = '{$user['idx']}' AND idx IN ( {$distLogData} )";
	}


	# 초기 정렬
	$_GET['orderBy'] = ($_GET['orderBy']) ? $_GET['orderBy'] : "order_by_date DESC";

	# 데이터 간단정리표
	$value = array(''=>'');
	$query = "
		SELECT
			  ( SELECT COUNT(*) FROM mt_db {$andQuery} ) AS totalCnt
			, ( SELECT COUNT(*) FROM mt_db {$andQuery} AND use_yn = 'N' ) AS deleteCnt
		FROM dual
	";
	$dashboard = view_pdo($query, $value);

	# 페이징 정리
	paging("mt_db");

	# 컬럼 정리
	$columnCnt = 0;
	$columnArr = [];
	$value = array(''=>'');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = 'Y'
		AND list_yn = 'Y'
		ORDER BY idx ASC
	";
	$columnData = list_pdo($query, $value);
	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$columnCnt++;
		
		$thisdatas = [];
		$thisdatas['name'] = $row['column_name'];
		$thisdatas['code'] = $row['column_code'];
		
		$columnArr[$columnCnt] = $thisdatas;
	}
	$columnWidth = 63 / ($columnCnt + 2);

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
						<span class="label">전체DB</span>
						<span class="value"><?=number_format($dashboard['totalCnt'])?></span>
					</li>
					<li>
						<span class="label">삭제DB</span>
						<span class="value"><?=number_format($dashboard['deleteCnt'])?></span>
					</li>
				</ul>
			</div>
		</div>
	</div>
	
	<!-- 통계 그래프 -->
	<div id="chartWrap">
		<div id="chartBox"></div>
	</div>
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="order_by_date ASC">분배일시 오름차순</option>
				<option value="order_by_date DESC">분배일시 내림차순</option>
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
				<col width="8%">
			<?php for($i = 0; $i < ($columnCnt + 2); $i++){ ?>
				<col width="<?=$columnWidth?>%">
			<?php } ?>
				<col width="10%">
				<col width="8%">
				<col width="7%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">NO</th>
					<th rowspan="2">DB고유번호</th>
					<th colspan="<?=($columnCnt + 2)?>">DB정보</th>
					<th colspan="2">분배정보</th>
					<th rowspan="2">삭제여부</th>
				</tr>
				<tr>
					<th>이름</th>
					<th>연락처</th>
				<?php foreach($columnArr as $val){ ?>
					<th><?=$val['name']?></th>
				<?php } ?>
					<th>분배일시</th>
					<th style="border-right: 1px solid #FFF;">담당자</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS m_name
					FROM mt_db MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
					$row['m_name'] = dhtml($row['m_name']);
			?>
				<tr>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05">D-<?=$row['idx']?></td>
					<td><?=($row['cs_name']) ? $row['cs_name'] : "-"?></td>
					<td class="lp05"><?=($row['cs_tel']) ? $row['cs_tel'] : "-"?></td>
				<?php foreach($columnArr as $val){ ?>
					<td style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?=($row["{$val['code']}"]) ? $row["{$val['code']}"] : "-"?></td>
				<?php } ?>
					<td class="lp05"><?=date("Y-m-d H:i", strtotime($row['order_by_date']))?></td>
					<td class="lp05"><?=($row['m_name']) ? "FC{$row['m_idx']}<br>{$row['m_name']}" : "-"?></td>
					<td><?=($row['use_yn'] == "Y") ? "-" : "<span style='color: #CCC;'>삭제</span>"?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="<?=($columnCnt + 7)?>" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<script type="text/javascript">
		$(function(){

			// https://naver.github.io/billboard.js
			var totalChart = bb.generate({
				data: {
					x: "x",
					columns: [
						["x"
							<?php
								$newDate = date("Y-m-d", strtotime("-1 day", strtotime($startDate)));
								while(true) {
									 $newDate = date("Y-m-d", strtotime("+1 day", strtotime($newDate)));
									echo ',"'.$newDate.'"';
									 if($newDate == $endDate) break;
								}
							 ?>
						],
						["전체DB"
							<?php
								$newDate = date("Y-m-d", strtotime("-1 day", strtotime($startDate)));
								while(true) {
									$newDate = date("Y-m-d", strtotime("+1 day", strtotime($newDate)));
									$num = view_sql("SELECT count(*) AS num FROM mt_db {$andQuery} AND order_by_date LIKE '{$newDate}%'")['num'];
									echo ',"'.$num.'"';
									if($newDate == $endDate) break;
								}
							 ?>
						]
					],
					types: {
						"전체DB" : "bar"
					},
					colors: {
						"전체DB" : "<?=$site['main_color']?>"
					}
				},
				legend: {
					"show": false
				},
				grid: {
					y: {
						show: true
					}
				},
				bar: {
					width: {
						max: 50
					}
				},
				axis: {
					x: {
						type: "category",
						tick: {
							multiline: false,
							tooltip: false
						}
					}
				},
				bindto: "#chartBox"
			});
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>