<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["004"];

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
	// $columnData = list_sql("

	// ");
	$value = array(':use_yn'=>'Y',':list_yn'=>'Y');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = :use_yn
		AND list_yn = :list_yn
		ORDER BY sort ASC
	";
	$sql = list_pdo($query, $value);
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){	
		$columnCnt++;
		
		$thisdatas = [];
		$thisdatas['name'] = $row['column_name'];
		$thisdatas['code'] = $row['column_code'];
		$thisdatas['type'] = $row['column_type'];
		
		$columnArr[$columnCnt] = $thisdatas;
	}
	$columnWidth = 74 / ($columnCnt + 2);

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data">
			<div class="tit">담당자 정보</div>
			<table>
				<colgroup>
					<col width="217px">
				</colgroup>
				<tbody>
					<tr>
						<th class="important">담당자 선택</th>
						<td>
							<ul id="fcListWrap">
							<?php
								$value = array(':use_yn'=>'Y',':tm_code'=>"{$user['tm_code']}");
								$query = "SELECT * FROM mt_member WHERE use_yn = :use_yn AND tm_code = :tm_code ORDER BY auth_code ASC";
								$sql = list_pdo($query, $value);
								while($row = $sql->fetch(PDO::FETCH_ASSOC)){
							?>
								<li>
									<input type="radio" id="fcItem<?=$row['idx']?>" name="fcCode" value="<?=$row['idx']?>">
									<label for="fcItem<?=$row['idx']?>">
										<i class="fas fa-check-circle on"></i>
										<i class="far fa-circle off"></i>
										<span><?=dhtml($row['m_name'])?>(FC<?=$row['idx']?>)</span>
									</label>
								</li>
							<?php } ?>
							</ul>
						</td>
					</tr>
				</tbody>
			</table>
			
			<div class="listWrap">
				<div></div>
				<div class="info">
					총 <span id="dbTotalCnt"><?=number_format(count($data))?>개</span>의 DB가 담당자변경 대기중에 있습니다.
				</div>
				<table>
					<colgroup>
						<col width="4%">
						<col width="8%">
					<?php for($i = 0; $i < ($columnCnt + 2); $i++){ ?>
						<col width="<?=$columnWidth?>%">
					<?php } ?>
						<col width="9%">
						<col width="5%">
					</colgroup>
					<thead>
						<tr>
							<th rowspan="2">NO</th>
							<th rowspan="2">DB고유번호</th>
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
							FROM mt_db MT
							WHERE use_yn = :use_yn
							AND dist_code = :dist_code
							AND idx IN ( {$_COOKIE['listCheckData']} )
							ORDER BY FIELD(idx, {$_COOKIE['listCheckData']}) ASC
						";
						$sql = list_pdo($query, $value);
						while($row = $sql->fetch(PDO::FETCH_ASSOC)){
							$i++;
					?>
						<tr>
							<td class="lp05"><?=$i?></td>
							<td class="lp05">D-<?=$row['idx']?><input type="hidden" name="idx[]" value="<?=$row['idx']?>"></td>
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
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="dist">취소</button>
	</div>
	
	<script type="text/javascript">
		var fcCodeList = <?=json_encode($fcCodeList)?>;
		var fcNameList = <?=json_encode($fcNameList)?>;
		
		function fcListSet(){
			var code = $("#selectTM").val();
			var codeItem = fcCodeList[code];
			var nameItem = fcNameList[code];
			var html = "";
			
			$.each(codeItem, function(index, value){
				html += "<li>";
				html += "<input type='radio' id='fcItem" + value + "' name='fcListCode' class='fcListCode' value='" + value + "'>";
				html += "<label for='fcItem" + value + "'>";
				html += "<i class='fas fa-check-circle on'></i>";
				html += "<i class='far fa-circle off'></i>";
				html += "<span>" + nameItem[index] + "(FC" + value + ")</span>";
				html += "</label>";
				html += "</li>";
			});
			
			$("#fcListWrap li").remove();
			$("#fcListWrap").append(html);
			$("#selectFCCode").val("");
			
			if(!html){
				$("#fcListWrap").append("<li><span>담당자가 존재하지 않습니다.</span></li>");
				return false;
			}
		}
		
		$(function(){
			
			$("#submitBtn").click(function(){
				var datas = $(".writeWrap > form").serialize();
				
				var cnt = $(".listWrap tbody > tr").length;
				if(!cnt){
					alert("분배가능한 DB가 존재하지 않습니다.");
					return false;
				}
				
				$("#loadingWrap").fadeIn(350, function(){
					$.ajax({
						url : "/ajax/db/dbDistChange",
						type : "POST",
						data : datas,
						success : function(result){
							switch(result){
								case "success" :
									alert("분배가 완료되었습니다.");
									parent.popupSubmitClose();
									break;
								case "fail" :
									alert("알 수 없는 이유로 분배를 실패하였습니다.");
									$("#loadingWrap").fadeOut(350);
									break;
								default :
									alert(result);
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
			
			fcListSet();
			$("#selectTM").change(function(){
				fcListSet();
			});
			
			$(document).on("click", ".fcListCode", function(){
				$("#selectFCCode").val($(this).val());
			});
			
		});
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>