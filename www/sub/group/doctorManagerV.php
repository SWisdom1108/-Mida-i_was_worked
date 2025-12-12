<?php

	# 메뉴설정
	$secMenu = "team";
	$trdMenu = "doctorManager";
	
	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";
	
	# 콘텐츠설정
	$contentsTitle = "실장 정보";
	$contentsInfo = "실장(를)을 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "사용자관리");
	array_push($contentsRoots, "실장관리");
	array_push($contentsRoots, "정보");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 데이터 추출
	$value = array(':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
		FROM mt_member MT
		WHERE auth_code = 006
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
					<th>코드</th>
					<td class="lp05">DM<?=$view['idx']?></td>
				</tr>
				<tr>
					<th>아이디</th>
					<td class="lp05"><?=dhtml($view['m_id'])?></td>
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
			<button type="button" class="typeBtn btnRed deleteBtn2" title="삭제"><i class="fas fa-trash-alt"></i>삭제</button>
			<a href="/sub/group/doctorManagerU?idx=<?=$view['idx']?>" class="typeBtn btnMain" title="수정"><i class="fas fa-edit"></i>수정</a> 
		</div>
	</div>
<script>
$(".deleteBtn2").click(function(event) {

	if ( confirm("정말 담당자를 삭제하시겠습니까?\n[*담당자가 완전삭제되니 신중히 선택해주세요*]") == true ){
		$.post('/ajax/group/memberDel.php', { idx : "<?=$_GET['idx']?>" }, function(data, textStatus, xhr) {
			switch(data){
				case "fail_cnt":
					alert("현재 보유중인 DB가존재합니다. 담당자 변경후 삭제부탁드립니다. 보유한 DB가 없을 경우 휴지통을 확인해주세요.");
					break;
				case "success":
					alert("담당자 삭제에 성공하였습니다.");
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