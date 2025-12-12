<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 데이터 정보추출
	$value = array(':use_yn'=>'Y',':idx'=>"{$_GET['idx']}",':pm_code'=>"{$user['pm_code']}");
	$query = "
		SELECT MT.*
		FROM mt_db MT
		WHERE use_yn = :use_yn
		AND idx = :idx
		AND pm_code = :pm_code
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
		
		$columnArr[$columnCnt] = $thisdatas;
	}

	$value = array(':idx'=>"{$user['pm_code']}");
	$query = "SELECT * FROM mt_member_cmpy WHERE idx = :idx";
	$cmpyView = view_pdo($query, $value)['hidden_yn'];

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/db/dbDistUP" data-callback="close" data-type="수정">
			<div class="tit">생산정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important">생산일자</th>
						<td><?=date("Y-m-d", strtotime($view['made_date']))?></td>
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
						<td>							
							<?php if($cmpyView == "Y" && $view['dist_code'] == "002"){ ?>
								<?=(mb_strlen($view['cs_name']) > 1) ? mb_substr($view['cs_name'],0,1,'utf-8')."**" : $view['cs_name']?>
							<?php }else{ ?>
								<?=$view['cs_name']?>
							<?php } ?>									
						</td>
						<th class="important"><?=$customLabel["cs_tel"]?></th>
						<td>
							<?php if($cmpyView == "Y" && $view['dist_code'] == "002"){ ?>
								<?=(mb_strlen($view['cs_tel']) > 1) ? mb_substr($view['cs_tel'],0,1,'utf-8')."*******" : $view['cs_tel']?>
							<?php }else{ ?>
								<?=$view['cs_tel']?>
							<?php } ?>							
						</td>
					</tr>
				<?php foreach($columnArr as $index => $val){ ?>
					<tr>
						<th><?=$val['name']?></th>
						<td colspan="3"><?=$view[$val['code']]?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</form>
	</div>
	
	<div id="popupBtnWrap">
		<?php if($view['dist_code']=="001"){ ?>
		<button type="button" class="typeBtn btnRed deleteBtn" data-ajax="/ajax/db/dbDP" data-callback="close" data-idx="<?=$view['idx']?>">삭제</button>
		<?php } ?>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="mod">취소</button>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>