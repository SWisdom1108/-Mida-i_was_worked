<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	# 메뉴설정
	$secMenu = "dbpay";
	
	# 콘텐츠설정
	$contentsTitle = "금액업로드";
	$contentsInfo = "금액업로드를 하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "금액업로드");
	array_push($contentsRoots, "목록");

	# 가이드 변수명 설정
	$guideName = "dbpay";

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND use_yn = 'Y' ";

	# 데이터 간단정리표

	$value = array(''=>'');

	$query = "
		SELECT
			  ( SELECT COUNT(*) FROM mt_db_dent {$andQuery} ) AS totalCnt
			, ( SELECT COUNT(*) FROM mt_db_dent {$andQuery} AND reg_date LIKE '".date("Y-m-d")."%' ) AS todayCnt
		FROM dual
	";

	$dashboard = view_pdo($query, $value);

	if($_GET["cs_name"]){
		$cs_name = $_GET["cs_name"];
		$andQuery .= " AND cs_name LIKE '%{$cs_name}%'";
	}

	if($_GET["treat_code"]){
		$andQuery .= " AND treat_code = '{$_GET["treat_code"]}'";
	}

	if($_GET["chart_num"]){
		$andQuery .= " AND chart_num = '{$_GET["chart_num"]}'";
	}

	if($_GET["md_name"]){
		$md_name = $_GET["md_name"];
		$md_name_val = array(':m_name' => "%{$md_name}%");
		$query = "SELECT * FROM mt_member WHERE use_yn = 'Y' AND auth_code = '006' AND m_name LIKE :m_name";
		
		$md_idx_arr = [];
		$md_list = list_pdo($query, $md_name_val);
		while($row = $md_list ->fetch(PDO::FETCH_ASSOC)){ 
			$md_idx_arr[] = $row['idx'];
		}
		$md_idx_in = implode(', ', $md_idx_arr);

		$andQuery .= " AND md_idx IN ({$md_idx_in})";
	}

	if($_GET["dr_name"]){
		$dr_name = $_GET["dr_name"];
		$dr_name_val = array(':m_name' => "%{$dr_name}%");
		$query = "SELECT * FROM mt_member WHERE use_yn = 'Y' AND auth_code = '007' AND m_name LIKE :m_name";

		$dr_idx_arr = [];
		$dr_list = list_pdo($query, $dr_name_val);
		while($row = $dr_list ->fetch(PDO::FETCH_ASSOC)){ 
			$dr_idx_arr[] = $row['idx'];
		}
		$dr_idx_in = implode(', ', $dr_idx_arr);

		$andQuery .= " AND dr_idx IN ({$dr_idx_in})";
	}

	if($_GET["pay_date"]){
		$pay_date = $_GET["pay_date"];
		$andQuery .= " AND pay_date LIKE '%{$pay_date}%'";
	}

	# 검색값 정리
	$_SEARCH["md_name"] = " AND md_idx IN ( SELECT idx FROM mt_member WHERE m_name LIKE '%{$_GET["value"]}%' )";
	$_SEARCH["dr_name"] = " AND dr_idx IN ( SELECT idx FROM mt_member WHERE m_name LIKE '%{$_GET["value"]}%' )";
	search();

	# 페이징 정리
	paging("mt_db_pay");

	# 201102 엑셀관련섹션
	$_SESSION["excelAndQuery"] = $andQuery;
	$_SESSION["excelOrderQuery"] = $orderQuery;

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
						<option value="md_name">실장명</option>
						<option value="dr_name">의사명</option>
					</select>
					<input type="text" class="txtBox value" name="value" value="<?=$_GET['value']?>">
				</li>
				<li class="drag">
					<span class="label">조회기간</span>
					<select class="txtBox" name="setDate">
						<option value="reg" <?=($_GET['setDate']=='reg') ? 'selected' : ''?>>등록일시</option>
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
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="reg_date ASC">등록일시 오름차순</option>
				<option value="reg_date DESC">등록일시 내림차순</option>
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
			<button type="button" class="typeBtn btnMain big dbPayInsertBtn"><i class="fas fa-exchange-alt"></i>금액적용</button>
			<div class="line"></div>
            <button type="button" class="typeBtn btnGray02 big dbPayDelBtn"><i class="fas fa-trash-alt"></i>삭제</button>
		</div>
		<div class="right">
            <button type="button" class="typeBtn btnGreen01" onclick="popupControl('open', 'excel', '/sub/db/excel/dbPay', 'DB 대량업로드');"><i class="fas fa-file-excel"></i>엑셀업로드</button> 
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="4%">
				<col width="4%">
				<col width="5%">
				<col width="5%">
				<col width="10%">
				<col width="8%">
				<col width="10%"> 
				<col width="8%"> 
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
					<th >NO</th>
					<th >이름</th>
					<th >진료항목</th>
					<th >차트번호</th>
					<th >금액</th>
					<th >실장명</th>
					<th >닥터명</th>
					<th >수납일</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');

				$query = "
					SELECT MT.*
					, ( SELECT m_name FROM mt_member WHERE idx = MT.md_idx ) AS md_name
					, ( SELECT m_name FROM mt_member WHERE idx = MT.dr_idx ) AS dr_name
					FROM mt_db_pay MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
			
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){

					$hex = str_replace('#', '', $color);
					$r = hexdec(substr($hex, 0, 2));
					$g = hexdec(substr($hex, 2, 2));
					$b = hexdec(substr($hex, 4, 2));
				
			?>

				<tr class="rowMove popupBtn" data-type="open" data-target="mod" data-url="/sub/db/dbPayU?idx=<?=$row['idx']?>" data-name="DB정보">
					<td>
						<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$row['idx']?>" data-idx="<?=$row['idx']?>">
						<label class="ch" for="listDataCheck_<?=$row['idx']?>">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</td>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=$row['cs_name']?></td>
					<td class="lp05">
                        <?php
                        $value = array('treat_code' => $row['treat_code']);
                        $query = "
                            SELECT treat_name
                            FROM mc_treatment_code
                            WHERE treat_code = :treat_code
                        ";
                        $treat_name = view_pdo($query, $value)['treat_name'];
                        ?>
                        <?=$treat_name?>
                    </td>
					<td class="lp05"><?=$row['chart_num']?></td>
					<td><?=number_format($row['pay'])?></td>
					<td class="lp05">
						<?php if($row['md_name']){?>
						<?=$row['md_name']?>
						<?php }else{?>
							-
						<?php }?>
					</td>
                    <td class="lp05">
						<?php if($row['dr_name']){?>
						<?=$row['dr_name']?>
						<?php }else{?>
							-
						<?php }?>
					</td>
					<td class="lp05" style="line-height: 15px;">
						<?=date("Y-m-d", strtotime($row['pay_date']))?>
					</td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="9" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnMain big dbPayInsertBtn"><i class="fas fa-exchange-alt"></i>금액적용</button>
			<div class="line"></div>
            <button type="button" class="typeBtn btnGray02 big dbPayDelBtn"><i class="fas fa-trash-alt"></i>삭제</button>
		</div>
		<div class="right">
            <button type="button" class="typeBtn btnGreen01" onclick="popupControl('open', 'excel', '/sub/db/excel/dbPay', 'DB 대량업로드');"><i class="fas fa-file-excel"></i>엑셀업로드</button> 
		</div>
	</div>

  <!-- 페이징 -->
	<?=paging()?>

	<?php

		$value = array('' => '');
    $query = "
        SELECT treat_code, treat_name
        FROM mc_treatment_code
        WHERE use_yn = 'Y'
    ";
    $treatment = list_pdo($query, $value);

	?>

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
							<p class="detailElementTit">진료항목</p>
							<select class="detailSearchSelect" name="treat_code">
								<option value="">선택</option>
							<?php while($row = $treatment ->fetch(PDO::FETCH_ASSOC)){ ?>
								<option value="<?=$row['treat_code']?>" <?=($_GET['treat_code'] == $row['treat_code']) ? "selected" : ""?>><?=$row['treat_name']?></option>
							<?php } ?>
							</select>
						</div>
		
						<div class="detailSearchElement">
							<p class="detailElementTit">차트번호</p>
							<input type="text" class="txtBox value detailSearchInput" name="chart_num" placeholder="차트번호를 입력해주세요" value="<?=$_GET['chart_num']?>">
						</div>
		
						<div class="detailSearchElement">
							<p class="detailElementTit">실장명</p>
							<input type="text" class="txtBox value detailSearchInput" name="md_name" placeholder="실장명" value="<?=$_GET['md_name']?>">
							<input type="hidden" name="md_idx" value="<?=$_GET['md_name']?>">
						</div>

						<div class="detailSearchElement">
							<p class="detailElementTit">닥터명</p>
							<input type="text" class="txtBox value detailSearchInput" name="dr_name" placeholder="닥터명" value="<?=$_GET['dr_name']?>">
							<input type="hidden" name="dr_idx" value="<?=$_GET['dr_name']?>">
						</div>
		
						<div class="detailSearchElement">
							<p class="detailElementTit">수납일</p>
							<div class="dateInputWrap">
								<div class="sDate detailSearchInput">
									<input type="text" class="txtBox" name="pay_date" id="pay_date" dateonly placeholder="분배일" style="border-radius: 5px;" value="<?=$_GET['pay_date']?>" autocomplete="off">
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

			$(document).on("click",".popup_btns",function(){
				var data = $(this).attr("data-idx");
				var idx = "";
				$(".popupBox").find("iframe").attr("src","/sub/db/csLogL?idx="+data);
				$.each(datas, function(index, item){
					if(item['idx'] == data){
						var prev_idx = datas[index-1];
						var next_idx = datas[index+1];
						if(prev_idx || next_idx){
							if(prev_idx && data != prev_idx['idx']){
								$(".prev_btn").show();
								$(".prev_btn").attr('data-idx',prev_idx['idx']);
							}else{
								$(".prev_btn").hide();
							}
							if(next_idx && data != next_idx['idx']){
								$(".next_btn").show();
								$(".next_btn").attr('data-idx',next_idx['idx']);
							}else{
								$(".next_btn").hide();
							}
						}
					}
				})
			})

			$('.dbPayDelBtn').on('click', function(){
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
			
				if(confirm("선택된 금액들을 삭제하시겠습니까?(영구 삭제 됩니다)")){
					$("#loadingWrap").fadeIn(350, function(){
						$.ajax({
							url : "/ajax/db/dbPayDP",
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

			$('.dbPayInsertBtn').on('click', function(){
				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("금액을 선택해주시길 바랍니다.");
					return false;
				}

				var idx = [];
				for(var i = 0; i < item.length; i++){
					idx.push($(item[i]).data("idx"));
				}
			
				idx = idx.join(",");
			
				if(confirm("선택된 금액들을 적용하시겠습니까?")){
					$("#loadingWrap").fadeIn(350, function(){
						$.ajax({
							url : "/ajax/db/payLogWP",
							data : {
								idx : idx
							},
							type : "POST",
							success : function(result){
								alert("적용이 완료되었습니다.");
								window.location.reload();
							}
						})
					});
				}
			});
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>