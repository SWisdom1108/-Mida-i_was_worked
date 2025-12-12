<?php

	# 메뉴설정
	$secMenu = "team";
	$trdMenu = "doctor";
	
	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";
	
	# 콘텐츠설정
	$contentsTitle = "닥터 수정";
	$contentsInfo = "닥터(를)을 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "사용자관리");
	array_push($contentsRoots, "닥터관리");
	array_push($contentsRoots, "수정");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 데이터 추출
	$value = array(':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
		FROM mt_member MT
		WHERE auth_code = 007
		AND idx = :idx
	";
	$view = view_pdo($query, $value);

	if(!$view){
		include_once "{$_SERVER['DOCUMENT_ROOT']}/sub/error/index.php";
		return false;
	}

?>
	
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="modFrm" data-ajax="/ajax/group/doctorUP" data-callback="/sub/group/doctorV?idx=<?=$view['idx']?>" data-type="수정">
			<input type="hidden" name="idx" value="<?=$view['idx']?>">

			<div class="tit">계정정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th>아이디</th>
						<td><?=dhtml($view['m_id'])?></td>
					</tr>
					<tr>
						<th>비밀번호</th>
						<td>
							<input type="password" name="m_pw" class="txtBox">
							<div style="width: 100%; float: left; text-align: left; margin-top: 10px; font-size: 12px;">
								<span>* 변경을 원하실경우에만 입력해주시길 바랍니다.</span>
							</div>
						</td>
					</tr>
					<tr>
					<tr>
						<th class="important">이름</th>
						<td><input type="text" name="m_name" class="txtBox" value="<?=dhtml($view['m_name'])?>"></td>
					</tr>
					<tr>
						<th class="important">연락처</th>
						<td class="lp05"><input type="text" name="m_tel" class="txtBox" numonly value="<?=$view['m_tel']?>"></td>
					</tr>
					<tr>
						<th>이메일</th>
						<td class="lp05"><input type="text" name="m_mail" class="txtBox" value="<?=dhtml($view['m_mail'])?>"></td>
					</tr>
					<tr>
						<th>주소</th>
						<td><input type="text" name="m_addr" class="txtBox" value="<?=dhtml($view['m_addr'])?>"></td>
					</tr>
					<tr>
						<th>사용여부</th>
						<td>
							<input type="checkbox" class="toggle" name="use_yn" id="use_yn" <?=($view['use_yn'] == "Y") ? "checked" : ""?>>
							<label class="toggle" for="use_yn"><div></div></label>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="/sub/group/doctorV?idx=<?=$view['idx']?>" class="typeBtn btnGray02" title="취소"><i class="fas fa-arrow-left"></i>취소</a> 
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnBlack submitBtn" data-target="mod"><i class="far fa-check-circle"></i>완료</button>
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>