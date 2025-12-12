<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["004"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/headerPopup.php";

	# 데이터 정리
	$data = explode(",", $_COOKIE['listCheckData']);
	if(!count($data)){
		www("/sub/error/popup");
	}

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data">
			<div class="tit">DB담당정보 설정</div>
			<table>
				<colgroup>
					<col width="40%">
					<col width="60%">
				</colgroup>
				<tbody>
					<tr>
						<th class="important">담당자 선택</th>
						<td>
							<input type="hidden" name="fcCode" id="selectFCCode" value="">
							<ul id="fcListWrap">
							<?php
								$value = array(':use_yn'=>'Y',':tm_code'=>$user['tm_code']);
								$query = "SELECT * FROM mt_member WHERE use_yn = :use_yn AND tm_code = :tm_code";
								$sql = list_pdo($query, $value);
								while($row = $sql->fetch(PDO::FETCH_ASSOC)){
								
							?>
								<li>
									<input type='radio' id='fcItem<?=$row["idx"]?>' name='fcListCode' class='fcListCode' value='<?=$row["idx"]?>'>
									<label for='fcItem<?=$row["idx"]?>'>
										<i class='fas fa-check-circle on'></i>
										<i class='far fa-circle off'></i>
										<span><?=$row["m_name"]?>(FC<?=$row["idx"]?>)</span>
									</label>
								</li>
							<?php } ?>
							</ul>
						</td>
					</tr>
				</tbody>
			</table>
			
			<div class="orgListWrap">
				<div></div>
				<div class="info">
					<b>총 <span id="dbTotalCnt"><?=number_format(count($data))?>개</span>가 분배 대기중</b>에 있습니다.
				</div>
				<table>
					<colgroup>
						<col width="30%">
						<col width="35%">
						<col width="35%">
					</colgroup>
					<thead>
						<tr>
							<th>DB고유번호</th>
							<th><?=$customLabel["cs_name"]?></th>
							<th><?=$customLabel["cs_tel"]?></th>
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
							<td class="lp05">D-<?=$row['idx']?><input type="hidden" name="idx[]" value="<?=$row['idx']?>"></td>
							<td><?=($row['cs_name']) ? "{$row['cs_name']}" : "-"?></td>
							<td class="lp05"><?=($row['cs_tel']) ? $row['cs_tel'] : "-"?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
		</form>
	</div>
	
	<div class="popupBtnWrap">
		<button type="button" class="typeBtn btnMain" id="submitBtn">완료</button>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			$("#submitBtn").click(function(){
				var datas = $(".writeWrap > form").serialize();
				
				var cnt = $(".orgListWrap tbody > tr").length;
				if(!cnt){
					alert("분배가능한 DB가 존재하지 않습니다.");
					return false;
				}
				
				if(!$("#selectFCCode").val()){
					alert("담당자를 선택해주시길 바랍니다.");
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
			
			$(document).on("click", ".fcListCode", function(){
				$("#selectFCCode").val($(this).val());
			});
			
		});
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/footerPopup.php"; ?>