<?php

	$nextData = view_sql("
		SELECT *
		FROM mt_bbs
		{$andQuery}
		AND idx > {$view['idx']}
		ORDER BY idx ASC
		LIMIT 0, 1
	");

	$prevData = view_sql("
		SELECT *
		FROM mt_bbs
		{$andQuery}
		AND idx < {$view['idx']}
		ORDER BY idx DESC
		LIMIT 0, 1
	");

?>
<div class="bbsNavigationWrap">
	<table>
		<colgroup>
			<col width="6%">
			<col width="10%">
			<col width="">
			<col width="100px">
		</colgroup>
		<tbody>
		<?php if($nextData){ ?>
			<tr onclick="location.href = '<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=V&idx=<?=$nextData['idx']?>';">
				<td><i class="fas fa-angle-up"></i></td>
				<td style="font-weight: 400;">다음글</td>
				<td class="title" style="text-align: left;"><?=bbsDhtml($nextData['title'])?></td>
				<td>
				<?php if(date("Y-m-d") == date("Y-m-d", strtotime($nextData['reg_date']))){ ?>
					<?=date("H:i", strtotime($nextData['reg_date']))?>
				<?php } else { ?>
					<?=date("Y-m-d", strtotime($nextData['reg_date']))?>
				<?php } ?>
				</td>
			</tr>
		<?php } else { ?>
			<tr>
				<td colspan="4" style="cursor: default; color: #BBB;">다음글이 존재하지 않습니다.</td>
			</tr>
		<?php } ?>
		<?php if($prevData){ ?>
			<tr onclick="location.href = '<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=V&idx=<?=$prevData['idx']?>';">
				<td><i class="fas fa-angle-down"></i></td>
				<td style="font-weight: 400;">이전글</td>
				<td class="title" style="text-align: left;"><?=bbsDhtml($prevData['title'])?></td>
				<td>
				<?php if(date("Y-m-d") == date("Y-m-d", strtotime($prevData['reg_date']))){ ?>
					<?=date("H:i", strtotime($prevData['reg_date']))?>
				<?php } else { ?>
					<?=date("Y-m-d", strtotime($prevData['reg_date']))?>
				<?php } ?>
				</td>
			</tr>
		<?php } else { ?>
			<tr>
				<td colspan="4" style="cursor: default; color: #BBB;">이전글이 존재하지 않습니다.</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>