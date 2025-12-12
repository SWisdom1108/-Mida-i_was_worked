<?php

	# 메뉴설정
	$secMenu = "pm";
	$trdMenu = "pm";
	
	# 콘텐츠설정
	$contentsTitle = "생산업체";
	$contentsInfo = "DB를 공급할 생산업체를 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "생산업체관리");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND auth_code = '003'";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_member_cmpy");

?>
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
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
		</div>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="/sub/group/pmW" class="typeBtn btnMain big"><i class="fas fa-plus-circle"></i>생산업체등록</a>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="8%">
				<col width="10%">
				<col width="11%">
				<col width="11%">
				<col width="11%">
				<col width="11%">
				<col width="11%">
				<col width="11%">
				<col width="12%">
				<col width="12%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">NO</th>
					<th rowspan="2">업체코드</th>
					<th colspan="3">사업자 정보</th>
					<th colspan="2">대표자 정보</th>
					<th colspan="2">계정 정보</th>
					<th rowspan="2">사용여부</th>
					<th rowspan="2">등록일시</th>
				</tr>
				<tr>
					<th>사업자명</th>
					<th>사업자번호</th>
					<th>연락처</th>
					<th>대표자명</th>
					<th>연락처</th>
					<th>아이디</th>
					<th style="border-right: 1px solid #FFF;">이름</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						 , ( SELECT m_id FROM mt_member WHERE idx = MT.m_idx ) AS m_id
						 , ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS m_name
					FROM mt_member_cmpy MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr class="rowMove" onclick="www('/sub/group/pmV?idx=<?=$row['idx']?>');">
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05">PM<?=$row['idx']?></td>
					<td><?=dhtml($row['company_name'])?></td>
					<td class="lp05"><?=($row['company_num']) ? dhtml($row['company_num']) : "-"?></td>
					<td class="lp05"><?=($row['company_tel']) ? dhtml($row['company_tel']) : "-"?></td>
					<td><?=($row['ceo_name']) ? dhtml($row['ceo_name']) : "-"?></td>
					<td class="lp05"><?=($row['ceo_tel']) ? dhtml($row['ceo_tel']) : "-"?></td>
					<td class="lp05"><?=($row['m_id']) ? dhtml($row['m_id']) : "-"?></td>
					<td class="lp05"><?=($row['m_name']) ? dhtml($row['m_name']) : "-"?></td>
					<td class="stopProgram">
						<input type="checkbox" class="toggle2 changeUseYn" value="<?=$row['idx']?>" data-mIdx="<?=$row['m_idx']?>" id="use_yn<?=$row['idx']?>" <?=($row['use_yn'] == "Y") ? "checked" : "";?>>
						<label class="toggle2" for="use_yn<?=$row['idx']?>"><div></div></label>
					</td>
					<td class="lp05"><?=date("Y-m-d", strtotime($row['reg_date']))?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="19" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="/sub/group/pmW" class="typeBtn btnMain big"><i class="fas fa-plus-circle"></i>생산업체등록</a>
		</div>
		<div class="right">
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<!-- 데이터 검색 -->
	<div class="simpleSearchWrap">
		<form method="get">
			<select class="txtBox" name="label">
				<option value="company_name">사업자명</option>
				<option value="company_num">사업자번호</option>
				<option value="company_tel">사업자연락처</option>
				<option value="ceo_name">대표자명</option>
				<option value="ceo_tel">대표자연락처</option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit" class="typeBtn">검색</button>
		</form>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>