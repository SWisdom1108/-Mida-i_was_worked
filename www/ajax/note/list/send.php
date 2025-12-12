<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	# 받은 쪽지함
	
	$value = array(':receive_idx'=>$user['idx']);
	$query = "
		SELECT MT.*
			, ( SELECT m_name FROM mt_member WHERE idx = MT.reg_idx ) AS regName
			, ( SELECT m_id FROM mt_member WHERE idx = MT.reg_idx ) AS regID
		FROM mt_note MT
		WHERE use_yn = 'Y'
		AND receive_idx = :receive_idx
		ORDER BY idx DESC
	";
	$sql = list_pdo($query, $value);

?>
<ul class="sendListWrap">
<?php while($row = $sql->fetch(PDO::FETCH_ASSOC)){ ?>
		<li data-idx="<?=$row['idx']?>" id="myNoteItem_<?=$row['idx']?>" data-type="my" data-from-id="<?=$row['regID']?>">
			<p class="con"><?=nl2br($row['contents'])?></p>
			<span class="userInfo"><i class="fas fa-sign-in-alt"></i><?=dhtml($row['regName'])?>(<?=$row['regID']?>)</span>
			<span class="date"><?=$row['reg_date']?></span>
		<?php if(!$row['view_date']){ ?>
			<span class="viewDate before"><i class="fas fa-eye"></i>안 읽음</span>
		<?php } else { ?>
			<span class="viewDate"><i class="fas fa-eye"></i><?=$row['view_date']?></span>
		<?php } ?>
		</li>
<?php } ?>
</ul>