<?php

	# 콘텐츠설정
	$pageTitle = "나의정보";

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/header.php";

?>
	
	<div class="dataSectionWrap" style="padding: 20px;">
	
		<div class="viewWrap">
			<div class="tit">기본정보</div>
			<table>
				<colgroup>
					<col width="30%">
					<col width="70%">
				</colgroup>
				<tbody>
					<tr>
						<th>아이디</th>
						<td class="lp05"><?=$user['m_id']?></td>
					</tr>
					<tr>
						<th>이름</th>
						<td><?=$user['m_name']?></td>
					</tr>
					<tr>
						<th>연락처</th>
						<td class="lp05"><?=$user['m_tel']?></td>
					</tr>
					<tr>
						<th>이메일</th>
						<td class="lp05"><?=dhtml($user['m_mail'])?></td>
					</tr>
					<tr>
						<th>주소</th>
						<td><?=dhtml($user['m_addr'])?></td>
					</tr>
				</tbody>
			</table>
		</div>
		
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/footer.php"; ?>