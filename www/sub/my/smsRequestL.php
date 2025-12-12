<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 메뉴설정
	$secMenu = "sms";
	$trdMenu = "request";
	
	# 콘텐츠설정
	$contentsTitle = "발신번호요청";
	$contentsInfo = "SMS 전송 시 사용할 발신번호를 개발사(미다웍스)에 요청하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "SMS설정");
	array_push($contentsRoots, "발신번호요청");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND use_yn = 'Y'";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_sms_request");

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
			<button type="button" class="typeBtn btnMain big popupBtn" data-type="open" data-target="write" data-url="/sub/my/smsRequestW" data-name="발신번호요청"><i class="fas fa-paper-plane"></i>발신번호요청</button>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="15%">
				<col width="15%">
				<col width="30%">
				<col width="12%">
				<col width="12%">
				<col width="12%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">NO</th>
					<th colspan="3">요청정보</th>
					<th colspan="2">요청결과</th>
					<th rowspan="2">요청일시</th>
				</tr>
				<tr>
					<th>이름</th>
					<th>연락처</th>
					<th>통신가입증명원</th>
					<th>완료여부</th>
					<th style="border-right: 1px solid #FFF;">완료일시</th>
				</tr>
			</thead>
			<tbody>
			<?php	
				// $sql = list_sql("
				// 	SELECT MT.*
				// 	FROM mt_sms_request MT
				// 	{$andQuery}
				// 	{$orderQuery}
				// 	{$limitQuery}
				// ");

				$value = array(''=>'');
				$query = "
					SELECT MT.*
					FROM mt_sms_request MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";

				$sql = list_pdo($query, $value);

				
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=dhtml($row['sent_name'])?></td>
					<td class="lp05"><?=$row['sent_tel']?></td>
					<td class="lp05 tl">
						<a href="/sub/my/smsRequestDown?idx=<?=$row["idx"]?>">
							<i class="fas fa-download" style="margin-right: 5px;"></i><?=$row["filename_r"]?>
						</a>
					</td>
					<td class="lp05" style="font-weight: bold; color: #<?=($row["finish_yn"] == "Y") ? "DC3333" : "CCC"?>;">
						<?=($row["finish_yn"] == "Y") ? "완료" : "대기"?>
					</td>
					<td class="lp05"><?=($row["finish_date"]) ? $row["finish_date"] : "-"?></td>
					<td class="lp05"><?=$row["reg_date"]?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="7">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnMain big popupBtn" data-type="open" data-target="write" data-url="/sub/my/smsRequestW" data-name="발신번호요청"><i class="fas fa-paper-plane"></i>발신번호요청</button>
		</div>
		<div class="right">
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>