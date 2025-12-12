<?php

	# 메뉴설정
	$secMenu = "my";
	
	# 콘텐츠설정
	$contentsTitle = "나의 정보";
	$contentsInfo = "나의 기본정보를 수정하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "나의정보");
	array_push($contentsRoots, "수정");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

?>
	
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="modFrm" data-ajax="/ajax/my/myUP" data-callback="/sub/my/myV" data-type="수정">
			<div class="tit">기본정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th>아이디</th>
						<td class="lp05 tl"><?=dhtml($user['m_id'])?></td>
					</tr>
					<tr>
						<th>비밀번호</th>
						<td>
							<input type="password" name="m_pw" class="txtBox">
							<div style="width: 100%; float: left; text-align: left; margin-top: 10px; font-size: 12px;">
								<span>* 변경을 원하실 경우에만 입력해주시길 바랍니다.</span>
							</div>
						</td>
						</tr>
						<tr>
							<th>비밀번호 확인</th>
							<td><input type="password" name="m_pw_check" class="txtBox"></td>
						</tr>
					<tr>
						<th class="important">이름</th>
						<td><input type="text" name="m_name" class="txtBox" value="<?=dhtml($user['m_name'])?>" maxlength="20"></td>
					</tr>
					<tr>
						<th>연락처</th>
						<td class="lp05"><input type="text" name="m_tel" class="txtBox" value="<?=$user['m_tel']?>" numonly></td>
					</tr>
					<tr>
						<th>이메일</th>
						<td class="lp05"><input type="text" name="m_mail" class="txtBox" value="<?=dhtml($user['m_mail'])?>"></td>
					</tr>
					<tr>
						<th>주소</th>
						<td class="lp05"><input type="text" name="m_addr" class="txtBox" value="<?=dhtml($user['m_addr'])?>"></td>
					</tr>
					<tr>
						<th>보안카드 사용여부</th>
						<td>
							<input type="checkbox" class="toggle" name="snum_use_yn" id="snum_use_yn" <?=($user['snum_use_yn'] == "Y") ? "checked" : ""?>>
							<label class="toggle" for="snum_use_yn"><div></div></label>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="/sub/my/myV" class="typeBtn btnGray02" title="취소"><i class="fas fa-arrow-left"></i>취소</a> 
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnBlack submitBtn" data-target="mod"><i class="far fa-check-circle"></i>완료</button>
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>