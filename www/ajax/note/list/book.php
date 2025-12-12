<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	# 주소록
	$value = array(':reg_idx'=>$user['idx']);
	$query = "
		SELECT MT.*
			, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS m_name
			, ( SELECT m_id FROM mt_member WHERE idx = MT.m_idx ) AS m_id
		FROM mt_note_book MT
		WHERE use_yn = 'Y'
		AND reg_idx = :reg_idx
		ORDER BY idx DESC
	";
	$sql = list_pdo($query, $value);

?>
<ul class="bookListWrap">
	<?php if($user['auth_code'] == "001" || $user['auth_code'] == "002"){ ?>
		<li class="addGroupBtn" data-type="PM">
			<i class="fas fa-cloud-download-alt"></i>생산업체 불러오기
		</li>
		<li class="addGroupBtn" data-type="FC">
			<i class="fas fa-cloud-download-alt"></i>담당자 불러오기
		</li>
	<?php } ?>
	
	<?php if($user['auth_code'] == "004" || $user['auth_code'] == "005"){ ?>
		<li class="addGroupBtn" data-type="FC">
			<i class="fas fa-cloud-download-alt"></i>팀원 불러오기
		</li>
	<?php } ?>
	<li id="addBookFrm">
		<form>
			<input type="text" class="txtBox" name="userID" id="userID" placeholder="회원 아이디">
			<input type="text" class="txtBox" name="userMemo" id="userMemo" placeholder="메모 입력...">
			<button type="button" class="typeBtn" id="bookAddBtn">추가</button>
		</form>
	</li>
	<?php while($row = $sql->fetch(PDO::FETCH_ASSOC)){ ?>
		<li data-idx="<?=$row['idx']?>" class="bookItem_<?=$row['idx']?>">
			<input type="text" class="txtBox" id="bookMemo_<?=$row['idx']?>" value="<?=dhtml($row['memo'])?>">
			<span class="memo"><?=dhtml($row['memo'])?></span>
			<span class="user"><?=dhtml($row['m_name'])?>(<?=$row['m_id']?>)</span>
			<div class="buttonList mod" style="display: none;">
				<i class="fas fa-check bookModOkBtn" data-idx="<?=$row['idx']?>"></i>
			</div>
			<div class="buttonList basic">
				<i class="fas fa-envelope bookSendBtn" title="보내기" data-id="<?=$row['m_id']?>"></i>
				<i class="fas fa-pencil-alt bookModBtn" title="수정"></i>
				<i class="fas fa-trash-alt bookDeleteBtn" data-idx="<?=$row['idx']?>" title="삭제"></i>
			</div>
		</li>
	<?php } ?>
</ul>