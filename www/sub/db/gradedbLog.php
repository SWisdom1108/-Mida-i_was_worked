<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002", "004", "005"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	$value = array(''=>'');
	$query = "SELECT * FROM mc_db_grade_info WHERE use_yn = 'Y' AND del_yn = 'N' ORDER BY grade_code DESC";
	$grade = list_pdo($query, $value);
	
	if($_GET['gradeCode']){
		$andQuery = "AND grade_code = {$_GET['gradeCode']}";
	}

	$value = array(''=>'');
	$query = "
		SELECT
				( SELECT COUNT(*) FROM mt_db_grade_log WHERE use_yn = 'Y' AND db_idx = '{$_GET['idx']}' {$andQuery}) AS Cnt
		FROM dual
	";
	$total = view_pdo($query, $value);

?>

<div class="dbCheckDataInfoWrap">
	<div class="titWrap">
		<div class="left">
			<i class="fas fa-arrow-circle-right"></i>
			회원고유번호 : <?=$_GET['idx']?>
		</div>		
		<div class="right">
		</div>
	</div>
</div>
<div class="listWrap">
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=$total['Cnt']?></span>
			<span class="cnt" style="margin: 0 15px; color: #CCC; font-weight: 400; top: -1.5px;">|</span>
			<select class="listSet" id="gradeCode">
				<option value="">고객등급별 보기</option>
			<?php while($row = $grade->fetch(PDO::FETCH_ASSOC)){ ?>
				<option value="<?=$row["grade_code"]?>" <?=($_GET["gradeCode"] == $row["grade_code"]) ? "selected" : ""?>><?=dhtml($row["grade_name"])?></option>
			<?php } ?>
			</select>
		</div>
	</div>
	<table>
		<colgroup>
			<col width="4%">
			<col width="10%">
			<col width="16%">
			<col width="60%">
			<col width="10%">
		</colgroup>
		<thead>
			<tr>
				<th>NO</th>
				<th>회원명</th>
				<th>고객등급</th>
				<th>상세설명</th>
				<th>변경일시</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$value = array(':idx'=>$_GET['idx']);
				$query = "
					SELECT MT.*
						, ( SELECT cs_name FROM mt_db WHERE idx = MT.db_idx ) AS db_name
						, ( SELECT grade_name FROM mc_db_grade_info WHERE MT.grade_code = grade_code ) AS grade_code
					FROM mt_db_grade_log MT
					WHERE use_yn = 'Y'
					AND db_idx = :idx
					{$andQuery}
					ORDER BY reg_date DESC
				";
				$sql = list_pdo($query, $value);
				$i = 0;
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
				$i++;
			?>
			<?php if($total['Cnt'] > 0){ ?>
				<tr>
					<td class="lp05"><?=$i?></td>
					<td class="lp05"><?=$row['db_name']?></td>
					<td class="lp05"><?=$row['grade_name']?></td>
					<td class="lp05"><?=$row['ex_memo']?></td>
					<td class="lp05">
						<?=date("Y-m-d", strtotime($row['reg_date']))?>
						<br><span style="font-size: 12px;"><?=date("H:i:s", strtotime($row['reg_date']))?></span>
					</td>
				</tr>
			<?php } ?>
			<?php } ?>

			<?php if($total['Cnt'] == 0){ ?>
				<tr>
					<td colspan="6">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<div id="popupBtnWrap">
	<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="gradelog">닫기</button>
</div>
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>