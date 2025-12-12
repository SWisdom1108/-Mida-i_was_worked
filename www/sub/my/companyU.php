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

	# 수정가능여부
	if($cmpy['m_idx'] != $user['idx']){
		www("/sub/error/");
	}

?>
	
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="modFrm" data-ajax="/ajax/my/companyUP" data-callback="/sub/my/companyV" data-type="수정">
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
						<th class="important">사업자명</th>
						<td><input type="text" class="txtBox" name="company_name" value="<?=dhtml($cmpy['company_name'])?>"></td>
						<th class="important">사업자번호</th>
						<td><input type="text" class="txtBox" name="company_num" value="<?=$cmpy['company_num']?>" numonly></td>
					</tr>
					<tr>
						<th class="important">사업장 연락처</th>
						<td><input type="text" class="txtBox" name="company_tel" value="<?=$cmpy['company_tel']?>" numonly></td>
						<th>사업장 팩스</th>
						<td><input type="text" class="txtBox" name="company_fax" value="<?=$cmpy['company_fax']?>" numonly></td>
					</tr>
					<tr>
						<th>사업장 이메일</th>
						<td colspan="3"><input type="text" class="txtBox" name="company_mail" value="<?=dhtml($cmpy['company_mail'])?>"></td>
					</tr>
					<tr>
						<th>업태</th>
						<td><input type="text" class="txtBox" name="company_type" value="<?=dhtml($cmpy['company_type'])?>"></td>
						<th>업종</th>
						<td><input type="text" class="txtBox" name="company_sec" value="<?=dhtml($cmpy['company_sec'])?>"></td>
					</tr>
					<tr>
						<th>사업장 주소</th>
						<td colspan="3"><input type="text" class="txtBox" name="company_addr" value="<?=dhtml($cmpy['company_addr'])?>"></td>
					</tr>
					<tr>
						<th>세금계산서처리용 이메일</th>
						<td colspan="3"><input type="text" class="txtBox" name="tax_mail" value="<?=dhtml($cmpy['tax_mail'])?>"></td>
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
						<td><textarea class="txtBox" name="memo"><?=$cmpy['memo']?></textarea></td>
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
						<th class="important">대표자</th>
						<td colspan="3"><input type="text" class="txtBox" name="ceo_name" value="<?=$cmpy['ceo_name']?>" maxlength="20"></td>
					</tr>
					<tr>
						<th class="important">대표자 연락처</th>
						<td><input type="text" class="txtBox" name="ceo_tel" value="<?=$cmpy['ceo_tel']?>" numonly></td>
						<th>대표자 이메일</th>
						<td><input type="text" class="txtBox" name="ceo_mail" value="<?=dhtml($cmpy['ceo_mail'])?>"></td>
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
						<td>
							<select class="txtBox" name="bank_code">
							<?php
								$value = array(''=>'');
								$query = "
									SELECT MT.*
									FROM mc_bank MT
									WHERE use_yn = 'Y'
									ORDER BY sort ASC
								";
								$sql = list_pdo($query, $value);
								while($row = $sql->fetch(PDO::FETCH_ASSOC)){
									$selected = ($row['bank_code'] == $cmpy['bank_code']) ? "selected" : "";
							?>
								<option value="<?=$row['bank_code']?>" <?=$selected?>><?=$row['bank_name']?></option>
							<?php } ?>
							</select>
						</td>
						<th>예금주명</th>
						<td><input type="text" class="txtBox" name="bank_holder" value="<?=$cmpy['bank_holder']?>" maxlength="20"></td>
					</tr>
					<tr>
						<th>계좌번호</th>
						<td colspan="3"><input type="text" class="txtBox" name="bank_num" value="<?=$cmpy['bank_num']?>" numonly></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="/sub/my/companyV" class="typeBtn btnGray02" title="취소"><i class="fas fa-arrow-left"></i>취소</a> 
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnBlack submitBtn" data-target="mod"><i class="far fa-check-circle"></i>완료</button>
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>