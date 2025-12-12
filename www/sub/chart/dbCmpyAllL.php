<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	# 메뉴설정
	$secMenu = "dbcmpy";
	$trdMenu = "all";
	
	# 콘텐츠설정
	$contentsTitle = "전체 DB분배통계";
	$contentsInfo = "{$customLabel["tm"]}별로 분배된 DB통계를 확인하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "DB분배통계");
	array_push($contentsRoots, "{$customLabel["tm"]}별전체현황");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 통계 일시 지정
	$startDate = ($_GET['s_date']) ? $_GET['s_date'] : date("Y-m-d", strtotime("- 1 month"));
	$endDate = ($_GET['e_date']) ? $_GET['e_date'] : date("Y-m-d");

	# 추가 쿼리문
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d')";
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";

	# 데이터 간단정리표
	$value = array(''=>'');
	$query = "
		SELECT
			  ( SELECT COUNT(*) FROM mt_db {$andQuery} AND use_yn = 'Y' AND pm_code != 0000 AND pm_code != 0001  ) AS totalCnt
		FROM dual
	";
	$dashboard = view_pdo($query, $value);

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
			
			$value = array(':status_code'=>$row['status_code']);
			$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE reg_date LIKE '{$datas[2]}%' AND idx IN ('{$datas[3]}' ) AND status_code = :status_code";
			$cnt = view_pdo($query, $value)['cnt'];
			$chartMoreDatas["{$index}@{$row['status_code']}"] = $cnt;
		}
	}



	$columnWidth = 60 / ($columnCnt);


	$columnCntArr = [];
	$value = array(''=>'');
	$query = "
		select ifnull(pm_code, 'root') as pm_code, cs_status_code, count(cs_status_code) as t_cnt from mt_db
		{$andQuery}
		AND use_yn = 'Y'
		group by pm_code, cs_status_code
		order by pm_code
	";

	$sql = list_pdo($query, $value);
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		$columnCntArr[$row['pm_code']][$row['cs_status_code']] = $row['t_cnt'];
	}





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
			<span class="cnt">TOTAL <?=number_format($dashboard['totalCnt'])?></span>
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
				<col width="10%">
			<?php for($i = 0; $i < ($columnCnt); $i++){ ?>
				<col width="<?=$columnWidth?>%">
			<?php } ?>
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">NO</th>
					<th rowspan="2">생산업체정보</th>
					<th rowspan="2">전체DB</th>
					<th rowspan="2">분배전DB</th>
					<th rowspan="2">분배후DB</th>
					<th colspan="<?=($columnCnt)?>">상담상태별</th>
				</tr>
				<tr>
					<?php foreach($columnArr as $val){ ?>
						<th><?=$val['name']?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
			<?php if(!$dashboard['totalCnt']){ ?>
				<tr>
					<td colspan="<?=($columnCnt + 5)?>" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php }else{ ?>
			
				<?php
					$i = 1;
					$value = array(''=>'');
					$query = "
						SELECT *
						FROM mt_member_cmpy
						WHERE 1=1
						AND pm_code != 0001
						AND use_yn = 'Y'
						ORDER BY idx ASC
					";
					$sql = list_pdo($query, $value);
					while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						$value = array(':pm_code'=>$row['pm_code']);
						$query = "SELECT COUNT(*) AS cnt FROM mt_db {$andQuery} AND use_yn = 'Y' AND pm_code = '{$row['pm_code']}'";						
						$cnt = view_pdo($query, $value)['cnt'];
						$value = array(':pm_code'=>$row['pm_code']);
						$query = "SELECT COUNT(*) AS cnt FROM mt_db {$andQuery} AND use_yn = 'Y' AND dist_code = 002 AND pm_code = :pm_code";
						$distcnt = view_pdo($query, $value)['cnt'];
						$value = array(':pm_code'=>$row['pm_code']);
						$query = "SELECT COUNT(*) AS cnt FROM mt_db {$andQuery} AND use_yn = 'Y' AND dist_code = 001 AND pm_code = :pm_code";
						$nodistcnt = view_pdo($query, $value)['cnt'];
				?>
				<tr>
					<td class="lp05"><?=$i++?></td>
					<td class="lp05">PM<?=$row[pm_code]?><br><?=$row[company_name]?></td>
					<td class="lp05"><?=number_format($cnt)?></td>
					<td class="lp05"><?=number_format($nodistcnt)?></td>
					<td class="lp05"><?=number_format($distcnt)?></td>

					<?php foreach($columnArr as $key => $val){ 
						// if($key >= 3) {
						// 	$key = $key + 1;
						// 	$key = sprintf('%03d', $key);
						// } else {
						// 	$key = sprintf('%03d', $key);
						// }

					?>
						<td><?=
							($columnCntArr[$row['pm_code']][$val['code']]) ? $columnCntArr[$row['pm_code']][$val['code']] : "-"
							?>
						</td>
					<?php } ?>


				</tr>
				<?php }?>
			<?php }?>
				
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
						["전체DB"
							<?php
								$newDate = date("Y-m-d", strtotime("-1 day", strtotime($startDate)));
								while(true) {
									$newDate = date("Y-m-d", strtotime("+1 day", strtotime($newDate)));
									$num = view_sql("SELECT count(*) AS num FROM mt_db {$andQuery} AND reg_date LIKE '{$newDate}%' AND use_yn = 'Y'")['num'];
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