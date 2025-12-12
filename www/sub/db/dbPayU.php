<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 데이터 정보추출
	$value = array(':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
		FROM mt_db_pay MT
		WHERE use_yn = 'Y'
        AND idx = :idx
	";
	$view = view_pdo($query, $value);

	if(!$view){
		include_once "{$_SERVER['DOCUMENT_ROOT']}/sub/error/popup.php";
		return false;
	}

    $value = array('' => '');
    $query = "
        SELECT treat_code, treat_name
        FROM mc_treatment_code
        WHERE use_yn = 'Y'
    ";
    $treatment = list_pdo($query,$value);

    $value = array('' => '');
    $query = "
        SELECT idx, m_name
        FROM mt_member
        WHERE use_yn = 'Y'
        AND auth_code = '006'
    ";
    $md_list = list_pdo($query,$value);

    $value = array('' => '');
    $query = "
        SELECT idx, m_name
        FROM mt_member
        WHERE use_yn = 'Y'
        AND auth_code = '007'
    ";
    $dr_list = list_pdo($query,$value);
?>

	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/db/dbPayUP" data-callback="close" data-type="수정">
			<input type="hidden" name="idx" value="<?=$view['idx']?>">
			<div class="tit">진료정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th class=""><?=$customLabel["cs_name"]?></th>
						<td><?=$view['cs_name']?></td>
                        <th class="">차트번호</th>
						<td><?=$view['chart_num']?></td>
					</tr>
                    <tr>
                        <th class="important">진료항목</th>
						<td>
                            <select class="txtBox" name="treat_code">
                            <?php
                                while($row = $treatment ->fetch(PDO::FETCH_ASSOC)){
                            ?>
                            <option value = <?=$row['treat_code']?> <?=($row['treat_code'] == $view['treat_code']) ? "selected" : ""?>><?=$row['treat_name']?></option>
                            <?php }?>
                            </select>
                        </td>
                        <th>금액</th>
                        <td><input type="text" class="txtBox" name="pay" numonly value="<?=$view['pay']?>"></td>
                    </tr>
				</tbody>
			</table>
            <div class="tit">담당자정보</div>
            <table>
                <colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
                <tbody>
                    <tr>
                        <th class="">실장명</th>
						<td>
                            <select class="txtBox" name="md_idx">
                                <option value = ''>실장명선택</option>
                                <?php
                                    while($row = $md_list ->fetch(PDO::FETCH_ASSOC)){
                                ?>
                                <option value = <?=$row['idx']?> <?=($row['idx'] == $view['md_idx']) ? "selected" : ""?>><?=$row['m_name']?></option>
                                <?php }?>
                            </select>
                        </td>
                        <th class="">닥터명</th>
						<td>
                            <select class="txtBox" name="dr_idx">
                                <option value = ''>닥터명선택</option>
                                <?php
                                    while($row = $dr_list ->fetch(PDO::FETCH_ASSOC)){
                                ?>
                                <option value = <?=$row['idx']?> <?=($row['idx'] == $view['dr_idx']) ? "selected" : ""?>><?=$row['m_name']?></option>
                            <?php }?>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
		</form>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnRed deleteBtn" data-ajax="/ajax/db/dbPayDP" data-callback="close" data-idx="<?=$view['idx']?>">삭제</button>
		<button type="button" class="typeBtn btnBlack submitBtn" data-target="write">수정</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="mod">취소</button>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>