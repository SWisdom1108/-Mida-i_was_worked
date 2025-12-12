<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	if($_GET['code']){
		$code = $_GET['code'];
		$value = array(':status_code'=>$_GET['code']);
		$query = "SELECT * FROM mc_db_cs_status WHERE use_yn = 'Y' AND status_code = :status_code AND number_yn = 'Y'";
		$codeInfo = view_pdo($query, $value);
		if(!$codeInfo){
			www("/");
			return false;
		}
	}

	# 메뉴설정
	$secMenu = "dbCalculate";
	$trdMenu = "code{$code}";
	
	# 콘텐츠설정
	$contentsTitle = "DB {$codeInfo["status_name"]} 정산통계현황";
	$contentsInfo = "숫자전용으로 기재된 해당 상담구분값의 정산통계현황을 확인하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "DB정산통계");
	array_push($contentsRoots, "{$codeInfo["status_name"]}현황");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 통계 일시 지정
	$startDate = ($_GET['s_date']) ? $_GET['s_date'] : date("Y-m-d", strtotime("- 7 days"));
	$endDate = ($_GET['e_date']) ? $_GET['e_date'] : date("Y-m-d");

	# 생산업체추출
	$pmList = [];
	$value = array(''=>'');
	$query = "SELECT idx, company_name FROM mt_member_cmpy WHERE use_yn = 'Y' AND auth_code = '003' ORDER BY idx DESC";
	$pmSQL = list_pdo($query, $value);
	while($row = $pmSQL->fetch(PDO::FETCH_ASSOC)){
		$pmList[$row["idx"]] = $row["company_name"];
	}
	if($_GET["pmCode"]){
		$dbList = [];
		$value = array(':pm_code'=>$_GET['pmCode']);
		$query = "SELECT idx FROM mt_db WHERE use_yn = 'Y' AND pm_code = :pm_code";
		$dbSQL = list_pdo($query, $value);
		while($row = $dbSQL->fetch(PDO::FETCH_ASSOC)){
			array_push($dbList, $row["idx"]);
		}
		
		$dbList = implode(",", $dbList);
		$andQuery .= " AND db_idx IN ( {$dbList} )";
	}

	# 팀추출
	$fcList = [];
	$teamList = [];
	$value = array(''=>'');
	$query = "SELECT idx, team_name FROM mt_member_team WHERE use_yn = 'Y' ORDER BY idx DESC";
	$teamSQL = list_pdo($query, $value);
	while($row = $teamSQL->fetch(PDO::FETCH_ASSOC)){
		$fcDatas = [];
		$value = array(':tm_code'=>$row['idx']);
		$query = "SELECT idx, m_name FROM mt_member WHERE use_yn = 'Y' AND tm_code = :tm_code";
		$fcSQL = list_pdo($query, $value);
		while($subRow = $fcSQL->fetch(PDO::FETCH_ASSOC)){
			$fcDatas[$subRow["idx"]] = $subRow["m_name"];
		}
		
		$teamList[$row["idx"]] = $row["team_name"];
		$fcList[$row["idx"]] = $fcDatas;
	}

	# 담당자추출
	if($_GET["tmCode"]){
		$thisTMFCList = [];
		foreach($fcList[$_GET["tmCode"]] as $mIDX => $name){
			array_push($thisTMFCList, $mIDX);
		}
		$thisTMFCList = implode(",", $thisTMFCList);
		$andQuery .= " AND (SELECT m_idx FROM mt_db WHERE idx = MT.db_idx ) IN ( {$thisTMFCList} )";
	}

	if($_GET["fcCode"]){
		$andQuery .= " AND (SELECT m_idx FROM mt_db WHERE idx = MT.db_idx ) = '{$_GET["fcCode"]}'";
	}

	# 추가 쿼리문
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d')";
	$andQuery .= " AND date_format(reg_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";
	$andQuery .= " AND status_code = '{$code}' AND use_yn = 'Y'";
	$andQuery .= " AND (SELECT use_yn FROM mt_db WHERE idx = MT.db_idx ) = 'Y'";

	# 페이징 정리
	paging("mt_db_cs_log MT");

	# 데이터 간단정리표
	$dataList = [];
	$dateDataList = [];
	$dashboard = [];
	$value = array(''=>'');
	$query = "
		SELECT MT.*
			, ( SELECT tm_code FROM mt_member WHERE idx = (SELECT m_idx FROM mt_db WHERE idx = MT.db_idx ) ) AS tm_code
			, ( SELECT m_name FROM mt_member WHERE idx = (SELECT m_idx FROM mt_db WHERE idx = MT.db_idx ) ) AS m_name
			, ( SELECT pm_code FROM mt_db WHERE idx = MT.db_idx ) AS pm_code
			, ( SELECT cs_name FROM mt_db WHERE idx = MT.db_idx ) AS cs_name
			, ( SELECT cs_tel FROM mt_db WHERE idx = MT.db_idx ) AS cs_tel
			, ( SELECT reg_date FROM mt_db WHERE idx = MT.db_idx ) AS cs_date
		FROM mt_db_cs_log MT
		{$andQuery}
		{$orderQuery}
		{$limitQuery}
	";
	$dataSQL = list_pdo($query, $value);
	if($_SERVER['REMOTE_ADDR'] == '118.45.184.18'){
		// echo "SELECT MT.*
		// 	, ( SELECT tm_code FROM mt_member WHERE idx = MT.reg_idx ) AS tm_code
		// 	, ( SELECT m_name FROM mt_member WHERE idx = MT.reg_idx ) AS m_name
		// 	, ( SELECT pm_code FROM mt_db WHERE idx = MT.db_idx ) AS pm_code
		// 	, ( SELECT cs_name FROM mt_db WHERE idx = MT.db_idx ) AS cs_name
		// 	, ( SELECT cs_tel FROM mt_db WHERE idx = MT.db_idx ) AS cs_tel
		// 	, ( SELECT reg_date FROM mt_db WHERE idx = MT.db_idx ) AS cs_date
		// FROM mt_db_cs_log MT
		// {$andQuery}
		// {$orderQuery}
		// {$limitQuery}";

	}
	while($row = $dataSQL->fetch(PDO::FETCH_ASSOC)){
		$row["memo"] = preg_replace("/[^0-9]/s", "", $row["memo"]);
		$dashboard["totalCnt"] += $row["memo"];
		$dateDataList[date("Y-m-d", strtotime($row["reg_date"]))] += $row["memo"];
		
		if($row["pm_code"] < 10){
			$row["pm_code"] = "000{$row["pm_code"]}";
		} else if($row["pm_code"] < 100){
			$row["pm_code"] = "00{$row["pm_code"]}";
		} else if($row["pm_code"] < 1000){
			$row["pm_code"] = "0{$row["pm_code"]}";
		}
		
		if($row["tm_code"] < 10){
			$row["tm_code"] = "000{$row["tm_code"]}";
		} else if($row["tm_code"] < 100){
			$row["tm_code"] = "00{$row["tm_code"]}";
		} else if($row["tm_code"] < 1000){
			$row["tm_code"] = "0{$row["tm_code"]}";
		}
		
		if($row["reg_idx"] < 10){
			$row["reg_idx"] = "000{$row["reg_idx"]}";
		} else if($row["reg_idx"] < 100){
			$row["reg_idx"] = "00{$row["reg_idx"]}";
		} else if($row["reg_idx"] < 1000){
			$row["reg_idx"] = "0{$row["reg_idx"]}";
		}
		
		array_push($dataList, $row);
	}

	$_SESSION["chartExcelAndQuery"] = $andQuery;
	$_SESSION["chartExcelOrderQuery"] = $orderQuery;



?>
	
	<!-- 데이터 검색영역 -->
	<div class="searchWrap" style="margin-bottom: 50px;">
		<form method="get">
			<input type="hidden" name="code" value="<?=$code?>">
			<ul class="formWrap">
				<li>
					<span class="label">상세검색</span>
					<select class="txtBox" name="pmCode">
						<option value="">생산업체 선택</option>
					<?php foreach($pmList as $code => $name){ ?>
						<option value="<?=$code?>" <?=($_GET["pmCode"] == $code) ? "selected" : ""?>><?=$name?></option>
					<?php } ?>
					</select>
					<select class="txtBox" name="tmCode">
						<option value=""><?=$customLabel["tm"]?> 선택</option>
					<?php foreach($teamList as $code => $name){ ?>
						<option value="<?=$code?>" <?=($_GET["tmCode"] == $code) ? "selected" : ""?>><?=$name?></option>
					<?php } ?>
					</select>
					<select class="txtBox" name="fcCode">
						<option value="">담당자 선택</option>
					<?php if($_GET["tmCode"]){ ?>
						<?php foreach($fcList[$_GET["tmCode"]] as $code => $name){ ?>
							<option value="<?=$code?>" <?=($_GET["fcCode"] == $code) ? "selected" : ""?>><?=$name?></option>
						<?php } ?>
					<?php } ?>
					</select>
				</li>
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
						<span class="label">정산현황</span>
						<span class="value"><?=number_format($dashboard['totalCnt'])?><?=$codeInfo["number_label"]?></span>
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
				<option value="reg_date ASC">일시 오름차순</option>
				<option value="reg_date DESC">일시 내림차순</option>
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
			<button type="button" class="typeBtn btnGray02 big dbCalculateDeleteBtn"><i class="fas fa-trash-alt"></i>정산내역삭제</button>
		</div>
		<div class="right">
			<a href="/excel/chart/dbCalculateAllL?code=<?=$_GET["code"]?>" class="typeBtn btnGreen02" title="엑셀다운로드"><i class="fas fa-file-excel"></i>엑셀다운로드</a> 
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="4%">
				<col width="11%">
				<col width="10%">
				
				<col width="15%">
				<col width="15%">
				<col width="11%">
				
				<col width="10%">
				<col width="10%">
				
				<col width="10%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">
						<input type="checkbox" id="listDataAllCheck">
						<label class="ch" for="listDataAllCheck">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</th>
					<th rowspan="2">NO</th>
					<th rowspan="2">일시</th>
					<th rowspan="2">생산업체</th>
					
					<th colspan="3">DB정보</th>
					
					<th colspan="2">담당정보</th>
					
					<th rowspan="2">정산내역</th>
				</tr>
				<tr>
					<th>이름</th>
					<th>연락처</th>
					<th>접수일시</th>
					
					<th><?=$customLabel["tm"]?></th>
					<th style="border-right: 1px solid #FFF;">담당자</th>
				</tr>
			</thead>
			<tbody>
			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="10" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			
				<?php foreach($dataList as $key => $data){ ?>
					<tr>
						<td>
							<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$data['idx']?>" data-idx="<?=$data['idx']?>">
							<label class="ch" for="listDataCheck_<?=$data['idx']?>">
								<i class="fas fa-check-square on"></i>
								<i class="far fa-square off"></i>
							</label>
						</td>
						<td class="lp05"><?=listNo()?></td>
						<td class="lp05" style="line-height: 15px;">
							<?=date("Y-m-d", strtotime($data['reg_date']))?>
							<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($data['reg_date']))?></span>
						</td>
						<td class="lp05" style="line-height: 15px;">
						<?php if($pmList[$data["pm_code"]]){ ?>
							<span><?=$pmList[$data["pm_code"]]?></span>
							<br><span style="font-size: 12px; color: #AAA;">PM<?=$data["pm_code"]?></span>
						<?php } else { ?>
							<span>-</span>
						<?php } ?>
						</td>
						
						<td><?=($data['cs_name']) ? $data['cs_name'] : "-"?></td>
						<td class="lp05"><?=($data['cs_tel']) ? $data['cs_tel'] : "-"?></td>
						<td class="lp05" style="line-height: 15px;">
							<?=date("Y-m-d", strtotime($data['cs_date']))?>
							<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($data['cs_date']))?></span>
						</td>
						
						<td class="lp05" style="line-height: 15px;">
						<?php if($teamList[$data["tm_code"]]){ ?>
							<span><?=$teamList[$data["tm_code"]]?></span>
							<br><span style="font-size: 12px; color: #AAA;">TM<?=$data["tm_code"]?></span>
						<?php } else { ?>
							<span>-</span>
						<?php } ?>
						</td>
						<td class="lp05" style="line-height: 15px;">
						<?php if($data['m_name']){ ?>
							<span><?=$data["m_name"]?></span>
							<br><span style="font-size: 12px; color: #AAA;">FC<?=$data["reg_idx"]?></span>
						<?php } else { ?>
							<span>-</span>
						<?php } ?>
						</td>
						
						<td class="lp05"><?=number_format($data["memo"])?><?=$codeInfo["number_label"]?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnGray02 big dbCalculateDeleteBtn"><i class="fas fa-trash-alt"></i>정산내역삭제</button>
		</div>
		<div class="right">
			<a href="/excel/chart/dbCalculateAllL?code=<?=$_GET["code"]?>" class="typeBtn btnGreen02" title="엑셀다운로드"><i class="fas fa-file-excel"></i>엑셀다운로드</a> 
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<script type="text/javascript">
		$(function(){
			
			$(".dbCalculateDeleteBtn").click(function(){
				var idxs = [];
				var item = $(".listDataCheck:checked");
				for(var i = 0; i < item.length; i++){
					idxs.push($(item[i]).data("idx"));
				}
				
				if(!idxs.length){
					alert("선택된 데이터가 존재하지 않습니다.");
					return false;
				}
				
				loading(function(){
					$.ajax({
						url : "/ajax/chart/dbCalculateAllDP",
						type : "POST",
						data : {
							idxs : idxs
						},
						success : function(result){
							if(result == "success"){
								alert("삭제가 완료되었습니다.");
								window.location.reload();
							} else {
								alert("알 수 없는 오류로 삭제에 실패하였습니다.");
								loadingClose();
							}
						}
					});
				});
			});
			
			var fcList = <?=json_encode($fcList)?>;
			function fcListSetting(){
				var tmCode = $("select[name='tmCode']").val();
				$("select[name='fcCode']").html('<option value="">담당자 선택</option>');
				
				if(fcList[tmCode]){
					var html = "";
					$.each(fcList[tmCode], function(code, name){
						html += '<option value="' + code + '">' + name + '</option>';
					});
					
					$("select[name='fcCode']").append(html);
				}
			}
			
			$("select[name='tmCode']").change(function(){
				fcListSetting();
			});

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
						["정산현황"
							<?php
								$newDate = date("Y-m-d", strtotime("-1 day", strtotime($startDate)));
								while(true) {
									$newDate = date("Y-m-d", strtotime("+1 day", strtotime($newDate)));
									echo ',"'.$dateDataList[$newDate].'"';
									if($newDate == $endDate) break;
								}
							 ?>
						]
					],
					types: {
						"정산현황" : "bar"
					},
					colors: {
						"정산현황" : "<?=$site['main_color']?>"
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