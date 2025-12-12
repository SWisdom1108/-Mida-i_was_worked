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

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/db/dbTeamWP" data-callback="close" data-type="등록">
			<input type="hidden" name="tmCode" value="<?=$_GET['code']?>">
			<div class="tit">담당자 정보</div>
			<table>
				<colgroup>
					<col width="217px">
				</colgroup>
				<tbody>
					<tr>
						<th>담당자 선택</th>
						<td>
							<ul id="fcListWrap">
							<?php
								$fcList_i = 0;
								$value = array(':tm_code'=>$_GET['code']);
								$query = "SELECT * FROM mt_member WHERE use_yn = 'Y' AND tm_code = :tm_code ORDER BY auth_code ASC";
								$sql = list_pdo($query, $value);
								while($row = $sql->fetch(PDO::FETCH_ASSOC)){
									$fcList_i++;
							?>
								<li>
									<input type="radio" id="fcItem<?=$row['idx']?>" name="fcCode" value="<?=$row['idx']?>" <?=($fcList_i == 1) ? "checked" : ""?>>
									<label for="fcItem<?=$row['idx']?>">
										<i class="fas fa-check-circle on"></i>
										<i class="far fa-circle off"></i>
										<span><?=$row['m_name']?>(FC<?=$row['idx']?>)</span>
										<i class="fas fa-crown" style="margin-left: 3px; color: #<?=($row['auth_code'] == "004") ? "DC3333" : "DDD"?>;" title="<?=($row['auth_code'] == "004") ? "{$customLabel["tm"]} 담당자" : "일반 담당자"?>"></i>
									</label>
								</li>
							<?php } ?>
							<?php if($fcList_i == 0){?>
								<input type="hidden" name="no_fc" value="N">
							<?php } ?>
							</ul>
						</td>
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