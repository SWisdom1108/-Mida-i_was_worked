<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 데이터 정보추출
	$value = array(':tm_code'=>$user['tm_code'], ':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
		FROM mt_db MT
		WHERE use_yn = 'Y'
		AND tm_code = :tm_code
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

	$value = array(''=>'');
	$query = "SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = 'Y'
		ORDER BY sort ASC";

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

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/db/dbMyTeamUP" data-callback="close" data-type="수정">
			<input type="hidden" name="idx" value="<?=$view['idx']?>">
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
						<td><input type="text" class="txtBox" name="cs_name" value="<?=$view['cs_name']?>"></td>
						<th class="important"><?=$customLabel["cs_tel"]?></th>
						<td><input type="text" class="txtBox" name="cs_tel" numonly value="<?=$view['cs_tel']?>"></td>
					</tr>
				<?php foreach($columnArr as $index => $val){ ?>
					<tr>
						<th><?=$val['name']?></th>
						<td colspan="3"><?=createFrm($val['type'], $val['ex'], $val['idx'], $val['code'], $view[$val['code']])?></td>
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