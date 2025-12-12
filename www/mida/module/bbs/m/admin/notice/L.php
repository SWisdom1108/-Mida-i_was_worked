<?php

	$sql = list_sql("
		SELECT MT.*
			, ( SELECT {$userNameColum} FROM {$userTable} WHERE idx = MT.reg_idx ) AS reg_name 
			, ( SELECT COUNT(*) FROM mt_bbs_comment WHERE bbs_idx = MT.idx AND use_yn = 'Y' ) AS commentCnt 
		FROM mt_bbs MT 
		{$andQuery} 
		ORDER BY idx DESC 
		{$limitSQL}");

	$sql2 = list_sql("
		SELECT MT.*
			, ( SELECT {$userNameColum} FROM {$userTable} WHERE idx = MT.reg_idx ) AS reg_name 
			, ( SELECT COUNT(*) FROM mt_bbs_comment WHERE bbs_idx = MT.idx AND use_yn = 'Y' ) AS commentCnt 
		FROM mt_bbs MT 
		{$andQuery}
		AND bbs_code = '{$bbsCode}' 
		AND noti_yn = 'Y' 
		ORDER BY idx DESC
	");

?>
<div class="bbsListWrap">
	<?php foreach($sql2 as $row){ ?>
		<ul class="notice basic" onclick="location.href = '<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=V&idx=<?=$row['idx']?>';">
			<li class="bbsInfo">
				<span class="tit"><i class="fas fa-volume-up"></i><?=bbsDhtml($row['title'])?></span>
				<span class="user"><?=$row['reg_name']?></span>
				<span class="date">
				<?php if(date("Y-m-d") == date("Y-m-d", strtotime($row['reg_date']))){ ?>
					<?=date("H:i", strtotime($row['reg_date']))?>
				<?php } else { ?>
					<?=date("Y-m-d", strtotime($row['reg_date']))?>
				<?php } ?>
				</span>
				<span class="view">조회 <?=number_format($row['hit'])?></span>
			</li>
			<li class="commentInfo">
				<span class="cnt"><?=$row['commentCnt']?></span>
				<span class="label">댓글</span>
			</li>
		</ul>
	<?php } ?>

	<?php foreach ( $sql as $row ){?>
		<ul class="basic" onclick="location.href = '<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=V&idx=<?=$row['idx']?>';">
			<li class="bbsInfo">
				<span class="tit"><?=bbsDhtml($row['title'])?></span>
				<span class="user"><?=$row['reg_name']?></span>
				<span class="date">
				<?php if(date("Y-m-d") == date("Y-m-d", strtotime($row['reg_date']))){ ?>
					<?=date("H:i", strtotime($row['reg_date']))?>
				<?php } else { ?>
					<?=date("Y-m-d", strtotime($row['reg_date']))?>
				<?php } ?>
				</span>
				<span class="view">조회 <?=number_format($row['hit'])?></span>
			</li>
			<li class="commentInfo">
				<span class="cnt"><?=$row['commentCnt']?></span>
				<span class="label">댓글</span>
			</li>
		</ul>
	<?php } ?>
	
	<?php if(!$totalCnt){ ?>
		<ul class="noData">조회된 데이터가 존재하지 않습니다.</ul>
	<?php } ?>
</div>