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
	<table>
		<colgroup>
			<col width="5%">
			<col width="">
			<col width="12%">
			<col width="13%">
			<col width="7%">
		</colgroup>
		
		<thead>
			<tr>
				<th></th>
				<th>제목</th>
				<th>작성자</th>
				<th>작성일</th>
				<th>조회</th>
			</tr>
		</thead>
		
		<tbody>
		<?php foreach($sql2 as $row){ ?>
			<tr onclick="location.href = '<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=V&idx=<?=$row['idx']?>';" class="notiRow">
				<td><i class="fas fa-star"></i></td>
				<td style="text-align: left;" class="title"><?=($row['important_yn'] == "Y") ? ' <b class="importantData">긴급</b>' : ''?><?=bbsDhtml($row['title'])?><span class="comment">[<?=$row['commentCnt']?>]</span></td>
				<td><?=$row['reg_name']?></td>
				<td>
				<?php if(date("Y-m-d") == date("Y-m-d", strtotime($row['reg_date']))){ ?>
					<?=date("H:i", strtotime($row['reg_date']))?>
				<?php } else { ?>
					<?=date("Y-m-d", strtotime($row['reg_date']))?>
				<?php } ?>
				</td>
				<td><?=number_format($row['hit'])?></td>
			</tr>
		<?php } ?>
		<?php foreach ( $sql as $row ){?>
			<tr onclick="location.href = '<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=V&idx=<?=$row['idx']?>';">
				<td><?=bbsListNo()?></td>
				<td style="text-align: left;" class="title"><?=($row['important_yn'] == "Y") ? ' <b class="importantData">긴급</b>' : ''?><?=bbsDhtml($row['title'])?><span class="comment">[<?=$row['commentCnt']?>]</span></td>
				<td><?=$row['reg_name']?></td>
				<td>
				<?php if(date("Y-m-d") == date("Y-m-d", strtotime($row['reg_date']))){ ?>
					<?=date("H:i", strtotime($row['reg_date']))?>
				<?php } else { ?>
					<?=date("Y-m-d", strtotime($row['reg_date']))?>
				<?php } ?>
				</td>
				<td><?=number_format($row['hit'])?></td>
			</tr>
		<?php } ?>
		<?php if($totalCnt == 0){ ?>
			<tr>
				<td colspan="5" style="cursor: default; color: #BBB;">게시글이 존재하지 않습니다.</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>