<?php

	if($user['mida_yn'] == "N" || $user['auth_code'] != "001"){
		include_once $_SERVER['DOCUMENT_ROOT']."/mida/module/bbs/bbsNoData.php";
		return false;
	}

?>
<div class="bbsEditorWrap">
	<form data-type="W" data-callback="<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=L">
		<input type="hidden" name="bbs_code" value="<?=$bbsCode?>">
		<table>
			<tbody>
				<tr>
					<th>제목</th>
					<td>
						<input type="text" name="title">
					</td>
				</tr>
				<tr>
					<th>내용</th>
					<td class="se2Plugin"><input type="text" name="contents" id="bbsContents"></td>
				</tr>
			</tbody>
		</table>
	</form>
</div>