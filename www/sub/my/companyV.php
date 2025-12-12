<?php

	# 메뉴설정
	$secMenu = "company";
	
	# 콘텐츠설정
	$contentsTitle = "회사 정보";
	$contentsInfo = "나의 회사정보를 확인 및 수정하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "회사정보");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

?>
	
	<div class="viewWrap">
		<div class="tit">회사 정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="30%">
				<col width="20%">
				<col width="30%">
			</colgroup>
			<tbody>
				<tr>
					<th>사업자명</th>
					<td><?=dhtml($cmpy['company_name'])?></td>
					<th>사업자번호</th>
					<td class="lp05"><?=$cmpy['company_num']?></td>
				</tr>
				<tr>
					<th>사업장 연락처</th>
					<td class="lp05"><?=$cmpy['company_tel']?></td>
					<th>사업장 팩스</th>
					<td class="lp05"><?=$cmpy['company_fax']?></td>
				</tr>
				<tr>
					<th>사업장 이메일</th>
					<td class="lp05" colspan="3"><?=dhtml($cmpy['company_mail'])?></td>
				</tr>
				<tr>
					<th>업태</th>
					<td><?=dhtml($cmpy['company_type'])?></td>
					<th>업종</th>
					<td><?=dhtml($cmpy['company_sec'])?></td>
				</tr>
				<tr>
					<th>사업장 주소</th>
					<td colspan="3"><?=dhtml($cmpy['company_addr'])?></td>
				</tr>
				<tr>
					<th>세금계산서처리용 이메일</th>
					<td colspan="3" class="lp05"><?=dhtml($cmpy['tax_mail'])?></td>
				</tr>
			</tbody>
		</table>
		
		<div class="tit">상세 정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="80%">
			</colgroup>
			<tbody>
				<tr>
					<th>비고</th>
					<td><?=dhtmlBf($cmpy['memo'])?></td>
				</tr>
			</tbody>
		</table>
		
		<div class="tit">대표자 정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="30%">
				<col width="20%">
				<col width="30%">
			</colgroup>
			<tbody>
				<tr>
					<th>대표자</th>
					<td colspan="3"><?=dhtml($cmpy['ceo_name'])?></td>
				</tr>
				<tr>
					<th>대표자 연락처</th>
					<td class="lp05"><?=dhtml($cmpy['ceo_tel'])?></td>
					<th>대표자 이메일</th>
					<td class="lp05"><?=dhtml($cmpy['ceo_mail'])?></td>
				</tr>
			</tbody>
		</table>
		
		<div class="tit">계좌 정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="30%">
				<col width="20%">
				<col width="30%">
			</colgroup>
			<tbody>
				<tr>
					<th>은행명</th>
					<td><?=dhtml($cmpy['bank_name'])?></td>
					<th>예금주명</th>
					<td><?=dhtml($cmpy['bank_holder'])?></td>
				</tr>
				<tr>
					<th>계좌번호</th>
					<td class="lp05" colspan="3"><?=dhtml($cmpy['bank_num'])?></td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		</div>
		<div class="right">
		<?php if($cmpy['m_idx'] == $user['idx']){ ?>
			<a href="/sub/my/companyU" class="typeBtn btnMain" title="수정"><i class="fas fa-edit"></i>수정</a> 
		<?php } ?>
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>