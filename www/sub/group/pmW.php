<?php

	# 메뉴설정
	$secMenu = "pm";
	$trdMenu = "pm";
	
	# 콘텐츠설정
	$contentsTitle = "생산업체 등록";
	$contentsInfo = "DB를 공급할 생산업체를 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "생산업체관리");
	array_push($contentsRoots, "등록");



	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";


	$value_depth1 = array(
		':category_depth' => '01'
	);
	$query_depth1 = "
		SELECT category_name, category_code  FROM mc_member_cmpy_category WHERE category_depth = :category_depth
	";
	$depth1 = list_pdo($query_depth1, $value_depth1);

	$value_depth2 = array(
		':category_depth' => '02'
	);
	$query_depth2 = "
		SELECT category_name, category_code  FROM mc_member_cmpy_category WHERE category_depth = :category_depth
	";
	$depth2 = list_pdo($query_depth2, $value_depth2);

	$value_depth3 = array(
		':category_depth' => '03'
	);
	$query_depth3 = "
		SELECT category_name, category_code  FROM mc_member_cmpy_category WHERE category_depth = :category_depth
	";
	$depth3 = list_pdo($query_depth3, $value_depth3);

	$value_depth4 = array(
		':category_depth' => '04'
	);
	$query_depth4 = "
		SELECT category_name, category_code  FROM mc_member_cmpy_category WHERE category_depth = :category_depth
	";
	$depth4 = list_pdo($query_depth4, $value_depth4);

	$value_depth5 = array(
		':category_depth' => '05'
	);
	$query_depth5 = "
		SELECT category_name, category_code  FROM mc_member_cmpy_category WHERE category_depth = :category_depth
	";
	$depth5 = list_pdo($query_depth5, $value_depth5);

?>
	
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/group/pmWP" data-callback="/sub/group/pmL" data-type="등록">
			<div class="tit"><i class="fas fa-user-circle"></i>계정정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th>분배 후 DB 미노출 여부</th>
						<td>
							<input type="checkbox" class="toggle" name="hidden_yn" id="hidden_yn" <?=($view['hidden_yn'] == "") ? "checked" : ""?>>
							<label class="toggle" for="hidden_yn"><div></div></label>
						</td>
					</tr>
					<tr>
						<th class="important">아이디</th>
						<td><input type="text" name="m_id" class="txtBox" usernameonly></td>
					</tr>
					<tr>
						<th class="important">비밀번호</th>
						<td>
							<input type="password" name="m_pw" class="txtBox">
							<div style="width: 100%; float: left; text-align: left; margin-top: 10px; font-size: 12px;">
							</div>
						</td>
					</tr>
					<tr>
					<tr>
						<th class="important">이름</th>
						<td><input type="text" name="m_name" class="txtBox"></td>
					</tr>
					<tr>
						<th>연락처</th>
						<td class="lp05"><input type="text" name="m_tel" class="txtBox" numonly></td>
					</tr>
					<tr>
						<th>이메일</th>
						<td class="lp05"><input type="text" name="m_mail" class="txtBox"></td>
					</tr>
					<tr>
						<th>주소</th>
						<td><input type="text" name="m_addr" class="txtBox"></td>
					</tr>
				</tbody>
			</table>

			<div class="tit">카테고리정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th>카테고리 1</th>
						<td>
							<select class="txtBox" name = "depth1">
								<option value="">선택하세요</option>
								<?php while($row_depth1 = $depth1->fetch(PDO::FETCH_ASSOC)) {  ?>
									<option value="<?=$row_depth1['category_code']?>"><?=$row_depth1['category_name']?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th>카테고리 2</th>
						<td>
							<select class="txtBox" name = "depth2">
								<option value="">선택하세요</option>
								<?php while($row_depth2 = $depth2->fetch(PDO::FETCH_ASSOC)) {  ?>
									<option value="<?=$row_depth2['category_code']?>"><?=$row_depth2['category_name']?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th>카테고리 3</th>
						<td>
							<select class="txtBox" name = "depth3">
								<option value="">선택하세요</option>
								<?php while($row_depth3 = $depth3->fetch(PDO::FETCH_ASSOC)) {  ?>
									<option value="<?=$row_depth3['category_code']?>"><?=$row_depth3['category_name']?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th>카테고리 4</th>
						<td>
							<select class="txtBox" name = "depth4">
								<option value="">선택하세요</option>
								<?php while($row_depth4 = $depth4->fetch(PDO::FETCH_ASSOC)) {  ?>
									<option value="<?=$row_depth4['category_code']?>"><?=$row_depth4['category_name']?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th>카테고리 5</th>
						<td>
							<select class="txtBox" name = "depth5">
								<option value="">선택하세요</option>
								<?php while($row_depth5 = $depth5->fetch(PDO::FETCH_ASSOC)) {  ?>
									<option value="<?=$row_depth5['category_code']?>"><?=$row_depth5['category_name']?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		
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
						<td><input type="text" class="txtBox" name="company_name"></td>
						<th>사업자번호</th>
						<td><input type="text" class="txtBox" name="company_num" numonly></td>
					</tr>
					<tr>
						<th>사업장 연락처</th>
						<td><input type="text" class="txtBox" name="company_tel" numonly></td>
						<th>사업장 팩스</th>
						<td><input type="text" class="txtBox" name="company_fax" numonly></td>
					</tr>
					<tr>
						<th>사업장 이메일</th>
						<td colspan="3"><input type="text" class="txtBox" name="company_mail"></td>
					</tr>
					<tr>
						<th>업태</th>
						<td><input type="text" class="txtBox" name="company_type"></td>
						<th>업종</th>
						<td><input type="text" class="txtBox" name="company_sec"></td>
					</tr>
					<tr>
						<th>사업장 주소</th>
						<td colspan="3"><input type="text" class="txtBox" name="company_addr"></td>
					</tr>
					<tr>
						<th>세금계산서처리용 이메일</th>
						<td colspan="3"><input type="text" class="txtBox" name="tax_mail"></td>
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
						<th>대표자명</th>
						<td colspan="3"><input type="text" class="txtBox" name="ceo_name"></td>
					</tr>
					<tr>
						<th>대표자 연락처</th>
						<td><input type="text" class="txtBox" name="ceo_tel" numonly></td>
						<th>대표자 이메일</th>
						<td><input type="text" class="txtBox" name="ceo_mail"></td>
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
									$selected = ($row['bank_code'] == $view['bank_code']) ? "selected" : "";
							?>
								<option value="<?=$row['bank_code']?>" <?=$selected?>><?=$row['bank_name']?></option>
							<?php } ?>
							</select>
						</td>
						<th>예금주명</th>
						<td><input type="text" class="txtBox" name="bank_holder"></td>
					</tr>
					<tr>
						<th>계좌번호</th>
						<td colspan="3"><input type="text" class="txtBox" name="bank_num" numonly></td>
					</tr>
				</tbody>
			</table>

			<div class="tit">상세 정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th>비고</th>
						<td colspan="3"><textarea class="txtBox" name="memo"></textarea></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="<?=$_SESSION['prevURL']?>" class="typeBtn btnGray02" title="취소"><i class="fas fa-arrow-left"></i>취소</a> 
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnBlack submitBtn" data-target="write"><i class="far fa-check-circle"></i>완료</button>
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>