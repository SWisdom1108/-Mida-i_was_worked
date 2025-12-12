<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["006"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	# 메뉴설정
	$secMenu = "dbMd";

	$orderBy = ($_GET['orderBy']) ? $_GET['orderBy'] : "MT.reg_date DESC, MT.idx DESC";
	
	# 콘텐츠설정
	$contentsTitle = ($code) ? "{$codeInfo['team_name']} 나의DB관리" : "전체 나의DB관리";
	$contentsInfo = "{$customLabel["tm"]}의 덴트웹 DB를 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "나의DB관리");
	array_push($contentsRoots, "목록");

	# 가이드 변수명 설정
	$guideName = "dbMd";

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	// $andQuery .= " AND use_yn = 'Y' AND EXISTS (
	// SELECT 1 FROM mt_pay_log AS pl 
	// WHERE pl.md_idx = MT.chart_num)";

	# 데이터 간단정리표

	$value = array(''=>'');

	$query = "
		SELECT
			  ( SELECT COUNT(*) FROM mt_db_dent AS MT {$andQuery} ) AS totalCnt
			, ( SELECT COUNT(*) FROM mt_db_dent AS MT {$andQuery} AND reg_date LIKE '".date("Y-m-d")."%' ) AS todayCnt
		FROM dual
	";

	$dashboard = view_pdo($query, $value);

	# 201102 생산업체정렬

	$value = array(''=>'');

	$query = "SELECT * FROM mt_member_cmpy WHERE use_yn = 'Y' AND auth_code = '003' ORDER BY idx DESC";
	$pmList = list_pdo($query, $value);
	if($_GET["pmCode"]){
		$andQuery .= " AND db.pm_code = '{$_GET["pmCode"]}'";
	}
	
	if($_GET["chart_num"]){
    $chart_num = $_GET["chart_num"];
		$andQuery .= " AND MT.chart_num LIKE '%{$chart_num}%'";
	}

	if($_GET["gender"]){
		$andQuery .= " AND MT.gender = '{$_GET["gender"]}'";
	}

	if($_GET["cs_tel"]){
		$cs_tel = $_GET["cs_tel"];
		$andQuery .= " AND MT.cs_tel LIKE '%{$cs_tel}%'";
	}

	if($_GET["cs_name"]){
		$cs_name = $_GET["cs_name"];
		$andQuery .= " AND MT.cs_name LIKE '%{$cs_name}%'";
	}

	if($_GET["rcpt_date"]){
		$rcpt_date = $_GET["rcpt_date"];
		$andQuery .= " AND MT.rcpt_date LIKE '%{$rcpt_date}%'";
	}

	if($_GET["r_date"]){
		$reg_date = $_GET["r_date"];
		$andQuery .= " AND MT.reg_date LIKE '%{$reg_date}%'";
	}

	$tmp = $andQuery;
    $join = "
        LEFT JOIN mt_member AS md
			ON MT.md_idx = md.idx
            AND md.use_yn = 'Y'
		LEFT JOIN mt_member AS dr
			ON MT.dr_idx = dr.idx
            AND dr.use_yn = 'Y'
        LEFT JOIN mt_db AS db
            ON MT.chart_num = db.chart_num
            AND db.use_yn = 'Y'
            AND db.idx = (
                SELECT MIN(idx)
                FROM mt_db
                WHERE chart_num = MT.chart_num
                AND use_yn = 'Y'
            )
        LEFT JOIN mt_member_cmpy AS cmpy
            ON cmpy.idx = db.pm_code 
			AND cmpy.use_yn = 'Y'
		INNER JOIN (
    	SELECT DISTINCT chart_num
    	FROM mt_pay_log
		    WHERE md_idx = '{$user['idx']}'
		) AS pl ON MT.chart_num = pl.chart_num
    ";
	$andQuery = $join . $andQuery;
	# 페이징 정리
	paging("mt_db_dent AS MT");

	# 201102 엑셀관련섹션
	$_SESSION["excelAndQuery"] = $andQuery;
	$_SESSION["excelOrderQuery"] = $orderQuery;

	$andQuery = $tmp;

?>

	<!-- 데이터 간단정리표 -->
	<div class="dataInfoSimpleWrap">
		<div>
			<div class="iconWrap">
				<i class="fas fa-file-import"></i>
			</div>
			<div class="conWrap">
				<ul class="dataCntList">
					<li>
						<span class="label">전체DB</span>
						<span class="value"><?=number_format($dashboard['totalCnt'])?></span>
					</li>
					<li>
						<span class="label">오늘의 업로드DB</span>
						<span class="value"><?=number_format($dashboard['todayCnt'])?></span>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<!-- 데이터 검색영역 -->
	<!-- <div class="searchWrap">
		<form method="get">
			<input type="hidden" name="code" value="<?=$code?>">
			<ul class="formWrap">
				<li>
					<span class="label">상세검색</span>
					<select class="txtBox" name="label">
						<option value="cs_name"><?=$customLabel["cs_name"]?></option>
						<option value="cs_tel"><?=$customLabel["cs_tel"]?></option>
						<option value="chart_num">차트번호</option>
					</select>
					<input type="text" class="txtBox value" name="value" value="<?=$_GET['value']?>">
				</li>
				<li class="drag">
					<span class="label">조회기간</span>
					<select class="txtBox" name="setDate">
						<option value="reg" <?=($_GET['setDate']=='reg') ? 'selected' : ''?>>등록일시</option>
						<option value="rcpt" <?=($_GET['setDate']=='rcpt') ? 'selected' : ''?>>최근진료일</option>
					</select>
					<input type="text" class="txtBox s_date" name="s_date" value="<?=$_GET['s_date']?>" dateonly>
					<span class="hypen">~</span>
					<input type="text" class="txtBox e_date" name="e_date" value="<?=$_GET['e_date']?>" dateonly>
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
	</div> -->
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
			<span class="cnt" style="margin: 0 15px; color: #CCC; font-weight: 400; top: -1.5px;">|</span>
			<select class="listSet" id="pmCode" style="margin-right: 10px;">
				<option value="">생산업체 선택</option>
			<?php while($row = $pmList->fetch(PDO::FETCH_ASSOC)){ ?>
				<option value="<?=$row["idx"]?>" <?=($_GET["pmCode"] == $row["idx"]) ? "selected" : ""?>><?=$row["company_name"]?></option>
			<?php } ?>
			</select>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="MT.reg_date ASC, MT.idx DESC">등록일시 오름차순</option>
				<option value="MT.reg_date DESC, MT.idx DESC">등록일시 내림차순</option>
				<option value="MT.rcpt_date ASC, MT.idx DESC">최근진료일 오름차순</option>
				<option value="MT.rcpt_date DESC, MT.idx DESC">최근진료일 내림차순</option>
			</select>
			<select class="listSet" id="listCnt">
				<option value="15">15개씩 보기</option>
				<option value="30">30개씩 보기</option>
				<option value="50">50개씩 보기</option>
				<option value="100">100개씩 보기</option>
				<option value="9999999">전체 보기</option>
			</select>
			<label class="left detailSearch" style="margin-left: 20px;">
				<i class="fas fa-search"></i>상세검색
			</label>
		</div>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnGray02 big dbDentDelBtn"><i class="fas fa-trash-alt"></i>삭제</button>
		</div>
		<div class="right">
			<a href="/excel/db/dbDentL" class="typeBtn btnGreen02" title="엑셀다운로드"><i class="fas fa-file-excel"></i>엑셀다운로드</a> 
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="4%">
				<col width="5%">
				<col width="5%">
				<col width="5%">
				<col width="8%">
				<col width="10%"> 
				<col width="8%"> 
				<col width="8%"> 
				<col width="10%">
				<col width="5%"> 
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
					<th rowspan="2">등록일시</th>
					<th rowspan="2">생산업체</th>
					<th rowspan="2">차트번호</th>
					<th colspan="4">고객정보</th>
					<th rowspan="2">누적금액</th>
					<th rowspan="2">금액</th>
				</tr>
				<tr>
					<th><?=$customLabel["cs_name"]?></th>
					<th><?=$customLabel["cs_tel"]?></th>
					<th>성별</th>
					<th>최근진료일</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');

				$query = "
					SELECT MT.*
						, md.m_name AS md_name
						, dr.m_name AS dr_name
                        , cmpy.company_name AS pm_name
                        , db.pm_code AS pm_code
					FROM mt_db_dent MT
					{$join}
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";

				$sql = list_pdo($query, $value);
			
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){

					# 200624 최종상담값 가져오기
					$value = array(':chart_num'=> $row['chart_num'], ':md_idx'=>$user['idx']);
					$query = "SELECT COUNT(*) AS cnt FROM mt_pay_log WHERE use_yn = 'Y' AND chart_num = :chart_num AND md_idx = :md_idx";
					
					$count_pay = view_pdo($query, $value)['cnt'];

					$hex = str_replace('#', '', $color);
					$r = hexdec(substr($hex, 0, 2));
					$g = hexdec(substr($hex, 2, 2));
					$b = hexdec(substr($hex, 4, 2));
					
			?>
				<tr class="" data-type="open" data-target="mod" data-url="/sub/db/dbDentU?idx=<?=$row['idx']?>" data-name="DB정보" >
					<td>
						<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$row['idx']?>" data-idx="<?=$row['idx']?>">
						<label class="ch" for="listDataCheck_<?=$row['idx']?>">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</td>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05" style="line-height: 15px;">
						<?=date("Y-m-d", strtotime($row['reg_date']))?>
						<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($row['reg_date']))?></span>
					</td>
					<td class="lp05" style="line-height: 15px;">
					<?php if($row['pm_name']){ ?>
						<span><?=dhtml($row["pm_name"])?></span>
						<br><span style="font-size: 12px; color: #AAA;">PM<?=$row["pm_code"]?></span>
					<?php } else { ?>
						<span>-</span>
					<?php } ?>
					</td>
					<td class="lp05"><?=$row['chart_num']?></td>
					<td><?=($row['cs_name']) ? dhtml($row['cs_name']) : "-"?></td>
					<td class="lp05"><?=($row['cs_tel']) ? $row['cs_tel'] : "-"?></td>
					<td class="lp05">
						<?php if($row['gender']){?>
						<?=$row['gender']?>
						<?php }else{?>
							-
						<?php }?>
					</td>
					<td class="lp05" style="line-height: 15px;">
						<?php if($row['rcpt_date']){?>
						<?=date("Y-m-d", strtotime($row['rcpt_date']))?>
						<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($row['rcpt_date']))?></span>
						<?php }else{?>
							-
						<?php }?>
					</td>
					<td>
						<?php
						$value = array(':chart_num'=>$row['chart_num'], ':md_idx'=>$user['idx']);
						$query = "
							SELECT SUM(pay) AS total_pay from mt_pay_log
							WHERE chart_num = :chart_num
							AND use_yn = 'Y'
							AND md_idx = :md_idx
						";
						$total_pay = view_pdo($query, $value)['total_pay'];
						$total_pay = $total_pay ? number_format($total_pay) : 0;
						?>
						<?=$total_pay?>
					</td>
					<td class="stopProgram">
                        <i class="fas fa-coins csBtn click dbPayBtn" style="font-size: 16px; color: <?=$site['main_color']?>;" onclick='popupControl("open", "payLogL", "/sub/db/payLogL?chart_num=<?=$row['chart_num']?>&md_idx=<?=$row['md_idx']?>&dr_idx=<?=$row['dr_idx']?>", "결제기록");'></i><br>
						<span style="font-size: 11px; color: #AAA;" class="lp05">(<?=number_format($count_pay)?>)</span>
                    </td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="10" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnGray02 big dbDentDelBtn"><i class="fas fa-trash-alt"></i>삭제</button>
		</div>
		<div class="right">
			<a href="/excel/db/dbDentL" class="typeBtn btnGreen02" title="엑셀다운로드"><i class="fas fa-file-excel"></i>엑셀다운로드</a> 
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<form method="get">
		<div class="popupDetail">
			<div class="popupDetail2" style="height: auto;">
				<p style="font-size: 16px; color: #333; padding: 20px 33px; padding-bottom: 10px; font-weight: 600;"><span style="color: #17008C">상세필터</span> 설정</p>
				<div class="popupDetailBody">
					<div style="width: 100%; float:left; padding:10px; margin: 0 auto;">
						<div class="detailSearchElement">
							<p class="detailElementTit">이름</p>
							<input type="text" class="txtBox value detailSearchInput" name="cs_name" placeholder="이름" value="<?=$_GET['cs_name']?>">
						</div>
		
						<div class="detailSearchElement">
							<p class="detailElementTit">연락처</p>
							<input type="text" class="txtBox value detailSearchInput" name="cs_tel" placeholder="숫자만 입력해주세요" value="<?=$_GET['cs_tel']?>">
						</div>

						<div class="detailSearchElement">
							<p class="detailElementTit">성별</p>
							<select class="detailSearchSelect" name="gender">
								<option value="">성별 선택</option>
								<option value="M" <?=($_GET['gender'] == "M") ? "selected" : ""?>>M</option>
								<option value="F" <?=($_GET['gender'] == "F") ? "selected" : ""?>>F</option>
							</select>
						</div>

						<div class="detailSearchElement">
							<p class="detailElementTit">차트번호</p>
							<input type="text" class="txtBox value detailSearchInput" name="chart_num" placeholder="숫자만 입력해주세요" value="<?=$_GET['chart_num']?>">
						</div>
		
						<div class="detailSearchElement">
							<p class="detailElementTit">등록일시</p>
							<div class="dateInputWrap">
								<div class="sDate detailSearchInput" style="margin-left: 0px;">
									<input type="text" class="txtBox" name="r_date" id="r_date" dateonly placeholder="등록일시" style="border-radius: 5px;" value="<?=$_GET['r_date']?>" autocomplete="off">
									<i class="fas fa-calendar-alt"></i>
								</div>
							</div>
						</div>

						<div class="detailSearchElement">
							<p class="detailElementTit">최근진료일</p>
							<div class="dateInputWrap">
								<div class="sDate detailSearchInput" style="margin-left: 0px;">
									<input type="text" class="txtBox" name="rcpt_date" id="rcpt_date" dateonly placeholder="최근진료일" style="border-radius: 5px;" value="<?=$_GET['rcpt_date']?>" autocomplete="off">
									<i class="fas fa-calendar-alt"></i>
								</div>
							</div>
						</div>

					</div>
				</div>
				<div style="background: #F5F5F5; margin-top: 10px; float: left; width: 100%; height: 60px; padding-top: 8px; border-bottom-right-radius: 20px; border-bottom-left-radius: 20px;">
					<label class="detailSearch right detailReload" style="border:none; color: #cccccc; margin-left: 10px;">
						<i class="fas fa-redo"></i>초기화
					</label>
					<button type="button" class="typeBtn btnGray02 popupCloseBtn left" data-target="csLogL" style="width: 100px; margin-left: 435px; color: #8C8C8C; background-color: #ffffff;">닫기</button>
					<button type="submit">
						<label class="detailSearch2 left" style="margin-left: 10px; background-color: #ffffff;">
							<i class="fas fa-search"></i>검색
						</label>
					</button>
				</div>
			</div>
		</div>
	</form>

	<style>
		.sDate { width: 10%; min-width: 140px; float:left; }
		.sDate .fa-calendar-alt { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #D8D8D8; pointer-events: none; }
	</style>
	
	<script type="text/javascript">
		$(function(){
			var datas = <?=json_encode($db_data)?>

			$(document).on("focus", "input[dateonly]", function() {
				$(this).prop("readonly", false);
			});

			$(document).on("input", "input[dateonly]", function() {
				this.value = this.value.replace(/[^0-9-]/g, '');
			});

			$(".detailSearch").click(function() {
				$(".popupDetail").show();
			});

			$(".popupCloseBtn").click(function() {
				$(".popupDetail").hide();
			});

			$(".multiple-dropdown").click(function (e) {
				e.stopPropagation();
			});

			$(".detailReload").click(function () {
				const popup = $(".popupDetail2");

				popup.find("input:not([type='checkbox'])").val("");
				popup.find("textarea").val("");
				popup.find("select").prop("selectedIndex", 0);
				popup.find(".item_box").prop("checked", false);
				popup.find(".detailSearchElement").each(function () {
					$(this).find(".detailMultiple").text("옵션 선택");
				});
			});

			$(".dropdown-btn").click(function (e) {
				e.preventDefault();
				e.stopPropagation();
				const box = $(this).siblings(".multiple-dropdown");
				$(".multiple-dropdown").not(box).removeClass("active");
				box.toggleClass("active");
			});

			$(document).click(function () {
				$(".multiple-dropdown").removeClass("active");
			});

			$(".detailSearchElement").each(function () {
				const parent = $(this);
				const checkedValues = parent.find(".item_box:checked").map(function () {
					return $(this).val();
				}).get();

				if (checkedValues.length > 0) {
					parent.find(".detailMultiple").text(`${checkedValues.length}개 선택 (${checkedValues.join(", ")})`);
				} else {
					parent.find(".detailMultiple").text("옵션 선택");
				}
			});

			$(".item_box").change(function () {
				const parent = $(this).closest(".detailSearchElement");
				const checkedValues = parent.find(".item_box:checked").map(function () {
						return $(this).val();
				}).get();
				
				if (checkedValues.length > 0) {
					var text = `${checkedValues.length}개 선택 (${checkedValues.join(", ")})`;
					parent.find(".detailMultiple").text(text);
				} else {
					parent.find(".detailMultiple").text("옵션 선택");
				}
				parent.find(".multi_etc").val(checkedValues.join("@"));
			});

			$(".dateResetBtn").click(function() {
				$(this).siblings(".sDate").find("input").val("");
			});

			$('.dbDentDelBtn').on('click', function(){
				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("삭제할 DB를 선택해주시길 바랍니다.");
					return false;
				}

				var idx = [];
				for(var i = 0; i < item.length; i++){
					idx.push($(item[i]).data("idx"));
				}
			
				idx = idx.join(",");
			
				if(confirm("선택된 DB들을 삭제하시겠습니까?")){
					$("#loadingWrap").fadeIn(350, function(){
						$.ajax({
							url : "/ajax/db/dbDentDP",
							data : {
								idx : idx
							},
							type : "POST",
							success : function(result){
								alert("삭제가 완료되었습니다.");
								window.location.reload();
							}
						})
					});
				}
			});
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>