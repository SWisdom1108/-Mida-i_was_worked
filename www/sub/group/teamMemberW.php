<?php

	# 메뉴설정
	$secMenu = "team";
	$trdMenu = "teamMember";
	
	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";
	
	# 콘텐츠설정
	$contentsTitle = "{$customLabel["fc"]} 등록";
	$contentsInfo = "{$customLabel["fc"]}(를)을 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "사용자관리");
	array_push($contentsRoots, "{$customLabel["fc"]}관리");
	array_push($contentsRoots, "등록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

?>
	
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/group/teamMemberWP" data-callback="/sub/group/teamMemberL" data-type="등록">
			<div class="tit"><?=$customLabel["tm"]?> 정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important"><?=$customLabel["tm"]?>명</th>
						<td class="tl">
							<input type="hidden" name="team_code" id="team_code">
							<span style="color: #CCC; float: left; height: 35px; line-height: 35px;" id="selectTeamName"><?=$customLabel["tm"]?>(를)을 선택해주시길 바랍니다.</span>
							<button type="button" class="typeBtn btnOrange" onclick="popupControl('open', 'search', '/sub/group/teamSearchL', '<?=$customLabel["tm"]?>검색');" style="float: right;">
								<i class="fas fa-search"></i><?=$customLabel["tm"]?>검색
							</button>
						</td>
						<th>담당관리자 여부<div class="miniGuideWrap" data-class="groupTeamMG"></div></th>
						<td>
							<input type="checkbox" class="toggle" name="mg_yn" id="mg_yn">
							<label class="toggle" for="mg_yn"><div></div></label>
						</td>
					</tr>
				</tbody>
			</table>

			<div class="tit">계정정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important">아이디</th>
						<td><input type="text" name="m_id" class="txtBox" usernameonly></td>
					</tr>
					<tr>
						<th class="important">비밀번호</th>
						<td>
							<input type="password" name="m_pw" class="txtBox">
						</td>
					</tr>
					<tr>
					<tr>
						<th class="important">이름</th>
						<td><input type="text" name="m_name" class="txtBox"></td>
					</tr>
					<tr>
						<th class="important">연락처</th>
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
					<tr>
						<th>사용여부</th>
						<td>
							<input type="checkbox" class="toggle" name="use_yn" id="use_yn" checked>
							<label class="toggle" for="use_yn"><div></div></label>
						</td>
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