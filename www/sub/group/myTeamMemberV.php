<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["004"];

	# 메뉴설정
	$secMenu = "myTeamMember";
	
	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";
	
	# 콘텐츠설정
	$contentsTitle = "{$customLabel["fc"]} 정보";
	$contentsInfo = "{$customLabel["fc"]}(를)을 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "{$customLabel["fc"]}관리");
	array_push($contentsRoots, "정보");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 데이터 추출
	$value = array(':tm_code'=>$user['tm_code'], ':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
		FROM mt_member MT
		WHERE auth_code IN ( 005 )
		AND tm_code = :tm_code
		AND idx = :idx
	";
	$view = view_pdo($query, $value);

	if(!$view){
		include_once "{$_SERVER['DOCUMENT_ROOT']}/sub/error/index.php";
		return false;
	}

?>
	
	<div class="viewWrap">
		<div class="tit">계정정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="80%">
			</colgroup>
			<tbody>
				<tr>
					<th>아이디</th>
					<td class="lp05"><?=$view['m_id']?></td>
				</tr>
				<tr>
					<th>이름</th>
					<td><?=dhtml($view['m_name'])?></td>
				</tr>
				<tr>
					<th>연락처</th>
					<td class="lp05"><?=$view['m_tel']?></td>
				</tr>
				<tr>
					<th>이메일</th>
					<td class="lp05"><?=dhtml($view['m_mail'])?></td>
				</tr>
				<tr>
					<th>주소</th>
					<td><?=dhtml($view['m_addr'])?></td>
				</tr>
				<tr>
					<th>사용여부</th>
					<td><?=($view['use_yn'] == "Y") ? "사용중" : "<span style='color: #CCC;'>사용안함</span>"?></td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="<?=$_SESSION['prevURL']?>" class="typeBtn btnGray02" title="이전"><i class="fas fa-arrow-left"></i>이전</a> 
		</div>
		<div class="right">
			<a href="/sub/group/myTeamMemberU?idx=<?=$view['idx']?>" class="typeBtn btnMain" title="수정"><i class="fas fa-edit"></i>수정</a> 
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>