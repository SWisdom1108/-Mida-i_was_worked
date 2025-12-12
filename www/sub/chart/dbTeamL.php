<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	if($_GET['code']){
		$code = $_GET['code'];
		$value = array(':idx'=>$_GET['code']);
		$query = "SELECT * FROM mt_member_team WHERE use_yn = 'Y' AND idx = :idx";
		$codeInfo = view_pdo($query, $value);
		if(!$codeInfo){
			www("/sub/chart/dbTeamAllL");
			return false;
		}
	}

	# 메뉴설정
	$secMenu = "dbTeam";
	$trdMenu = ($code) ? "tm{$code}" : "all";
	
	# 콘텐츠설정
	$contentsTitle = ($code) ? "{$codeInfo['team_name']} DB분배통계" : "전체 DB분배통계";
	$contentsInfo = "{$customLabel["tm"]}의 분배된 DB통계를 확인하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "DB분배통계");
	array_push($contentsRoots, ($code) ? $codeInfo['team_name'] : "전체보기");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 통계 일시 지정
	$startDate = ($_GET['s_date']) ? $_GET['s_date'] : date("Y-m-d", strtotime("- 1 month"));
	$endDate = ($_GET['e_date']) ? $_GET['e_date'] : date("Y-m-d");

	# 추가 쿼리문
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d')";
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";
	$andQuery .= " AND tm_code = '{$code}'";
	$andQuery .= " AND use_yn = 'Y'";
	$andQuery .= " AND dist_code = '002'";
	# 데이터 간단정리표
	$value = array(''=>'');
	$query = "
		SELECT
			  ( SELECT COUNT(*) FROM mt_db {$andQuery} AND use_yn = 'Y' AND dist_code = '002' ) AS totalCnt
		FROM dual
	";
	$dashboard = view_pdo($query, $value);

	# 데이터정리
	$totalCnt = 0;
	$chartTableDatas = [];
	$chartTableDatasName = [];
	$chartDatas = [];
	$chartMoreDatas = [];
	$sum = [];
	$value = array(''=>'');
	$query = "
		SELECT MT.*
			, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS m_name
			, ( SELECT team_name FROM mt_member_team WHERE idx = MT.tm_code ) AS tm_name
		FROM mt_db MT
		{$andQuery}
		ORDER BY reg_date DESC
	";
	$sql = list_pdo($query, $value);
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		$thisDate = date("Y-m", strtotime($row['reg_date']));
		
		$chartTableDatas[$row['m_idx']]++;
		$chartTableDatasName[$row['m_idx']] = $row['m_name'];
		$chartDatas["{$row['m_idx']}@{$row['m_name']}@{$thisDate}"]++;
		if($chartDatas["{$row['m_idx']}@{$row['m_name']}@{$thisDate}"] == 1){
			$totalCnt++;
		}
	}
	arsort($chartTableDatas);

	# 컬럼 정리
	$columnCnt = 0;
	$columnArr = [];
	$value = array(''=>'');
	$query = "
		SELECT *
		FROM mc_db_cs_status
		WHERE use_yn = 'Y'
		AND sms_yn = 'N'
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);
	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$columnCnt++;
		
		$thisdatas = [];
		$thisdatas['name'] = $row['status_name'];
		$thisdatas['code'] = $row['status_code'];
		
		$columnArr[$columnCnt] = $thisdatas;
		
		foreach($chartDatas as $index => $val){
			$datas = explode("@", $index);

			$value = array(':m_idx'=>$datas[0], ':cs_status_code'=>$row['status_code']);
			$query = "SELECT COUNT(*) AS cnt FROM mt_db {$andQuery} AND m_idx = :m_idx AND cs_status_code = :cs_status_code";
			$cnt = view_pdo($query, $value)['cnt'];
			$value = array(':cs_status_code'=>$row['status_code']);
			$query = "SELECT COUNT(*) as tt FROM mt_db {$andQuery} AND cs_status_code = :cs_status_code";
			$tt = view_pdo($query, $value)['tt'];
			
			$chartMoreDatas["{$index}@{$row['status_code']}"] = $cnt;
			$sum["{$index}@{$row['status_code']}"] = $tt ;

		}
	}
	$columnWidth = 70 / ($columnCnt + 1);

?>
	
	<!-- 데이터 검색영역 -->
	<div class="searchWrap" style="margin-bottom: 50px;">
		<form method="get">
			<input type="hidden" name="code" value="<?=$code?>">
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
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="6%">
				<col width="10%">
				<col width="10%">
			<?php for($i = 0; $i < ($columnCnt + 1); $i++){ ?>
				<col width="<?=$columnWidth?>%">
			<?php } ?>
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">NO</th>
					<th rowspan="2">년월</th>
					<th rowspan="2"><?=$customLabel["tm"]?>정보</th>
					<th rowspan="2">담당자정보</th>
					<th colspan="<?=($columnCnt + 1)?>">DB정보</th>
				</tr>
				<tr>
					<th>전체DB</th>
				<?php foreach($columnArr as $val){ ?>
					<th><?=$val['name']?></th>
				<?php } ?>
				</tr>
			</thead>
			<tbody>
			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="<?=($columnCnt + 5)?>" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } else {?>
			
				<?php foreach($chartDatas as $index => $val){ ?>
					<?php $data = explode("@", $index); ?>
					<tr>
						<td class="lp05"><?=$totalCnt?></td>
						<td class="lp05"><?=$data[2]?></td>
						<td class="lp05">TM<?=$code?><br><?=$codeInfo['team_name']?></td>
						<td class="lp05">FC<?=$data[0]?><br><?=$data[1]?></td>
						<td class="lp05"><?=number_format($val)?></td>
					<?php foreach($columnArr as $columnVal){ ?>
						<td class="lp05"><?=number_format($chartMoreDatas["{$index}@{$columnVal['code']}"])?></td>
					<?php } ?>
					</tr>
				<?php $totalCnt--; } ?>

				<tr style="background-color: #f3f3f3;">
						<td colspan="4" class="lp05" style="font-size: 13px; font-weight: bold; color: #333;">합계</td>
						<td class="lp05" style="font-size: 13px; font-weight: bold; color: #333;"><?=number_format($dashboard['totalCnt'])?></td>
					<?php foreach($columnArr as $columnVal){ ?>
						<td class="lp05" style="font-size: 13px; font-weight: bold; color: #333;">
							<?=
								number_format($sum["{$index}@{$columnVal['code']}"])
							?>
						</td>
					<?php } ?>
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
								foreach($chartTableDatas as $idx => $val){
									echo ", '{$chartTableDatasName[$idx]}'";
								}
							?>
						],
						["전체DB"
							<?php
								foreach($chartTableDatas as $idx => $val){
									echo ", '{$val}'";
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