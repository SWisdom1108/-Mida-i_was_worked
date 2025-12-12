<div class="bbsEditorWrap">
	<form data-type="W" data-callback="<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=L">
		<input type="hidden" name="bbs_code" value="<?=$bbsCode?>">
		<table>
			<tbody>
				<tr>
					<th>제목</th>
					<td>
						<input type="text" name="title" style="width: 80%;">
						<input type="checkbox" name="notice" id="notice">
						<label for="notice" class="ch">
							<i class="far fa-circle off"></i>
							<i class="fas fa-check-circle on"></i>
							공지여부
						</label>
					</td>
				</tr>
				<tr>
					<th>내용</th>
					<td class="se2Plugin"><input type="text" name="contents" id="bbsContents"></td>
				</tr>
				<tr>
					<th>첨부파일</th>
					<td>
						<input type="file" id="bbsFile" data-cnt="<?=$bbsInfo['max_file']?>">
						<label for="bbsFile" id="bbsFileBtn"><i class="fas fa-search"></i>파일찾기</label>
						<div class="bbsFileEx">* 최대 업로드 가능한 파일 개수는 <b><?=$bbsInfo['max_file']?>개</b>입니다.</div>
						<ul class="bbsFileUploadListWrap">
							<li class="head"><div>파일명</div><div>용량</div><div>삭제</div></li>
							<li class="noData">업로드된 파일이 존재하지 않습니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th>팝업 기간설정</th>
					<td>
						<input type="text" name="etc1" class="s_date" placeholder="시작일시" dateonly style="width: 150px;">
						<input type="text" name="etc2" class="e_date" placeholder="종료일시" dateonly style="width: 150px; margin-left: 10px;">
						<div class="bbsFileEx">* 팝업기간 설정 시 로그인 후 대시보드 화면에서 팝업형태로 설정 기간동안 노출됩니다.</div>
					</td>
				</tr>
				<tr>
					<th>상단고정 기간설정</th>
					<td>
						<input type="text" name="etc3" class="s_date" placeholder="시작일시" dateonly style="width: 150px;">
						<input type="text" name="etc4" class="e_date" placeholder="종료일시" dateonly style="width: 150px; margin-left: 10px;">
						<div class="bbsFileEx">* 상단고정기간 설정 시 좌측 상단에 고정 목록형태로 설정 기간동안 노출됩니다.</div>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>