<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 메뉴설정
	$secMenu = "trash";
	
	# 콘텐츠설정
	$contentsTitle = "휴지통";
	$contentsInfo = "삭제된 DB를 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "휴지통");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND use_yn = 'N'";

	# 초기 정렬
	$_GET['orderBy'] = ($_GET['orderBy']) ? $_GET['orderBy'] : "reg_date DESC";

	# 데이터 간단정리표
	$value = array(''=>'');

	$query = "
		SELECT
			  ( SELECT COUNT(*) FROM mt_db {$andQuery} ) AS totalCnt
		FROM dual
	";
	$dashboard = view_pdo($query, $value);

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_db");

	# 컬럼 정리
	$columnCnt = 0;
	$columnArr = [];
	$value = array(':use_yn'=>'Y');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = :use_yn
		AND list_yn = 'Y'
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);

	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$columnCnt++;
		
		$thisdatas = [];
		$thisdatas['name'] = $row['column_name'];
		$thisdatas['code'] = $row['column_code'];
		$thisdatas['type'] = $row['column_type'];

		
		$columnArr[$columnCnt] = $thisdatas;
	}
	$columnWidth = 40 / ($columnCnt + 2);

	if($_GET["csStatusCode"]){
		$andQuery .= " AND cs_status_code = '{$_GET["csStatusCode"]}'";
	}

	if($_GET["fcCode"]){
		$andQuery .= " AND m_idx = '{$_GET["fcCode"]}'";
	}
	
	if($_GET["cs_tel"]){
		$cs_tel = $_GET["cs_tel"];
		$andQuery .= " AND cs_tel LIKE '%{$cs_tel}%'";
	}

	if($_GET["cs_name"]){
		$cs_name = $_GET["cs_name"];
		$andQuery .= " AND cs_name LIKE '%{$cs_name}%'";
	}

	if($_GET["order_by_date"]){
		$order_by_date = $_GET["order_by_date"];
		$andQuery .= " AND order_by_date LIKE '%{$order_by_date}%'";
	}

	if($_GET["r_date"]){
		$reg_date = $_GET["r_date"];
		$andQuery .= " AND reg_date LIKE '%{$reg_date}%'";
	}

	if($_GET["cs_etc01"]){
		$cs_etc01 = $_GET["cs_etc01"];
    $andQuery .= " AND cs_etc01 LIKE '%{$cs_etc01}%'";
	}

	if($_GET["cs_etc02"]){
		$cs_etc02 = $_GET["cs_etc02"];
		$andQuery .= " AND cs_etc02 LIKE '%{$cs_etc02}%'";
	}

	if($_GET["cs_etc03"]){
		$cs_etc03 = $_GET["cs_etc03"];
		$andQuery .= " AND cs_etc03 LIKE '%{$cs_etc03}%'";
	}

	if($_GET["cs_etc04"]){
		$cs_etc04 = $_GET["cs_etc04"];
		$andQuery .= " AND cs_etc04 LIKE '%{$cs_etc04}%'";
	}

	if($_GET["cs_etc05"]){
		$cs_etc05 = $_GET["cs_etc05"];
		$andQuery .= " AND cs_etc05 LIKE '%{$cs_etc05}%'";
	}

	if($_GET["cs_etc06"]){
		$cs_etc06 = $_GET["cs_etc06"];
		$andQuery .= " AND cs_etc06 LIKE '%{$cs_etc06}%'";
	}

	if($_GET["cs_etc07"]){
		$cs_etc07 = $_GET["cs_etc07"];
		$andQuery .= " AND cs_etc07 LIKE '%{$cs_etc07}%'";
	}

	if($_GET["cs_etc08"]){
		$cs_etc08 = $_GET["cs_etc08"];
		$andQuery .= " AND cs_etc08 LIKE '%{$cs_etc08}%'";
	}

	if($_GET["cs_etc09"]){
		$cs_etc09 = $_GET["cs_etc09"];
		$andQuery .= " AND cs_etc09 LIKE '%{$cs_etc09}%'";
	}

	if($_GET["cs_etc10"]){
		$cs_etc10 = $_GET["cs_etc10"];
		$andQuery .= " AND cs_etc10 LIKE '%{$cs_etc10}%'";
	}

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
						<span class="label">전체 삭제된DB</span>
						<span class="value"><?=number_format($dashboard['totalCnt'])?></span>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<!-- 데이터 검색영역 -->
	<!-- <div class="searchWrap">
		<form method="get">
			<ul class="formWrap">
				<li>
					<span class="label">상세검색</span>
					<select class="txtBox" name="label">
						<option value="cs_name"><?=$customLabel["cs_name"]?></option>
						<option value="cs_tel"><?=$customLabel["cs_tel"]?></option>
					<?php foreach($columnArr as $val){ ?>
						<option value="<?=$val['code']?>"><?=$val['name']?></option>
					<?php } ?>
					</select>
					<input type="text" class="txtBox value" name="value" value="<?=$_GET['value']?>">
				</li>
				<li class="drag">
					<span class="label">조회기간</span>
					<select class="txtBox" name="setDate">
						<option value="reg" <?=($_GET['setDate']=='reg') ? 'selected' : ''?>>등록일시</option>
						<option value="order_by" <?=($_GET['setDate']=='order_by') ? 'selected' : ''?>>분배일시</option>
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
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="made_date ASC">생산일시 오름차순</option>
				<option value="made_date DESC">생산일시 내림차순</option>
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
			<button type="button" class="typeBtn btnMain big returnBtn"><i class="fas fa-undo"></i>DB복원</button>
			<?php if($user['auth_code'] == "001"){ ?>
				<button type="button" class="typeBtn btnRed big foreverDeleteBtn"><i class="fas fa-exclamation-triangle"></i>DB영구삭제</button>
			<?php } ?>
		</div>
		<div class="right">
			<?php if($user['excel_yn'] =="Y"){ ?>
				<button type="button" class="typeBtn btnGreen01" style="background-color: #fff; color: #b3b3b3; border-color: #b3b3b3;" onclick="popupControl('open', 'excel', '/sub/db/excel/trashSelecteAll', '엑셀항목설정<span style=\'color: #999999; margin-left: 20px;font-size: 15px; font-weight: 100;\'> 원하시는 항목을 선택하여 엑셀을 다운로드 받으실 수 있습니다.</span>');"><i class="fas fa-file-excel"></i>엑셀항목설정</button> 
				<div class="line" style="width: 1px; height: 35px; float: left; background-color: #CCC; margin: 0 15px;"></div>
			<?php } ?>
			<?php if($user['excel_yn'] =="Y"){ ?>
				<a href="/excel/db/trashAllL" class="typeBtn btnGreen02" title="엑셀다운로드"><i class="fas fa-file-excel"></i>엑셀다운로드</a> 
			<?php } ?>
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="4%">
				<col width="8%">
			<?php for($i = 0; $i < ($columnCnt + 2); $i++){ ?>
				<col width="<?=$columnWidth?>%">
			<?php } ?>
				<col width="8%">
				<col width="10%">
				<col width="8%">
				<col width="8%">
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
					<th rowspan="2">DB고유번호</th>
					<th colspan="<?=($columnCnt + 2)?>">DB정보</th>
					<th colspan="4">분배정보</th>
					<th rowspan="2">등록일시</th>
				</tr>
				<tr>
					<th><?=$customLabel["cs_name"]?></th>
					<th><?=$customLabel["cs_tel"]?></th>
				<?php foreach($columnArr as $val){ ?>
					<th><?=$val['name']?></th>
				<?php } ?>
					<th>분배여부</th>
					<th>분배일시</th>
					<th><?=$customLabel["tm"]?></th>
					<th style="border-right: 1px solid #FFF;">담당자</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						, ( SELECT company_name FROM mt_member_cmpy WHERE idx = MT.pm_code ) AS pm_name
						, ( SELECT team_name FROM mt_member_team WHERE idx = MT.tm_code ) AS tm_name
						, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS m_name
					FROM mt_db MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr>
					<td>
						<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$row['idx']?>" data-idx="<?=$row['idx']?>">
						<label class="ch" for="listDataCheck_<?=$row['idx']?>">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</td>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05">D-<?=$row['idx']?></td>
					<td><?=($row['cs_name']) ? dhtml($row['cs_name']) : "-"?></td>
					<td class="lp05"><?=($row['cs_tel']) ? $row['cs_tel'] : "-"?></td>
				<?php foreach($columnArr as $val){ ?>
					<td class="lp05 <?=($val['type'] == "file")? "stopProgram" : ""?>"><?php
						if($val['type'] == "file"){
							if($row["{$val['code']}"]){
								$value = explode( '@#@#', $row["{$val['code']}"] );
								echo "<a href='/upload/db_etc/{$value[0]}' class='db_csdwon' download='{$value[1]}'>{$value[1]}<i class=\"fas fa-download\"></i></a>";	
							}else{
								echo "-";
							}
						} else{
							echo ($row["{$val['code']}"]) ? $row["{$val['code']}"] : "-";
						}
						?></td>
				<?php } ?>
					<td class="lp05" style="font-weight: bold;"><?=($row['dist_code'] == "001") ? "<span style='color: #CCC;'>분배전</span>" : "분배완료"?></td>
					<td class="lp05" style="line-height: 15px;">
					<?php if($row['dist_code'] == "002"){ ?>
						<?=date("Y-m-d", strtotime($row['order_by_date']))?>
						<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($row['order_by_date']))?></span>
					<?php } else { ?>
						<span>-</span>
					<?php } ?>
					</td>
					<td class="lp05" style="line-height: 15px;">
					<?php if($row['tm_name'] && $row['dist_code'] == "002"){ ?>
						<span><?=dhtml($row["tm_name"])?></span>
						<br><span style="font-size: 12px; color: #AAA;">TM<?=$row["tm_code"]?></span>
					<?php } else { ?>
						<span>-</span>
					<?php } ?>
					</td>
					<td class="lp05" style="line-height: 15px;">
					<?php if($row['m_name'] && $row['dist_code'] == "002"){ ?>
						<span><?=dhtml($row["m_name"])?></span>
						<br><span style="font-size: 12px; color: #AAA;">FC<?=$row["m_idx"]?></span>
					<?php } else { ?>
						<span>-</span>
					<?php } ?>
					</td>
					<td class="lp05" style="line-height: 15px;">
						<?=date("Y-m-d", strtotime($row['reg_date']))?>
						<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($row['reg_date']))?></span>
					</td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="<?=($columnCnt + 10)?>" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnMain big returnBtn"><i class="fas fa-undo"></i>DB복원</button>
			<?php if($user['auth_code'] == "001"){ ?>
				<button type="button" class="typeBtn btnRed big foreverDeleteBtn"><i class="fas fa-exclamation-triangle"></i>DB영구삭제</button>
			<?php } ?>
		</div>
		<div class="right">
		<?php if($user['excel_yn'] =="Y"){ ?>
				<button type="button" class="typeBtn btnGreen01" style="background-color: #fff; color: #b3b3b3; border-color: #b3b3b3;" onclick="popupControl('open', 'excel', '/sub/db/excel/trashSelecteAll', '엑셀항목설정<span style=\'color: #999999; margin-left: 20px;font-size: 15px; font-weight: 100;\'> 원하시는 항목을 선택하여 엑셀을 다운로드 받으실 수 있습니다.</span>');"><i class="fas fa-file-excel"></i>엑셀항목설정</button> 
				<div class="line" style="width: 1px; height: 35px; float: left; background-color: #CCC; margin: 0 15px;"></div>
			<?php } ?>
		<?php if($user['excel_yn'] =="Y"){ ?>
			<a href="/excel/db/trashAllL" class="typeBtn btnGreen02" title="엑셀다운로드"><i class="fas fa-file-excel"></i>엑셀다운로드</a> 
		<?php } ?>
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<form method="get">
		<div class="popupDetail">
			<div class="popupDetail2">
				<p style="font-size: 16px; color: #333; padding: 20px 33px; padding-bottom: 10px; font-weight: 600;"><span style="color: #17008C">상세필터</span> 설정</p>
				<div class="popupDetailBody" style="height: 430px;">
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
							<p class="detailElementTit">분배일</p>
							<div class="dateInputWrap">
								<div class="sDate detailSearchInput">
									<input type="text" class="txtBox" name="order_by_date" id="order_by_date" dateonly placeholder="분배일" style="border-radius: 5px;" value="<?=$_GET['order_by_date']?>" autocomplete="off">
									<i class="fas fa-calendar-alt"></i>
								</div>
								<!-- <div class="dateResetBtn" style="cursor: pointer;">
									<i class="fas fa-redo"></i>
								</div> -->
							</div>
						</div>
						<div class="detailSearchElement">
							<p class="detailElementTit">등록일</p>
							<div class="dateInputWrap">
								<div class="sDate detailSearchInput">
									<input type="text" class="txtBox" name="r_date" id="r_date" dateonly placeholder="등록일" style="border-radius: 5px;" value="<?=$_GET['r_date']?>" autocomplete="off">
									<i class="fas fa-calendar-alt"></i>
								</div>
								<!-- <div class="dateResetBtn" style=" cursor: pointer;">
									<i class="fas fa-redo"></i>
								</div> -->
							</div>
						</div>
					</div>
					
					<?php 
						if ($columnCnt > 0) {
					?>
					<hr class="detailElementLine">
		
					<div style="width: 100%; float:left; padding:10px; margin: 0 auto;">
						<p class="detailElementTit2">DB 상세정보</p>
						<?php 
							$idx = 0;
							foreach ($columnArr as $val) {
								$code_num = $val['code'];
								$idx++;
								
		
								// 텍스트 입력
								if ($val['type'] == 'text' || $val['type'] == 'textarea' || $val['type'] == 'number') { ?>
									<div class="detailSearchElement">
										<p class="detailElementTit"><?=$val['name']?></p>
										<input type="text" class="txtBox value detailSearchInput" name="<?=$val['code']?>" placeholder="<?=$val['name']?>" value="<?=$_GET[$code_num]?>">
									</div>
								<?php	} ?>
	
								<!-- 단일 선택 -->
								<?php if ($val['type'] == 'select' || $val['type'] == 'radio') { ?>
									<div class="detailSearchElement">
										<p class="detailElementTit"><?=$val['name']?></p>
											<?php
												$selectArr = [];
												$value = array(':idx' => $idx);
												$query = "SELECT info_val FROM mt_db_cs_info_detail WHERE use_yn = 'Y' AND info_idx = :idx ORDER BY sort ASC";
												$select_info = list_pdo($query, $value);
		
												while($row = $select_info->fetch(PDO::FETCH_ASSOC)){
													$thisdatas = [];
													$thisdatas['info_val'] = $row['info_val'];
													
													array_push($selectArr, $thisdatas);
												}	
											?>
										<select class="detailSearchSelect" name="<?=$val['code']?>">
											<option value="">선택</option>
										<?php foreach($selectArr as $r) { ?>
											<option value="<?=$r['info_val']?>" <?=($_GET[$code_num] == $r['info_val']) ? "selected" : ""?>><?=$r['info_val']?></option>
										<?php } ?>
										</select>
									</div>
								<?php	} ?>
		
								<!-- 날짜선택 -->
								<?php if ($val['type'] == 'datepicker') { ?>
									<div class="detailSearchElement">
										<p class="detailElementTit"><?=$val['name']?></p>
										<div class="dateInputWrap">
											<div class="sDate detailSearchInput " style="width: 220px; position: relative;">
												<input type="text" class="txtBox" style="border-radius: 5px;" name="<?=$val['code']?>" id="<?=$val['code']?>" dateonly placeholder="<?=$val['name']?>"  value="<?=$_GET[$code_num]?>" autocomplete="off">
												<i class="fas fa-calendar-alt" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color:#D8D8D8;"></i>
											</div>
											<!-- <div class="dateResetBtn" style="cursor: pointer;">
												<i class="fas fa-redo"></i>
											</div> -->
										</div>
									</div>
								<?php	} ?>
								
								<!-- 다중선택 -->
								<?php if ($val['type'] == 'checkbox') { 
										$tmpIdx = -1;	?>
									<div class="detailSearchElement">
										<p class="detailElementTit"><?=$val['name']?></p>
										<?php
											$detailArr = [];
											$value = array(':idx' => $idx);
											$query = "SELECT info_val FROM mt_db_cs_info_detail WHERE use_yn = 'Y' AND info_idx = :idx ORDER BY sort ASC";
											$detail_info = list_pdo($query, $value);
		
											while($row = $detail_info->fetch(PDO::FETCH_ASSOC)){
												$thisdatas = [];
												$thisdatas['info_val'] = $row['info_val'];
												array_push($detailArr, $thisdatas);
											}	?>
		
										<button type="button" class="dropdown-btn detailSearchSelect detailMultiple">옵션 선택</button>
										<div class="multiple-dropdown">
											<input type="hidden" name="<?=$val['code']?>" class="multi_etc" value="<?=$checkedValues?>">
											<?php 
												$multi = explode('@', $_GET[$idx_num]);
	
												foreach($detailArr as $r) { 
													$tmpIdx++; ?>
													<div>
														<input type="checkbox" class="item_box" id="check3_<?=$tmpIdx?>" value="<?=$r['info_val']?>" <?=in_array($r['info_val'], $multi) ? 'checked' : '' ?>>
														<label class="checkBox" for="check3_<?=$tmpIdx?>">
															<i class="fas fa-check-square on"></i>
															<i class="far fa-square off"></i>
														</label>
														<label for="check3_<?=$tmpIdx?>"><?=$r['info_val']?></label>
													</div>
											<?php }	?>
										</div>
									</div>
								<?php	} ?>
							<?php }
							}
						?>
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

			$(".returnBtn").click(function(){
				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("복원할 DB를 선택해주시길 바랍니다.");
					return false;
				}
				
				if(confirm("해당 데이터를 복원하시겠습니까?")){
					loading(function(){
						$.ajax({
							url : "/ajax/db/trashUP",
							type : "POST",
							success : function(result){
								window.location.reload();
							}
						})
					});
				}
			});

			$(".foreverDeleteBtn").click(function(){
				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("영구삭제할 DB를 선택해주시길 바랍니다.");
					return false;
				}
				
				if(confirm("해당 데이터를 영구삭제하시겠습니까? 되돌릴 수 없습니다.")){
					loading(function(){
						$.ajax({
							url : "/ajax/db/trashDP",
							type : "POST",
							success : function(result){
								alert("삭제가 완료되었습니다.")
								window.location.reload();
							}
						})
					});
				}
			});

		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>