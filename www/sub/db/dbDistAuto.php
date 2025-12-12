<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 데이터 정리
	$andQuery = "WHERE use_yn = 'Y' AND (dist_code = '001' OR dist_code = '003')";
	$andQuery .= ($_GET['code']) ? " AND pm_code = '{$_GET['code']}'" : "";

	if ( $_COOKIE['listCheckData'] ){
		$andQuery .= " AND idx IN ( {$_COOKIE['listCheckData']} ) ";
	}



	$value = array(':username'=>$username);
	$query = "SELECT COUNT(*) AS cnt FROM mt_db {$andQuery}";
	$totalCnt = view_pdo($query, $value)['cnt'];

	$value = array(':overlap_yn'=>'Y');
	$query = "SELECT COUNT(*) AS cnt FROM mt_db {$andQuery} AND overlap_yn = :overlap_yn";
	$overtotalCnt = view_pdo($query, $value)['cnt'];

	$minuscnt = $totalCnt - $overtotalCnt;

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data">
			<input type="hidden" name="code" value="<?=$_GET['code']?>">
			<input type="hidden" name="type" id="distType" value="distTM">
			<div class="listWrap">
				<div class="info" id="overlapWrap" style="color: #333;">
					<input type='checkbox' id='overlap_yn_check' name='overlap_yn_check' value='Y' checked>
						<label for='overlap_yn_check'>
							<i class='fas fa-check-circle on'></i>
							<i class='far fa-circle off'></i>
							<span>중복제외</span>
						</label>

					<b>총 <span id="dbTotalCnt" style="color: #DC3333"><?=number_format($minuscnt)?></span>개가 분배 대기중</b>에 있습니다.
				</div>
			</div>
			
			<table>
				<colgroup>
					<col width="217px">
				</colgroup>
				<tbody>
					<tr>
						<th>분배방식 선택<div class="miniGuideWrap" data-class="dbDistType"></div></th>
						<td>
							<ul id="fcListWrap">
								<li>
									<input type='radio' id='distTypeA' name='distMainType' value='typeA' checked>
									<label for='distTypeA'>
										<i class='fas fa-check-circle on'></i>
										<i class='far fa-circle off'></i>
										<span>순차분배</span>
									</label>
								</li>
								<li>
									<input type='radio' id='distTypeB' name='distMainType' value='typeB'>
									<label for='distTypeB'>
										<i class='fas fa-check-circle on'></i>
										<i class='far fa-circle off'></i>
										<span>그룹별분배</span>
									</label>
								</li>
							</ul>
						</td>
					</tr>
					<tr>
						<th>분배대상 선택<div class="miniGuideWrap" data-class="dbDistTarget"></div></th>
						<td>
							<ul id="fcListWrap">
								<li>
									<input type='radio' id='fcItemDistTM' name='fcListCode' class='fcListCode' value='distTM' checked>
									<label for='fcItemDistTM'>
										<i class='fas fa-check-circle on'></i>
										<i class='far fa-circle off'></i>
										<span><?=$customLabel["tm"]?>별분배</span>
									</label>
								</li>
								<li>
									<input type='radio' id='fcItemDistFC' name='fcListCode' class='fcListCode' value='distFC'>
									<label for='fcItemDistFC'>
										<i class='fas fa-check-circle on'></i>
										<i class='far fa-circle off'></i>
										<span><?=$customLabel["fc"]?>별분배</span>
									</label>
								</li>
							</ul>
						</td>
					</tr>
					<tr class="item_distTMWrap item_distWrap">
						<th><?=$customLabel["tm"]?>목록<br>(<?=$customLabel["tm"]?>명/우선순위/분배수량)</th>
						<td style="padding-bottom: 0;">
							<ul class="fcDIstInfoListWrap">
							<?php
								$fcList = [];
								$value = array(':use_yn'=>'Y');
								$query = "SELECT * FROM mt_member_team WHERE use_yn = :use_yn ORDER BY dist_sort ASC";
								$sql = list_pdo($query, $value);
								
								while($row = $sql->fetch(PDO::FETCH_ASSOC)){
							?>
								<li class='item'>
									<span class='name'><?=dhtml($row['team_name'])?><span class="lp05" style="color: #CCC;">(TM<?=$row['idx']?>)</span></span>
									<input type='hidden' value='<?=$row['idx']?>' name='tmCode[]'>
									<input type='text' class='txtBox' value='<?=$row['dist_cnt']?>' name='tmCnt_<?=$row['idx']?>' numberonly placeholder='수량'>
									<span class='sort'><?=$row['dist_sort']?>위</span>
								</li>
							<?php } ?>
							</ul>
						</td>
					</tr>
					<tr class="item_distFCWrap item_distWrap" style="display: none;">
						<th><?=$customLabel["tm"]?> 선택</th>
						<td>
							<select class="txtBox" id="selectTM" name="selectTM">
							<?php
								$fcList = [];
								$value = array(':use_yn'=>'Y');
								$query = "SELECT * FROM mt_member_team WHERE use_yn = :use_yn ORDER BY dist_sort ASC";
								$sql = list_pdo($query, $value);
								while($row = $sql->fetch(PDO::FETCH_ASSOC)){
									$thisFCList = [];
									$value = array(':use_yn'=>'Y', ':tm_code'=>"{$row['idx']}");
									$query = "SELECT * FROM mt_member WHERE use_yn = :use_yn AND tm_code = :tm_code ORDER BY dist_sort ASC";
									$sql2 = list_pdo($query, $value);
									while($row2 = $sql2->fetch(PDO::FETCH_ASSOC)){
										$thisdata = [];
										$thisdata['code'] = $row2['idx'];
										$thisdata['name'] = $row2['m_name'];
										$thisdata['sort'] = $row2['dist_sort'];
										$thisdata['cnt'] = $row2['dist_cnt'];

										array_push($thisFCList, $thisdata);
									}
									
									$fcList[$row['idx']] = $thisFCList;
							?>
								<option value="<?=$row['idx']?>"><?=$row['team_name']?></option>
							<?php } ?>
							</select>
						</td>
					</tr>
					<tr class="item_distFCWrap item_distWrap" style="display: none;">
						<th>담당자목록<br>(담당자명/우선순위/분배수량)</th>
						<td style="padding-bottom: 0;">
							<ul class="fcDIstInfoListWrap">
							</ul>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div class="guideWrap">
		<div>
			<div class="iconWrap">
				<i class="fas fa-info-circle"></i>
			</div>
			<ul class="conWrap">
				<li class="basic">- 분배수량은 <b>DB관리설정 > DB분배설정</b>에서 등록한 수량이 참고용으로 기재되어 있습니다.</li>
				<li class="basic">- DB분배 설정시 분배를 원하는 방식에 따라서 그때그때 수량을 수정하여 분배하실 수 있습니다.</li>
				<li class="basic">- <b>드래그 앤 드롭</b>으로 분배하실 <?=$customLabel["tm"]?>(혹은 <?=$customLabel["fc"]?>)의 <b>우선순위를 변경</b>하실 수 있습니다.</li>
				<li class="basic">- 분배설정값은 <b>DB관리설정 > DB분배설정</b>에서 변경이 가능합니다. <a href="#" id="goToDistPage" style="margin-left: 10px; font-weight: 500;">[분배설정 바로가기]</a></li>
			</ul>
		</div>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnMain" id="submitBtn"><i class="fas fa-share"></i>분배하기</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="dist">취소</button>
	</div>
	
	<script type="text/javascript">
		var fcList = <?=json_encode($fcList)?>;
		
		function fcListSet(){
			var code = $("#selectTM").val();
			var item = fcList[code];
			var html = "";
			
			$.each(item, function(index, value){
				html += "<li class='item'>";
				html += "<span class='name'>" + value['name'] + "<span class='lp05' style='color: #CCC;'>(FC" + value['code'] + ")</span></span>";
				html += "<input type='hidden' value='" + value['code'] + "' name='idx[]'>";
				html += "<input type='text' class='txtBox' value='" + value['cnt'] + "' name='cnt_" + value['code'] + "' numberonly placeholder='수량'>";
				html += "<span class='sort'>" + value['sort'] + "위</span>";
				html += "</li>";
			});
			
			$(".item_distFCWrap .fcDIstInfoListWrap li").remove();
			$(".item_distFCWrap .fcDIstInfoListWrap").append(html);
			$("#selectFCCode").val("");
			
			if(!html){
				$(".item_distFCWrap .fcDIstInfoListWrap").append("<li class='no'><span>담당자가 존재하지 않습니다.</span></li>");
				return false;
			}
		}
		
		$(function(){
			var totalCnt = <?=$totalCnt?>;
			var minuscnt = <?=$minuscnt?>;

			$("#overlap_yn_check").change(function(){
        		if($("#overlap_yn_check").is(":checked")){
					$("#dbTotalCnt").text(minuscnt);	
      			}else{
            		$("#dbTotalCnt").text(totalCnt);
       			}
   			});
			
			$("#submitBtn").click(function(){
				var datas = $(".writeWrap > form").serialize();
				
				/* 수량체크 */
				switch($("#distType").val()){
					case "distTM" :
						var itemCnt = 0;
						var item = $(".item_distTMWrap .txtBox");
						for(var i = 0; i < item.length; i++){
							itemCnt += Number($(item[i]).val());
						}
						
						if(itemCnt > totalCnt){
							alert("분배수량이 맞지 않습니다.\n다시 한 번 확인해주시길 바랍니다.");
							return false;
						}
						break;
					case "distFC" :
						var itemCnt = 0;
						var item = $(".item_distFCWrap input.txtBox");
						for(var i = 0; i < item.length; i++){
							itemCnt += Number($(item[i]).val());
						}
						
						if(itemCnt > totalCnt){
							alert("분배수량이 맞지 않습니다.\n다시 한 번 확인해주시길 바랍니다.");
							return false;
						}
						break;
				}
				
				$("#loadingWrap").fadeIn(350, function(){
					$.ajax({
						url : "/ajax/db/dbDistAuto",
						type : "POST",
						data : datas,
						success : function(result){
							alert("분배가 완료되었습니다.");
							parent.popupSubmitClose();
						}
					});
				});
			});
			
			fcListSet();
			$("#selectTM").change(function(){
				fcListSet();
			});
			
			$(".fcDIstInfoListWrap").sortable({
				update : function(){
					var item = $(this).find("li");
					for(var i = 0; i < item.length; i++){
						$(item[i]).find(".sort").text((i + 1) + "위");
					}
				}
			});
			
			$(".fcListCode").change(function(){
				var name = $(".fcListCode:checked").val();
				
				$(".item_distWrap").hide();
				$(".item_" + name + "Wrap").show();
				$("#distType").val(name);
			});
			
			$("#goToDistPage").click(function(e){
				e.preventDefault();
				
				parent.location.href = "/sub/db_setting/distL";
			});
			
		});
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>