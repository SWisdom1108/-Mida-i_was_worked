<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 메뉴설정
	$secMenu = "group";
	
	# 콘텐츠설정
	$contentsTitle = "조직현황통계";
	$contentsInfo = "조직들에 관한 통계를 간단하게 확인하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "조직현황통계");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 데이터 간단정리표
	$value = array(''=>'');
	$query = "
		SELECT
			  ( SELECT COUNT(*) FROM mt_member_cmpy WHERE auth_code = '003' ) AS 003Cnt
			, ( SELECT COUNT(*) FROM mt_member_team WHERE auth_code = '004' ) AS 004Cnt
			, ( SELECT COUNT(*) FROM mt_member WHERE auth_code IN ( 004, 005 ) ) AS 005Cnt
		FROM dual
	";
	$dashboard = view_pdo($query, $value);
	

	$value = array(''=>'');
	$query = "SELECT COUNT(*) AS cnt FROM mt_member_cmpy WHERE auth_code IN ( 003 )";
	$totalCnt = view_pdo($query, $value)['cnt'];
	
	
	$value = array(''=>'');
	$query = "SELECT COUNT(*) AS cnt FROM mt_member_team WHERE auth_code IN ( 004 )";
	$totalCnt += view_pdo($query, $value)['cnt'];

?>


	
	<!-- 데이터 간단정리표 -->
	<div class="dataInfoSimpleWrap">
		<div>
			<div class="iconWrap">
				<i class="fas fa-chart-pie"></i>
			</div>
			<div class="conWrap">
				<ul class="dataCntList">
					<li>
						<span class="label">전체 생산업체 수</span>
						<span class="value"><?=number_format($dashboard['003Cnt'])?></span>
					</li>
					<li>
						<span class="label">전체 <?=$customLabel["tm"]?> 수</span>
						<span class="value"><?=number_format($dashboard['004Cnt'])?></span>
					</li>
					<li>
						<span class="label">전체 <?=$customLabel["fc"]?> 수</span>
						<span class="value"><?=number_format($dashboard['005Cnt'])?></span>
					</li>
				</ul>
			</div>
		</div>
	</div>
	
	<!-- 통계 그래프 -->
	<div id="chartWrap">
		<div id="chartBox"></div>
	</div>
	
	<div class="listWrap">
		<div class="tit">생산업체별 조직현황</div>
		<table>
			<colgroup>
				<col width="10%">
				<col width="15%">
				<col width="63%">
				<col width="12%">
			</colgroup>
			<thead>
				<tr>
					<th>권한</th>
					<th>현황</th>
					<th>업체명</th>
					<th>등록일시</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					$value = array(''=>'');
					$query = "
						SELECT MT.*
						FROM mt_member_cmpy MT
						WHERE auth_code = '003'
						ORDER BY idx DESC
					";
					$sql = list_pdo($query, $value);
					while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						$i++;
				?>
					<tr>
					<?php if($i == 1){ ?>
						<td rowspan="<?=$dashboard['003Cnt']?>">생산업체</td>
						<td rowspan="<?=$dashboard['003Cnt']?>"><?=number_format($dashboard['003Cnt'])?></td>
					<?php } ?>
						<td><?=dhtml($row['company_name'])?></td>
						<td class="lp05"><?=date("Y-m-d", strtotime($row['reg_date']))?></td>
					</tr>
				<?php } ?>

				<?php if(!$i){ ?>
					<tr>
						<td colspan="4" class="no">등록된 생산업체가 존재하지 않습니다.</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		
		<div class="tit"><?=$customLabel["tm"]?>별 조직현황</div>
		<table>
			<colgroup>
				<col width="10%">
				<col width="15%">
				<col width="48%">
				<col width="15%">
				<col width="12%">
			</colgroup>
			<thead>
				<tr>
					<th>권한</th>
					<th>현황</th>
					<th><?=$customLabel["tm"]?>명</th>
					<th><?=$customLabel["fc"]?> 수</th>
					<th>등록일시</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$i = 0;
					$value = array(''=>'');
					$query = "
						SELECT MT.*
							, ( SELECT COUNT(*) FROM mt_member WHERE tm_code = MT.idx ) AS totalCnt
						FROM mt_member_team MT
						WHERE auth_code = '004'
						ORDER BY idx DESC
					";
					$sql = list_pdo($query, $value);
					while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						$i++;
				?>
					<tr>
					<?php if($i == 1){ ?>
						<td rowspan="<?=$dashboard['004Cnt']?>"><?=dhtml($customLabel["tm"]);?></td>
						<td rowspan="<?=$dashboard['004Cnt']?>"><?=number_format($dashboard['004Cnt'])?></td>
					<?php } ?>
						<td><?=dhtml($row['team_name'])?></td>
						<td><?=number_format($row['totalCnt'])?>명</td>
						<td class="lp05"><?=date("Y-m-d", strtotime($row['reg_date']))?></td>
					</tr>
				<?php } ?>
			
				<?php if(!$i){ ?>
					<tr>
						<td colspan="5" class="no">등록된 <?=dhtml($customLabel["tm"])?>(가)이 존재하지 않습니다.</td>
					</tr>
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
						["x", "생산업체", "<?=dhtml($customLabel["tm"])?>", "<?=$customLabel["fc"]?>"],
						["등록된 수", <?=$dashboard['003Cnt']?>, <?=$dashboard['004Cnt']?>, <?=$dashboard['005Cnt']?>]
					],
					types: {
						"등록된 수" : "bar"
					},
					colors: {
						"등록된 수" : "<?=$site['main_color']?>"
					}
				},
				legend: {
					"show": false
				},
				// chart.resize{
				// width:100
				// height:
				// };
				grid: {
					y: {
						show: true
					}
				},
				bar: {
					width: {
						max: 100
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