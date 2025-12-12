<?php

	$value = array(':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
			, ( SELECT {$userNameColum} FROM {$userTable} WHERE idx = MT.reg_idx ) AS reg_name
			, ( SELECT count(*) FROM mt_bbs_file WHERE bbs_idx = MT.idx ) AS file_cnt
			, ( SELECT COUNT(*) FROM mt_bbs_comment WHERE bbs_idx = MT.idx AND use_yn = 'Y' ) AS commentCnt 
		FROM mt_bbs MT
		WHERE use_yn = 'Y'
		{$groupQuery}
		AND idx = :idx
		AND bbs_code = '{$bbsCode}'
	";
	$view = view_pdo($query, $value);

	if(!$view){
		include_once $_SERVER['DOCUMENT_ROOT']."/mida/module/bbs/bbsNoData.php";
		return false;
	}

?>
<div class="bbsEditorWrap">
	<form data-type="U" data-callback="<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=L">
		<input type="hidden" name="idx" value="<?=$view['idx']?>">
		<table>
			<tbody>
				<tr>
					<th>제목</th>
					<td>
						<input type="text" name="title" value="<?=$view['title']?>">
					</td>
				</tr>
				<tr>
					<th>내용</th>
					<td class="se2Plugin"><textarea name="contents" id="bbsContents"><?=$view['content']?></textarea></td>
				</tr>
			</tbody>
		</table>
	</form>
</div>