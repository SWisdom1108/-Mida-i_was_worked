<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 메뉴설정
	$secMenu = "sendTel";
	
	# 콘텐츠설정
	$contentsTitle = "발신번호 조회";
	$contentsInfo = "SMS 전송 시 사용할 발신번호를 조회하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "발신번호조회");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_sms_tel");

?>

	<!-- 데이터 검색영역 -->
	<div class="searchWrap">
		<form method="get">
			<ul class="formWrap">
				<li>
					<span class="label">상세검색</span>
					<select class="txtBox" name="label">
						<option value="sent_name">이름</option>
						<option value="sent_tel">연락처</option>
					</select>
					<input type="text" class="txtBox value" name="value" value="<?=$_GET['value']?>">
				</li>
				<li class="drag">
					<span class="label">조회기간</span>
					<input type="hidden" name="setDate" value="reg">
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
	</div>
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="order_by_date DESC">기본정렬</option>
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
		</div>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="30%">
				<col width="30%">
				<col width="14%">
				<col width="10%">
				<col width="12%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">NO</th>
					<th colspan="2">발신번호 정보</th>
					<th rowspan="2">메인번호여부</th>
					<th rowspan="2">사용여부</th>
					<th rowspan="2">등록일시</th>
				</tr>
				<tr>
					<th>이름</th>
					<th style="border-right: 1px solid #FFF;">연락처</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
					FROM mt_sms_tel MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr class="rowMove popupBtn" data-type="open" data-target="write" data-url="/sub/sms/sendTelU?idx=<?=$row["idx"]?>" data-name="발신번호 <?=$row["sent_name"]?>(<?=$row["sent_tel"]?>) 수정">
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=$row['sent_name']?></td>
					<td class="lp05"><?=$row['sent_tel']?></td>
					<td class="lp05" style="font-weight: bold; color: #<?=($row["main_yn"] == "Y") ? "DC3333" : "CCC"?>;">
						<?=($row["main_yn"] == "Y") ? "사용중" : "미사용"?>
					</td>
					<td class="lp05" style="font-weight: bold; color: #<?=($row["use_yn"] == "Y") ? "333" : "CCC"?>;">
						<?=($row["use_yn"] == "Y") ? "사용중" : "미사용"?>
					</td>
					<td class="lp05"><?=$row["reg_date"]?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="6">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		</div>
		<div class="right">
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>