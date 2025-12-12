<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	# 메뉴설정
	$secMenu = "dbAll";
	$trdMenu = "all005";

	# 콘텐츠설정
	$contentsTitle = "{$customLabel["fc"]} DB통합통계";
	$contentsInfo = "{$customLabel["fc"]}별 업로드된 DB통계를 확인하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "DB통합통계");
	array_push($contentsRoots, $customLabel["fc"]);

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	if($_GET["tmCode"]){
		$andQuery2 = " AND tm_code = '{$_GET["tmCode"]}'";
	}

	# 통계 일시 지정
	$startDate = ($_GET['s_date']) ? $_GET['s_date'] : date("Y-m-d", strtotime("- 7 days"));
	$endDate = ($_GET['e_date']) ? $_GET['e_date'] : date("Y-m-d");
	$totalCnt = 0;

	# 데이터 정리
	$pmList = [];
	$pmNameList = [];
	$tmNameList = [];
	$value = array(''=>'');
	$query = "
		SELECT MT.*
			, ( SELECT team_name FROM mt_member_team WHERE idx = MT.tm_code ) AS tm_name
		FROM mt_member MT
		WHERE use_yn = 'Y'
		{$andQuery2}
		AND auth_code IN ( '004', '005' )
		ORDER BY idx DESC
	";
	$sql = list_pdo($query, $value);
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		array_push($pmList, $row['idx']);
		$pmNameList[$row['idx']] = $row["m_name"];
		$tmNameList[$row['idx']] = "{$row["tm_code"]}@#@#{$row["tm_name"]}";
		$totalCnt++;
	}

	$value = array(''=>'');
	$query = "
		SELECT MT.*
		FROM mt_member_team MT
		WHERE use_yn = 'Y'
		AND auth_code IN ( '004' )
		ORDER BY idx DESC
	";
	$tmListSQL = list_pdo($query, $value);

	$totalData = [];
	$chartData = [];
	$newDate = date("Y-m-d", strtotime("+1 day", strtotime($endDate)));
	while(true){
		$newDate = date("Y-m-d", strtotime("-1 day", strtotime($newDate)));
		$thisDateData = [];
		
		foreach($pmList as $pmCode){
			$query = "
				SELECT
					  ( SELECT COUNT(*) FROM mt_db WHERE m_idx = '{$pmCode}' AND use_yn = 'Y' AND dist_code = 002 AND dist_date LIKE '{$newDate}%' ) AS distCnt
					, ( SELECT COUNT(*) FROM mt_db WHERE m_idx = '{$pmCode}' AND use_yn = 'N' AND edit_date LIKE '{$newDate}%' ) AS deleteCnt
				FROM dual
			";
			$thisPmData = view_pdo($query, $value);
			
			$totalData[$newDate]["distCnt"] += $thisPmData["distCnt"];
			$totalData[$newDate]["deleteCnt"] += $thisPmData["deleteCnt"];
			$chartData[$pmCode]["distCnt"] += $thisPmData["distCnt"];
			$chartData[$pmCode]["deleteCnt"] += $thisPmData["deleteCnt"];
		}
		
		if($newDate == $startDate) break;
	}

	# 추가 쿼리문
	$pmList = implode(",", $pmList);
	$andQuery = " WHERE 1=1";
	$andQuery .= " AND m_idx IN ( {$pmList} )";
	if($_GET["tmCode"]){
		$andQuery .= " AND tm_code = '{$_GET["tmCode"]}'";
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

	<!-- 데이터 간단정리표 -->
	<div class="dataInfoSimpleWrap">
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
	
	<!-- 데이터 검색영역 -->
	<div class="searchWrap" style="margin-bottom: 50px;">
		<form method="get">
			<input type="hidden" name="tmCode" value="<?=$_GET["tmCode"]?>">
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
	
	<!-- 통계 그래프 -->
	<div id="chartWrap">
		<div id="chartBox"></div>
	</div>
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
			<span class="cnt" style="margin: 0 15px; color: #CCC; font-weight: 400; top: -1.5px;">|</span>
			<select class="listSet" id="tmCode">
				<option value=""><?=$customLabel["tm"]?>별 보기</option>
			<?php while($row = $tmListSQL->fetch(PDO::FETCH_ASSOC)){ ?>
				<option value="<?=$row["idx"]?>" <?=($_GET["tmCode"] == $row["idx"]) ? "selected" : ""?>><?=dhtml($row["team_name"])?></option>
			<?php } ?>
			</select>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="8%">
				<col width="8%">
				<col width="30%">
				<col width="30%">
				<col width="10%">
				<col width="10%">
			</colgroup>
			<thead>
				<tr>
					<th>NO</th>
					<th><?=dhtml($customLabel["tm"])?></th>
					<th><?=$customLabel["fc"]?></th>
					<th>분배DB</th>
					<th>삭제DB</th>
					<th>일별통계</th>
					<th>상세보기</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($chartData as $pmCode => $data){ $tmInfo = explode("@#@#", $tmNameList[$pmCode]); ?>
					<tr>
						<td class="lp05"><?=$totalCnt?></td>
						<td class="lp05">TM<?=$tmInfo[0]?><br><?=dhtml($tmInfo[1])?></td>
						<td class="lp05">FC<?=$pmCode?><br><?=dhtml($pmNameList[$pmCode])?></td>
						<td class="lp05"><?=number_format($data["distCnt"])?></td>
						<td class="lp05"><?=number_format($data["deleteCnt"])?></td>
						<td class="stopProgram">
							<button type="button" class="typeBtn mini btnGray02" onclick='popupControl("open", "dayChart", "/sub/chart/dbAll005DayL?s_date=<?=$startDate?>&e_date=<?=$endDate?>&code=<?=$pmCode?>", "FC<?=$pmCode?> DB통합 일별통계");' style="max-width: 100%; float: none;">
								<i class="fas fa-chart-pie"></i>일별통계보기
							</button>
						</td>
						<td class="stopProgram">
							<button type="button" class="typeBtn mini btnGray02" onclick='popupControl("open", "dbList", "/sub/chart/dbAll005DBL?s_date=<?=$startDate?>&e_date=<?=$endDate?>&code=<?=$pmCode?>", "FC<?=$pmCode?> DB통합 상세목록");' style="max-width: 100%; float: none;">
								<i class="fas fa-list-ul"></i>상세DB보기
							</button>
						</td>
					</tr>
				<?php $totalCnt--; } ?>
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
						["분배DB"
							<?php
								$newDate = date("Y-m-d", strtotime("-1 day", strtotime($startDate)));
								while(true) {
									$newDate = date("Y-m-d", strtotime("+1 day", strtotime($newDate)));
									echo ", '{$totalData[$newDate]["distCnt"]}'";
									if($newDate == $endDate) break;
								}
							 ?>
						],
						["삭제DB"
							<?php
								$newDate = date("Y-m-d", strtotime("-1 day", strtotime($startDate)));
								while(true) {
									$newDate = date("Y-m-d", strtotime("+1 day", strtotime($newDate)));
									echo ", '{$totalData[$newDate]["deleteCnt"]}'";
									if($newDate == $endDate) break;
								}
							 ?>
						]
					],
					types: {
						"분배DB" : "bar",
						"삭제DB" : ""
					},
					colors: {
						"분배DB" : "<?=$site['main_color']?>",
						"삭제DB" : "#DC3333"
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