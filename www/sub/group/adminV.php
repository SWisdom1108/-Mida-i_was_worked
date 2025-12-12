<?php

	# 메뉴설정
	$secMenu = "admin";

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001"];
	
	# 콘텐츠설정
	$contentsTitle = "관리자 정보";
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

?>
	
	<div class="viewWrap">
		<div class="tit">기본정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="80%">
			</colgroup>
			<tbody>
				<tr>
					<th>권한</th>
					<td colspan="3">관리자</td>
				</tr>
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
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="<?=$_SESSION['prevURL']?>" class="typeBtn btnGray02" title="이전"><i class="fas fa-arrow-left"></i>이전</a> 
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnRed deleteBtn2" title="삭제"><i class="fas fa-trash-alt"></i>삭제</button>
			<a href="/sub/group/adminU?idx=<?=$view['idx']?>" class="typeBtn btnMain" title="수정"><i class="fas fa-edit"></i>수정</a> 
		</div>
	</div>
<script>
$(".deleteBtn2").click(function(event) {

	if ( confirm("정말 관리자를 삭제하시겠습니까?\n[*관리자가 완전삭제되니 신중히 선택해주세요*]") == true ){
		$.post('/ajax/group/memberDel.php', { idx : "<?=$_GET['idx']?>" }, function(data, textStatus, xhr) {
			switch(data){
				case "fail_cnt":
					alert("현재 보유중인 DB가존재합니다. 관리자 변경후 삭제부탁드립니다.");
					break;
				case "success":
					alert("관리자 삭제에 성공하였습니다.");
					window.location.href = "<?=$_SESSION['prevURL']?>";
					break;
				case "fail":
					alert("알수없는 이유로 실패했습니다.");
					break;
			}
		});
	}
});
</script>
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>