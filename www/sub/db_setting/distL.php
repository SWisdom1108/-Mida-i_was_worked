<?php

	# 메뉴설정
	$secMenu = "dist";

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";
	
	# 콘텐츠설정
	$contentsTitle = "DB분배설정";
	$contentsInfo = "{$customLabel["tm"]}별 및 {$customLabel["fc"]}들의 DB자동분배에 대한 우선순위 및 수량을 설정하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "DB분배설정");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	$value = array(''=>'');
	$query = "SELECT * FROM mt_site_info";
	$view = view_pdo($query, $value);

?>
	
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="modFrm" data-ajax="/ajax/db_setting/distUP" data-callback="/sub/db_setting/distL" data-type="저장">
			<div class="tit">
				<span style="float: left; margin-right: 15px;"><?=$customLabel["tm"]?>별 분배설정</span>
				<input type="checkbox" class="toggle" name="auto_dist_team" id="auto_dist_team" <?=($view['auto_dist_yn'] == "T") ? "checked" : ""?> >
				<label class="toggle" for="auto_dist_team" style="float: right;"><div></div></label>
				<span style="float: right; margin-right: 15px;">자동 분배</span>
			</div> 
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>

				<tbody>
					<tr>
						<th><?=$customLabel["tm"]?>분배 설정<br>(<?=$customLabel["tm"]?>명/우선순위/기본수량)</th>
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
									<input type='hidden' value='<?=$row['dist_sort']?>' name='tm_sort_<?=$row['idx']?>' class="sortInput">
									<input type='text' class='txtBox' value='<?=$row['dist_cnt']?>' name='tm_cnt_<?=$row['idx']?>' numberonly placeholder='수량'>
									<span class='sort'><?=$row['dist_sort']?>위</span>
								</li>
							<?php } ?>
							</ul>
						</td>
					</tr>
				</tbody>
			</table>
			
			<div class="tit">
				<span>담당자별 분배설정</span>
				<input type="checkbox" class="toggle" name="auto_dist_fc" id="auto_dist_fc" <?=($view['auto_dist_yn'] == "F") ? "checked" : ""?> >
				<label class="toggle" for="auto_dist_fc" style="float: right;"><div></div></label>
				<span style="float: right; margin-right: 15px;">자동 분배</span>
			</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>

				<tbody>
					<tr>
						<th><?=$customLabel["tm"]?> 선택</th>
						<td>
							<select class="txtBox" id="selectTM" name="selectTM">
								<option value="0000">전체</option>
							<?php
								$fcList = [];
								$allFcList = [];
								
								$value = array(':use_yn'=>'Y');
								$query = "SELECT * FROM mt_member WHERE use_yn = :use_yn AND auth_code > 3 ORDER BY dist_sort ASC";
								$fc = list_pdo($query, $value);
								
								while($fcData = $fc->fetch(PDO::FETCH_ASSOC)){
									$thisdata = [];
									$thisdata['code'] = $fcData['idx'];
									$thisdata['name'] = $fcData['m_name'];
									$thisdata['sort'] = $fcData['dist_sort'];
									$thisdata['cnt'] = $fcData['dist_cnt'];
									$thisdata['dist_yn'] = $fcData['dist_yn'];
									$thisdata['idx'] = '0000';
									array_push($allFcList, $thisdata);
								}
								$fcList['0000'] = $allFcList;


								$value = array(':use_yn'=>'Y');
								$query = "SELECT * FROM mt_member_team WHERE use_yn = :use_yn ORDER BY dist_sort ASC";
								$sql = list_pdo($query, $value);
								while($row = $sql->fetch(PDO::FETCH_ASSOC)){
									$thisFCList = [];
									$value = array(':tm_code'=> $row['idx'],':use_yn'=>'Y');
									$query = "SELECT * FROM mt_member WHERE use_yn = :use_yn AND tm_code = :tm_code ORDER BY dist_sort ASC";
									$sql2 = list_pdo($query, $value);
									while($row2 = $sql2->fetch(PDO::FETCH_ASSOC)){
										$thisdata = [];
										$thisdata['code'] = $row2['idx'];
										$thisdata['name'] = $row2['m_name'];
										$thisdata['sort'] = $row2['dist_sort'];
										$thisdata['cnt'] = $row2['dist_cnt'];
										$thisdata['dist_yn'] = $row2['dist_yn'];
										$thisdata['idx'] = $row['idx'];

										array_push($thisFCList, $thisdata);
									}
									
									$fcList[$row['idx']] = $thisFCList;
							?>
								<option value="<?=$row['idx']?>" <?=$row['idx'] == $view['auto_dist_team'] ? "selected" : "" ?>><?=dhtml($row['team_name'])?></option>
							<?php } ?>
							</select>
						</td>
					</tr>
					<?php foreach($fcList as $code => $team){ $teamCnt++; ?>
						<tr style="display: <?=($team[0]['idx'] == $view['auto_dist_team'] ) ? "table-row" : "none"?>;" id="fcList_<?=$code?>" class="fcListWrap">
							<th>담당자<br>(담당자명/우선순위/기본수량)</th>
							<td style="padding-bottom: 0;">
								<ul class="fcDIstInfoListWrap">
								<?php foreach($team as $fc){ ?>
									<li class='item'>
										<span class='name'><?=$fc['name']?><span class="lp05" style="color: #CCC;">(FC<?=$fc['code']?>)</span></span>
										<input type='hidden' value='<?=$fc['code']?>' name='idx[]'>
										<input type='hidden' value='<?=$fc['sort']?>'  data-fc='<?=$fc['code']?>' name='sort_<?=$fc['code']?>' class="sortInput fc_sort">
										<input type='text' class='txtBox fc_cnt' value='<?=$fc['cnt']?>' data-fc='<?=$fc['code']?>' name='cnt_<?=$fc['code']?>' numberonly placeholder='수량'>
										<span class='sort'><?=$fc['sort']?>위</span>
									</li>
								<?php } ?>
								</ul>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>

			<?php if($pm_module == 'Y'){?>
				<div class="tit">
					<span>생산업체별 분배설정</span>
					<input type="checkbox" class="toggle" name="auto_dist_pm" id="auto_dist_pm" <?=($view['auto_dist_yn'] == "P") ? "checked" : ""?> >
					<label class="toggle" for="auto_dist_pm" style="float: right;"><div></div></label>
					<span style="float: right; margin-right: 15px;">자동 분배</span>
				</div>
				<table>
					<colgroup>
						<col width="20%">
						<col width="80%">
					</colgroup>

					<tbody>
						<?php
							$value = array(':use_yn'=>'Y', ':auth_code'=>'003');
							$query = "SELECT * FROM mt_member_cmpy WHERE use_yn = :use_yn AND auth_code = :auth_code";
							$pm_sql = list_pdo($query, $value);
							while($pm_list = $pm_sql->fetch(PDO::FETCH_ASSOC)){
						?>
							<tr>
								<th>생산업체(<?=dhtml($pm_list['company_name'])?>) - 팀 매칭</th>
								<input type="hidden" name="pm_code[]" value="<?=$pm_list['idx']?>">
								<td>
									<select class="txtBox select_pm_TM" name="select_pm_TM[]" data-pm="<?=$pm_list['idx']?>">
										<option value="0000">전체</option>
									<?php
									$fcList = [];
									$allFcList = [];
									
									$value = array(':use_yn'=>'Y');
									$query = "SELECT *, (SELECT m_name FROM mt_member WHERE idx = MT.m_idx) as m_name FROM mt_member_pmDist MT WHERE use_yn = :use_yn AND pm_code = {$pm_list['idx']} ORDER BY dist_sort ASC";
									$fc = list_pdo($query, $value);
									
									while($fcData = $fc->fetch(PDO::FETCH_ASSOC)){
										$thisdata = [];
										$thisdata['code'] = $fcData['idx'];
										$thisdata['m_idx'] = $fcData['m_idx'];
										$thisdata['name'] = $fcData['m_name'];
										$thisdata['sort'] = $fcData['dist_sort'];
										$thisdata['cnt'] = $fcData['dist_cnt'];
										$thisdata['dist_yn'] = $fcData['dist_yn'];
										$thisdata['idx'] = '0000';
										array_push($allFcList, $thisdata);
									}
									$fcList['0000'] = $allFcList;


									$value = array(':use_yn'=>'Y');
									$query = "SELECT * FROM mt_member_team WHERE use_yn = :use_yn ORDER BY dist_sort ASC";
									$sql = list_pdo($query, $value);
									while($row = $sql->fetch(PDO::FETCH_ASSOC)){
										$thisFCList = [];
										$value = array(':tm_code'=> $row['idx'],':use_yn'=>'Y');
										$query = "SELECT *, (SELECT m_name FROM mt_member WHERE idx = MT.m_idx) as m_name FROM mt_member_pmDist MT WHERE use_yn = :use_yn AND (SELECT tm_code FROM mt_member WHERE idx = MT.m_idx) = :tm_code AND pm_code = {$pm_list['idx']} ORDER BY dist_sort ASC";
										$sql2 = list_pdo($query, $value);
										while($row2 = $sql2->fetch(PDO::FETCH_ASSOC)){
											$thisdata = [];
											$thisdata['code'] = $row2['idx'];
											$thisdata['m_idx'] = $row2['m_idx'];
											$thisdata['name'] = $row2['m_name'];
											$thisdata['sort'] = $row2['dist_sort'];
											$thisdata['cnt'] = $row2['dist_cnt'];
											$thisdata['dist_yn'] = $row2['dist_yn'];
											$thisdata['idx'] = $row['idx'];

											array_push($thisFCList, $thisdata);
										}
										
										$fcList[$row['idx']] = $thisFCList;
								?>
									<option value="<?=$row['idx']?>" <?=$row['idx'] == $pm_list['auto_dist_team'] ? "selected" : "" ?>><?=dhtml($row['team_name'])?></option>
								<?php } ?>
									</select>
								</td>
							</tr>
							<?php foreach($fcList as $code => $team){ $teamCnt++; ?>
								<tr style="display: <?=($team[0]['idx'] == $pm_list['auto_dist_team'] ) ? "table-row" : "none"?>;" id="fcPmList_<?=$code?>_<?=$pm_list['idx']?>" class="fcPmListWrap_<?=$pm_list['idx']?>">
									<th>담당자<br>(담당자명/우선순위/기본수량)</th>
									<td style="padding-bottom: 0;">
										<ul class="fcDIstInfoListWrap">
										<?php foreach($team as $fc){ ?>
											<li class='item'>
												<span class='name'><?=$fc['name']?><span class="lp05" style="color: #CCC;">(FC<?=$fc['m_idx']?>)</span></span>
												<input type='hidden' value='<?=$fc['code']?>' name='pm_idx[]'>
												<input type='hidden' value='<?=$fc['sort']?>'  data-fc='<?=$fc['code']?>' name='pm_sort_<?=$fc['code']?>' class="sortInput fc_sort">
												<input type='text' class='txtBox fc_cnt' value='<?=$fc['cnt']?>' data-fc='<?=$fc['code']?>' name='pm_cnt_<?=$fc['code']?>' numberonly placeholder='수량'>
												<span class='sort'><?=$fc['sort']?>위</span>
											</li>
										<?php } ?>
										</ul>
									</td>
								</tr>
							<?php } ?>
						<?php }?>
					</tbody>
				</table>
			<?php }else {?>
				<div class="tit">
					<span>생산업체별 분배설정</span>
					<input type="checkbox" class="toggle" name="auto_dist_pm" id="auto_dist_pm" <?=($view['auto_dist_yn'] == "P") ? "checked" : ""?> >
					<label class="toggle" for="auto_dist_pm" style="float: right;"><div></div></label>
					<span style="float: right; margin-right: 15px;">자동 분배</span>
				</div>
				<table>
					<colgroup>
						<col width="20%">
						<col width="80%">
					</colgroup>

					<tbody>
						<?php
							$value = array(':use_yn'=>'Y', ':auth_code'=>'003');
							$query = "SELECT * FROM mt_member_cmpy WHERE use_yn = :use_yn AND auth_code = :auth_code";
							$pm_sql = list_pdo($query, $value);
							while($pm_list = $pm_sql->fetch(PDO::FETCH_ASSOC)){
						?>
							<tr>
								<th>생산업체(<?=dhtml($pm_list['company_name'])?>) - 팀 매칭</th>
								<input type="hidden" name="pm_code[]" value="<?=$pm_list['idx']?>">
								<td>
									<select class="txtBox" id="select_pm_TM" name="select_pm_TM[]">
										<option value="">팀 선택</option>
									<?php
										$fcList = [];
										$value = array(':use_yn'=>'Y');
										$query = "SELECT * FROM mt_member_team WHERE use_yn = :use_yn ORDER BY dist_sort ASC";
										$sql = list_pdo($query, $value);
										while($row = $sql->fetch(PDO::FETCH_ASSOC)){
											$thisFCList = [];
											// $sql2 = list_sql("SELECT * FROM mt_member WHERE use_yn = 'Y' AND tm_code = '{$row['idx']}' ORDER BY dist_sort ASC");
											$fcList[$row['idx']] = $thisFCList;
									?>
										<option value="<?=$row['idx']?>" <?=$row['idx'] == $pm_list['auto_dist_team'] ? "selected" : "" ?>><?=dhtml($row['team_name'])?></option>
									<?php } ?>
									</select>
								</td>
							</tr>
						<?php }?>
					</tbody>
				</table>
			<?php }?>
		</form>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnBlack big submitBtn" data-target="mod"><i class="far fa-check-circle"></i>저장하기</button>
		</div>
	</div>
	
	<script type="text/javascript">
		$(function(){

			$("#selectTM").change(function(){
				var code = $(this).val();
				
				$(".fcListWrap").hide();
				$("#fcList_" + code).show();
			});

			$(".select_pm_TM").change(function() {
				var code = $(this).val();
				var data = $(this).data("pm");
				
				$(".fcPmListWrap_" + data).hide();

				$("#fcPmList_" + code + "_" + data).show();
			})

			$("#auto_dist_fc").change(function(){
				$("#auto_dist_team").prop("checked",false);
				$("#auto_dist_pm").prop("checked",false);
			});
			$("#auto_dist_pm").change(function(){
				$("#auto_dist_fc").prop("checked",false);
				$("#auto_dist_team").prop("checked",false);
			});
			$("#auto_dist_team").change(function(){
				$("#auto_dist_fc").prop("checked",false);
				$("#auto_dist_pm").prop("checked",false);
			});

			$(".fcDIstInfoListWrap").sortable({
				update : function(){
					var item = $(this).find("li");
					for(var i = 0; i < item.length; i++){
						$(item[i]).find(".sort").text((i + 1) + "위");
						$(item[i]).find(".sortInput").val(i + 1);

						var sort_val = $(item[i]).find(".sortInput").val();
						var fc_data = $(item[i]).find(".sortInput").data("fc");
						$(".fc_sort[data-fc='"+fc_data+"']").val(sort_val);
					}
				}
			});

			$(".fc_toggle").change(function() {
				var fc_status = $(this).prop("checked");
				var fc_data = $(this).data("fc");

				$(".fc_toggle[data-fc='"+fc_data+"']").prop("checked",fc_status);

			})

			$(".fc_cnt").change(function() {
				var cnt_val = $(this).val();
				var fc_data = $(this).data("fc");

				$(".fc_cnt[data-fc='"+fc_data+"']").val(cnt_val);

			})

			$(document).on("change", ".fc_sort", function() {
				var sort_val = $(this).val();
				var fc_data = $(this).data("fc");

				$(".fc_sort[data-fc='"+fc_data+"']").val(sort_val);


			})

			
		});


		
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>