<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	# 받은 쪽지함
	
	$value = array(':reg_idx'=>$user['idx']);
	$query = "
		SELECT MT.*
			, ( SELECT m_name FROM mt_member WHERE idx = MT.receive_idx ) AS receiveName
			, ( SELECT m_id FROM mt_member WHERE idx = MT.receive_idx ) AS receiveID
		FROM mt_note MT
		WHERE use_yn = 'Y'
		AND reg_idx = :reg_idx
		ORDER BY idx DESC
	";
	$sql = list_pdo($query, $value);

?>
<ul class="sendListWrap">
<?php while($row = $sql->fetch(PDO::FETCH_ASSOC)){ ?>
		<li data-idx="<?=$row['idx']?>">
			<p class="con"><?=nl2br($row['contents'])?></p>
			<span class="userInfo"><i class="fas fa-sign-out-alt"></i><?=dhtml($row['receiveName'])?>(<?=$row['receiveID']?>)</span>
			<span class="date"><?=$row['reg_date']?></span>
		<?php if(!$row['view_date']){ ?>
			<span class="viewDate before"><i class="fas fa-eye"></i>안 읽음</span>
		<?php } else { ?>
			<span class="viewDate"><i class="fas fa-eye"></i><?=$row['view_date']?></span>
		<?php } ?>
		</li>
<?php } ?>
</ul>