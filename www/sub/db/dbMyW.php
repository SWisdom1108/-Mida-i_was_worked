<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 컬럼 정리
	$columnCnt = 0;
	$columnArr = [];
	$value = array(':use_yn'=>'Y');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = :use_yn
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);

	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$columnCnt++;
		
		$thisdatas = [];
		$thisdatas['name'] = $row['column_name'];
		$thisdatas['code'] = $row['column_code'];
		$thisdatas['type'] = $row['column_type'];
		$thisdatas['ex'] = $row['column_ex'];
		$thisdatas['idx'] = $row['idx'];
		
		$columnArr[$columnCnt] = $thisdatas;
	}

	# 200825 생산업체목록	
	$value = array(':use_yn'=>'Y' ,':auth_code'=>'003');
	$query = "
		SELECT * FROM mt_member_cmpy WHERE use_yn = :use_yn AND auth_code = :auth_code ORDER BY idx DESC
	";
	$pmList = list_pdo($query, $value);

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/db/dbMyWP" data-callback="close" data-type="등록">
			<div class="tit">생산정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th>DB유입경로</th>
						<td>
							<select class="txtBox" name="pm_code">
								<option value="0">선택안함</option>
							<?php 	while($row = $pmList->fetch(PDO::FETCH_ASSOC)){ ?>
								<option value="<?=$row["idx"]?>"><?=$row["company_name"]?></option>
							<?php } ?>
							</select>
						</td>
						<th class="important">생산일자</th>
						<td><input type="text" class="txtBox" name="made_date" value="<?=date("Y-m-d")?>" dateonly></td>
					</tr>
				</tbody>
			</table>
			
			<div class="tit">DB정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important"><?=$customLabel["cs_name"]?></th>
						<td><input type="text" class="txtBox" name="cs_name"></td>
						<th class="important"><?=$customLabel["cs_tel"]?></th>
						<td><input type="text" class="txtBox" name="cs_tel" numonly></td>
					</tr>
				<?php foreach($columnArr as $index => $val){ ?>
					<tr>
						<th><?=$val['name']?></th>
						<td colspan="3"><?=createFrm($val['type'], $val['ex'], $val['idx'], $val['code'])?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</form>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnBlack submitBtn" data-target="write">완료</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="write">취소</button>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>