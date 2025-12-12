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
						<input type="text" name="title" style="width: 80%;" value="<?=$view['title']?>">
						<input type="checkbox" name="notice" id="notice" <?=($view['noti_yn'] == "Y") ? "checked" : ""?>>
						<label for="notice" class="ch">
							<i class="far fa-circle off"></i>
							<i class="fas fa-check-circle on"></i>
							공지여부
						</label>
					</td>
				</tr>
				<tr>
					<th>내용</th>
					<td class="se2Plugin"><textarea name="contents" id="bbsContents"><?=dhtml2(dhtml($view['content']))?></textarea></td>
				</tr>
				<tr>
					<th>첨부파일</th>
					<td>
						<input type="file" id="bbsFile" data-cnt="<?=$bbsInfo['max_file']?>">
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
								<div><?=convertFileSize($row['file_size'])?></div>
								<div><i class="far fa-times-circle fileDeleteBtn"></i></div>
							</li>
						<?php } ?>
						</ul>
					</td>
				</tr>
				<tr>
					<th>팝업 기간설정</th>
					<td>
						<input type="text" name="etc1" class="s_date" placeholder="시작일시" dateonly style="width: 150px;" value="<?=$view['etc1']?>">
						<input type="text" name="etc2" class="e_date" placeholder="종료일시" dateonly style="width: 150px; margin-left: 10px;" value="<?=$view['etc2']?>">
						<div class="bbsFileEx">* 팝업기간 설정 시 로그인 후 대시보드 화면에서 팝업형태로 설정 기간동안 노출됩니다.</div>
					</td>
				</tr>
				<tr>
					<th>상단고정 기간설정</th>
					<td>
						<input type="text" name="etc3" class="s_date" placeholder="시작일시" dateonly style="width: 150px;" value="<?=$view['etc3']?>">
						<input type="text" name="etc4" class="e_date" placeholder="종료일시" dateonly style="width: 150px; margin-left: 10px;" value="<?=$view['etc4']?>">
						<div class="bbsFileEx">* 상단고정기간 설정 시 좌측 상단에 고정 목록형태로 설정 기간동안 노출됩니다.</div>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>