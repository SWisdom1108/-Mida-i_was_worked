<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002", "004"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 데이터 정리
	$data = explode(",", $_COOKIE['listCheckData']);
	if(!count($data)){
		www("/sub/error/popup");
	}

	# 컬럼 정리
	$columnCnt = 0;
	$columnArr = [];
	$value = array(':use_yn'=>'Y',':list_yn'=>'Y');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = :use_yn
		AND list_yn = :list_yn
		ORDER BY idx ASC
	";
	$columnData = list_pdo($query, $value);
	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$columnCnt++;
		
		$thisdatas = [];
		$thisdatas['name'] = $row['column_name'];
		$thisdatas['code'] = $row['column_code'];
		$thisdatas['type'] = $row['column_type'];
		
		$columnArr[$columnCnt] = $thisdatas;
	}
	$columnWidth = 57 / ($columnCnt + 2);


	$value = array(':use_yn'=>'Y');
	$query = "SELECT * FROM mc_db_grade_info WHERE use_yn = 'Y' ORDER BY grade_code ASC";
	$grade = list_pdo($query, $value);

	$value = array(':use_yn'=>'Y');
	$query = "SELECT * FROM mt_db WHERE use_yn = :use_yn AND idx IN ( {$_COOKIE['listCheckData']} ) ORDER BY FIELD(idx, {$_COOKIE['listCheckData']} ) ASC";
	$gradea = view_pdo($query, $value);

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data">
			<div class="tit">상담상태 정보</div>
			<input type="hidden" value="<?=$gradea["grade_code"]?>">
			<table>
				<colgroup>
					<col width="217px">
				</colgroup>
				<tbody>
					<tr>
						<th>상담상태 선택</th>
						<td>
							<select name="grade_code" class="txtBox">
							<?php while($row = $grade->fetch(PDO::FETCH_ASSOC)){ ?>
								<option value="<?=$row["grade_code"]?>" <?=($gradea["grade_code"] == $row["grade_code"]) ? "selected" : ""?>><?=dhtml($row["grade_name"])?></option>
							<?php } ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			
			<div class="listWrap">
				<div></div>
				<div class="info">
					총 <span id="dbTotalCnt"><?=number_format(count($data))?>개</span>의 DB가 고객등급변경 대기중에 있습니다.
				</div>
				<table>
					<colgroup>
						<col width="4%">
						<col width="8%">
						<col width="10%">
						<col width="9%">
						<col width="7%">
					<?php for($i = 0; $i < ($columnCnt + 2); $i++){ ?>
						<col width="<?=$columnWidth?>%">
					<?php } ?>
						<col width="5%">
					</colgroup>
					<thead>
						<tr>
							<th rowspan="2">NO</th>
							<th rowspan="2">DB고유번호</th>
							<th rowspan="2">생산업체</th>
							<th rowspan="2">고객등급</th>
							<th colspan="<?=($columnCnt + 2)?>" style="border-right: 0;">DB정보</th>
							<th rowspan="2" style="border-left: 1px solid #FFF;">분배일자</th>
							<th rowspan="2">삭제</th>
						</tr>
						<tr>
							<th><?=$customLabel["cs_name"]?></th>
							<th><?=$customLabel["cs_tel"]?></th>
						<?php foreach($columnArr as $val){ ?>
							<th><?=$val['name']?></th>
						<?php } ?>
						</tr>
					</thead>
					<tbody>
					<?php
						$i = 0;
						$value = array(':use_yn'=>'Y',':dist_code'=>'002');
						$query = "
							SELECT MT.*
								, ( SELECT company_name FROM mt_member_cmpy WHERE idx = MT.pm_code ) AS pm_name
								, ( SELECT grade_name FROM mc_db_grade_info WHERE grade_code = MT.grade_code ) AS grade_name
							FROM mt_db MT
							WHERE use_yn = :use_yn
							AND dist_code = :dist_code
							AND idx IN ( {$_COOKIE['listCheckData']} )
							ORDER BY FIELD(idx,{$_COOKIE['listCheckData']}) ASC
						";
						$sql = list_pdo($query, $value);
						while($row = $sql->fetch(PDO::FETCH_ASSOC)){
							$i++;
					?>
						<tr>
							<td class="lp05"><?=$i?></td>
							<td class="lp05">D-<?=$row['idx']?><input type="hidden" name="idx[]" value="<?=$row['idx']?>"></td>
							<?php $row['pm_name'] = dhtml($row['pm_name']) ?>
							<td class="lp05"><?=($row['pm_code']) ? "PM{$row['pm_code']}<br>{$row['pm_name']}" : "-"?></td>
							<td class="lp05"><?=dhtml($row['grade_name'])?></td>
							<td><?=($row['cs_name']) ? dhtml($row['cs_name']) : "-"?></td>
							<td class="lp05"><?=($row['cs_tel']) ? $row['cs_tel'] : "-"?></td>
						<?php foreach($columnArr as $val){ ?>
							<td><?php
								if($val['type'] == "file"){
									if($row["{$val['code']}"]){
										$value = explode( '@#@#', $row["{$val['code']}"] );
										echo "<a href='/upload/db_etc/{$value[0]}' class='db_csdwon' download='{$value[1]}'>{$value[1]}<i class=\"fas fa-download\"></i></a>";	
									}else{
										echo "-";
									}
								} else{
									echo ($row["{$val['code']}"]) ? $row["{$val['code']}"] : "-";
								}
								?></td>
						<?php } ?>
							<td class="lp05"><?=date("Y-m-d", strtotime($row['order_by_date']))?></td>
							<td><i class="far fa-times-circle click rowDeleteBtn"></i></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
		</form>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnBlack" id="submitBtn">완료</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="grade">취소</button>
	</div>
	
	<script type="text/javascript">
		
		$(function(){
			
			$("#submitBtn").click(function(){
				var datas = $(".writeWrap > form").serialize();
				
				var cnt = $(".listWrap tbody > tr").length;
				if(!cnt){
					alert("고객등급변경 가능한 DB가 존재하지 않습니다.");
					return false;
				}
				
				
				$("#loadingWrap").fadeIn(350, function(){
					$.ajax({
						url : "/ajax/db/dbGradeChange",
						type : "POST",
						data : datas,
						success : function(result){
							switch(result){
								case "success" :
									alert("고객등급변경이 완료되었습니다.");
									parent.popupSubmitClose();
									break;
								case "fail" :
									alert("알 수 없는 이유로 고객등급변경을 실패하였습니다.");
									$("#loadingWrap").fadeOut(350);
									break;
								default :
									alert(result);
									console.log(result);
									$("#loadingWrap").fadeOut(350);
									break;
							}
						}
					});
				});
			});
			
			$(".rowDeleteBtn").click(function(){
				$(this).closest("tr").remove();
				
				var item = $(".listWrap tbody > tr");
				var cnt = item.length;
				
				for(var i = 0; i < cnt; i++){
					$(item[i]).find("td:first-of-type").html(i+1);
				}
				
				$("#dbTotalCnt").html(cnt + "개");
			});
			
			
			
		});
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>