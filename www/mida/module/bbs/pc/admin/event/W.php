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
				<tr>
					<th>첨부파일</th>
					<td>
						<input type="file" name="bbsFile" id="bbsFile" data-cnt="<?=$bbsInfo['max_file']?>">
						<label for="bbsFile" id="bbsFileBtn"><i class="fas fa-search"></i>파일찾기</label>
						<div class="bbsFileEx">* 최대 업로드 가능한 파일 개수는 <b><?=$bbsInfo['max_file']?>개</b>입니다.</div>
						<ul class="bbsFileUploadListWrap">
							<li class="head"><div>파일명</div><div>용량</div><div>삭제</div></li>
							<li class="noData">업로드된 파일이 존재하지 않습니다.</li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>