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
		$query = "SELECT * FROM mt_member_cmpy WHERE use_yn = 'Y' AND auth_code = '003' AND idx = :idx";
		$codeInfo = view_pdo($query, $value);
		if(!$codeInfo){
			www("/sub/chart/dbAllL");
			return false;
		}
	}

	# 메뉴설정
	$secMenu = "dbAll";
	$trdMenu = ($code) ? "db{$code}" : "all";
	
	# 콘텐츠설정
	$contentsTitle = ($code) ? "{$codeInfo['company_name']} DB통합통계" : "전체 DB통합통계";
	$contentsInfo = "생산업체에서 업로드한 DB통계를 확인하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "DB통합통계");
	array_push($contentsRoots, ($code) ? $codeInfo['company_name'] : "전체보기");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 통계 일시 지정
	$startDate = ($_GET['s_date']) ? $_GET['s_date'] : date("Y-m-d", strtotime("- 7 days"));
	$endDate = ($_GET['e_date']) ? $_GET['e_date'] : date("Y-m-d");
	$totalCnt = 0;

	# 데이터 정리
	$pmList = [];
	$pmNameList = [];
	if($code){
		array_push($pmList, $code);
		$pmNameList[$code] = $codeInfo['company_name'];
	} else {
		$value = array(''=>'');
		$query = "
			SELECT MT.*
			FROM mt_member_cmpy MT
			WHERE use_yn = 'Y'
			AND auth_code = '003'
			ORDER BY idx DESC
		";
		$sql = list_pdo($query, $value);
		while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			array_push($pmList, $row['idx']);
			$pmNameList[$row['idx']] = $row['company_name'];
		}
	}

	$totalData = [];
	$chartData = [];
	$newDate = date("Y-m-d", strtotime("+1 day", strtotime($endDate)));
	while(true){
		$newDate = date("Y-m-d", strtotime("-1 day", strtotime($newDate)));
		$thisDateData = [];
		
		foreach($pmList as $pmCode){
			$totalCnt++;
			$value = array(''=>'');
			$query = "
				SELECT
					  ( SELECT COUNT(*) FROM mt_db_pm_log WHERE type_name = 'upload' AND pm_code = '{$pmCode}' AND reg_date LIKE '{$newDate}%' ) AS uploadCnt
					, ( SELECT COUNT(*) FROM mt_db_pm_log WHERE type_name = 'dist' AND pm_code = '{$pmCode}' AND reg_date LIKE '{$newDate}%' ) AS distCnt
					, ( SELECT COUNT(*) FROM mt_db_pm_log WHERE type_name = 'delete' AND pm_code = '{$pmCode}' AND reg_date LIKE '{$newDate}%' ) AS deleteCnt
					, ( SELECT db_cnt FROM mt_db_pm_log WHERE type_name = 'stock' AND pm_code = '{$pmCode}' AND reg_date LIKE '{$newDate}%' ) AS stockCnt
				FROM dual
			";
			$thisPmData = view_pdo($query, $value);
			
			$totalData[$newDate]["uploadCnt"] += $thisPmData["uploadCnt"];
			$thisDateData[$pmCode] = $thisPmData;
		}
		
		$chartData[$newDate] = $thisDateData;
		if($newDate == $startDate) break;
	}

	# 추가 쿼리문
	$andQuery = " WHERE 1=1";
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d')";
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";
	if($code){
		$andQuery .= " AND pm_code = '{$code}'";
	}

	# 데이터 간단정리표
	$value = array(''=>'');
	$query = "
		SELECT
			  ( SELECT COUNT(*) FROM mt_db {$andQuery} ) AS totalCnt
			, ( SELECT COUNT(*) FROM mt_db {$andQuery} AND use_yn = 'N' ) AS deleteCnt
			, ( SELECT COUNT(*) FROM mt_db {$andQuery} AND use_yn = 'Y' AND dist_code = '002' ) AS distCnt
		FROM dual
	";
	$dashboard = view_pdo($query, $value);

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
					<li>
						<span class="label">삭제DB</span>
						<span class="value"><?=number_format($dashboard['deleteCnt'])?></span>
					</li>
					<li>
						<span class="label">분배DB</span>
						<span class="value"><?=number_format($dashboard['distCnt'])?></span>
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
				<col width="13%">
				<col width="13%">
				<col width="20%">
				<col width="20%">
				<col width="20%">
				<col width="20%">
			</colgroup>
			<thead>
				<tr>
					<th>NO</th>
					<th>생산업체</th>
					<th>업로드일</th>
					<th>업로드DB</th>
					<th>분배DB</th>
					<th>잔여DB</th>
					<th>삭제DB</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($chartData as $date => $val){ ?>
					<?php foreach($chartData[$date] as $code => $data){ ?>
						<tr>
							<td class="lp05"><?=$totalCnt?></td>
							<td class="lp05">PM<?=$code?><br><?=$pmNameList[$code]?></td>
							<td class="lp05"><?=$date?></td>
							<td class="lp05"><?=number_format($data["uploadCnt"])?></td>
							<td class="lp05"><?=number_format($data["distCnt"])?></td>
							<td class="lp05"><?=number_format($data["stockCnt"])?></td>
							<td class="lp05"><?=number_format($data["deleteCnt"])?></td>
						</tr>
					<?php $totalCnt--; } ?>
				<?php } ?>
			</tbody>
		</table>
	</div>
	
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
						["업로드DB"
							<?php
								$newDate = date("Y-m-d", strtotime("-1 day", strtotime($startDate)));
								while(true) {
									$newDate = date("Y-m-d", strtotime("+1 day", strtotime($newDate)));
									echo ", '{$totalData[$newDate]["uploadCnt"]}'";
									if($newDate == $endDate) break;
								}
							 ?>
						]
					],
					types: {
						"업로드DB" : "bar"
					},
					colors: {
						"업로드DB" : "<?=$site['main_color']?>"
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