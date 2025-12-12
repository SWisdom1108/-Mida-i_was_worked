<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 데이터 정보추출
	$value = array(':use_yn'=>'Y', ':idx' => $_GET['idx']);
	$query = "
		SELECT MT.*
		FROM mt_db MT
		WHERE use_yn = :use_yn
		AND idx = :idx
	";
	$view = view_pdo($query, $value);

	if(!$view){
		include_once "{$_SERVER['DOCUMENT_ROOT']}/sub/error/popup.php";
		return false;
	}

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
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/db/dbAllUP" data-callback="close" data-type="수정">
			<input type="hidden" name="idx" value="<?=$view['idx']?>">
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
						<th>생산업체</th>
						<td>
							<select class="txtBox" name="pm_code">
								<option value="0">선택안함</option>
							<?php 	while($row = $pmList->fetch(PDO::FETCH_ASSOC)){ ?>
								<option value="<?=$row["idx"]?>" <?=($row["idx"] == $view["pm_code"]) ? "selected" : ""?>><?=dhtml($row["company_name"])?></option>
							<?php } ?>
							</select>
						</td>
						<th class="important">생산일자</th>
						<td><input type="text" class="txtBox" name="made_date" value="<?=date("Y-m-d", strtotime($view['made_date']))?>" dateonly></td>
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
						<td><input type="text" class="txtBox" name="cs_name" value="<?=dhtml($view['cs_name'])?>"></td>
						<th class="important"><?=$customLabel["cs_tel"]?></th>
						<td><input type="text" class="txtBox" name="cs_tel" numonly value="<?=$view['cs_tel']?>"></td>
					</tr>
				<?php foreach($columnArr as $index => $val){ ?>
					<tr>
						<th><?=dhtml($val['name'])?></th>
						<td colspan="3"><?=createFrm($val['type'], $val['ex'], $val['idx'], $val['code'], ($view[$val['code']]))?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</form>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnRed deleteBtn" data-ajax="/ajax/db/dbDP" data-callback="close" data-idx="<?=$view['idx']?>">삭제</button>
		<button type="button" class="typeBtn btnBlack submitBtn" data-target="write">수정</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="mod">취소</button>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>