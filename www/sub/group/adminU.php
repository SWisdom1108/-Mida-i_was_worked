<?php

	# 메뉴설정
	$secMenu = "admin";

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001"];
	
	# 콘텐츠설정
	$contentsTitle = "관리자 수정";
	$contentsInfo = "내부시스템 전체관리자를 설정하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "관리자설정");
	array_push($contentsRoots, "등록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND use_yn = 'Y' AND auth_code = '002'";

	# 데이터 추출
	$value = array(':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
		FROM mt_member MT
		{$andQuery}
		AND idx = :idx
	";
	$view = view_pdo($query, $value);

	if(!$view){
		return false;
	}

	// print_r($view);

?>
	
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/group/adminUP" data-callback="/sub/group/adminV?idx=<?=$view['idx']?>" data-type="수정">
			<input type="hidden" name="idx" value="<?=$view['idx']?>">
			<div class="tit">기본정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th>아이디</th>
						<td class="lp05 tl"><?=$view['m_id']?></td>
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
				</tbody>
			</table>
		</form>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="/sub/group/adminV?idx=<?=$view['idx']?>" class="typeBtn btnGray02" title="취소"><i class="fas fa-arrow-left"></i>취소</a> 
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnBlack submitBtn" data-target="write"><i class="far fa-check-circle"></i>완료</button>
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>