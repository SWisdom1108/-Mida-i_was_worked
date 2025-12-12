<?php

	$view = view_sql("
		SELECT MT.*
			, ( SELECT count(*) FROM mt_bbs_file WHERE bbs_idx = MT.idx ) AS file_cnt
		FROM mt_bbs MT
		WHERE use_yn = 'Y'
		AND idx = '{$_GET['idx']}'
		AND bbs_code = '{$bbsCode}'
	");

	if(!$view){
		include_once $_SERVER['DOCUMENT_ROOT']."/mida/module/bbs/bbsNoData.php";
		return false;
	}

	# 파일정보
	$sql = list_sql("
		SELECT *
		FROM mt_bbs_file
		WHERE bbs_idx = '{$view['idx']}'
		ORDER BY filename_r ASC
	");
	$fileDisplay = ($view['file_cnt'] == 0) ? "block" : "none";

?>
<div class="bbsEditorWrap">
	<form data-type="U" data-callback="<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=V&idx=<?=$view['idx']?>">
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
				<tr>
					<th>첨부파일</th>
					<td>
						<input type="file" name="bbsFile" id="bbsFile" data-cnt="<?=$bbsInfo['max_file']?>">
						<label for="bbsFile" id="bbsFileBtn"><i class="fas fa-search"></i>파일찾기</label>
						<div class="bbsFileEx">* 최대 업로드 가능한 파일 개수는 <b><?=$bbsInfo['max_file']?>개</b>입니다.</div>
						<ul class="bbsFileUploadListWrap">
							<li class="head"><div>파일명</div><div>용량</div><div>삭제</div></li>
							<li class="noData" style="display: <?=$fileDisplay?>;">업로드된 파일이 존재하지 않습니다.</li>
						<?php foreach ( $sql as $row ){?>
							<li class="item">
								<div>
									<?=$row['filename_r']?>
									<input type="hidden" name="fileItem_<?=$row['idx']?>" value="<?=$row['idx']?>">
								</div>
								<div><?=round($row['file_size']/1024/1024)?>MB</div>
								<div><i class="far fa-times-circle fileDeleteBtn"></i></div>
							</li>
						<?php } ?>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>