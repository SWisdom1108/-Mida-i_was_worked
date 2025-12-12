<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 데이터검사
	$value = array(':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
		FROM mt_schedule MT
		WHERE use_yn = 'Y'
		AND schedule_type = 'basic'
		AND idx = :idx
	";	
	$view = view_pdo($query, $value);
	if(!$view){
		return false;
	}

	# 변수설정
	$date = date("Y-m-d", strtotime($view["s_date"]));

?>
<style>
	#popupWrap { height:auto; }
	.db_info { width: 100%; background-color: #eeeeee; font-size: 17px; line-height: 1.5; border-radius: 10px; padding: 20px; margin-bottom: 25px; }
	.db_info > .title { font-weight: 900; margin-left: 5px; }
	.db_info > .info { margin-left:20px; }
	.miniGuideWrap > div { top: -435%; }
</style>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/schedule/scheduleUP" data-callback="/sub/schedule/scheduleR?date=<?=$date?>" data-type="수정">
			<input type="hidden" name="idx" value="<?=$view["idx"]?>">
			<?php if($view['cs_name'] && $view['cs_tel']) { ?>
				<div class="db_info">
					<i class="fas fa-user-circle"></i>
					<span class="title">DB정보</span>
					<span class="info"><?=$view["cs_name"]?> (<?=$view["cs_tel"]?>)</span>
				</div>
			<?php } ?>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th>일자</th>
						<td class="lp05" colspan="3">
							<div class="sDate" style="width: 10%; min-width: 140px; margin-left: 0; float:left;">
								<input type="text" class="txtBox" name="date" id="changeDate" dateonly value=<?=$date?>>
								<i class="fas fa-calendar-alt"></i>
							</div>
						</td>
					</tr>
					<tr>
						<th class="important">시작시간</th>
						<td><input type="text" class="txtBox" name="s_time" timeonly readonly value="<?=date("H:i", strtotime($view["s_date"]))?>"></td>
						<th class="important">종료시간</th>
						<td><input type="text" class="txtBox" name="e_time" timeonly readonly value="<?=date("H:i", strtotime($view["e_date"]))?>"></td>
					</tr>
					<tr>
						<th class="important">일정구분</th>
						<td>
							<select class="txtBox" name="type_code">
								<option value="">구분선택</option>
							<?php
								$value = array(''=>'');
								$query = "SELECT * FROM mc_schedule_type WHERE use_yn = 'Y' ORDER BY sort ASC";
								$sql = list_pdo($query, $value);
								while($row = $sql->fetch(PDO::FETCH_ASSOC)){
							?>
								<option value="<?=$row["type_code"]?>" <?=($row["type_code"] == $view["type_code"]) ? "selected" : ""?>>
									<?=dhtml($row["type_name"])?>
								</option>
							<?php } ?>
							</select>
						</td>
						<th>통화여부</th>
						<td>
							<input type="checkbox" class="toggle" name="call_yn" id="call_yn" <?=($view["call_yn"] == "Y") ? "checked" : ""?>>
							<label class="toggle" for="call_yn"><div></div></label>
						</td>
					</tr>
					<tr>
						<th>일정내용</th>
						<td colspan="3">
							<textarea class="txtBox" name="memo"><?=dhtml($view["memo"])?></textarea>
						</td>
					</tr>
					<?php if ($view['noti_yn'] == 'Y') { ?> 
						<tr>
							<th>알림여부
								<div class="noti_status">
									<i class="fas fa-exclamation-circle popupOpenBtn"></i>
								</div>
							</th>
							<td>
								<input type="checkbox" class="toggle" name="noti_yn" id="noti_yn" checked>
								<label class="toggle" for="noti_yn">
									<div></div>
								</label>
							</td>
							<th>알림발송시점</th>
							<td>
								<select class="txtBox" name="noti_time" id="noti_time" value="<?=$view['noti_time']?>" style="color:#666;">
									<option <?= $view['noti_time'] == '1min' ? 'selected' : '' ?> value="1min">1분 전</option>
									<option <?= $view['noti_time'] == '5min' ? 'selected' : '' ?> value="5min">5분 전</option>
									<option <?= $view['noti_time'] == '10min' ? 'selected' : '' ?> value="10min">10분 전</option>
									<option <?= $view['noti_time'] == '30min' ? 'selected' : '' ?> value="30min">30분 전</option>
									<option <?= $view['noti_time'] == '1hrs' ? 'selected' : '' ?> value="1hrs">1시간 전</option>
									<option <?= $view['noti_time'] == '1day' ? 'selected' : '' ?> value="1day">1일 전</option>
								</select>
							</td>
						</tr>
					<?php } else { ?>
						<tr>
							<th>알림여부
								<div class="noti_status">
									<i class="fas fa-exclamation-circle popupOpenBtn"></i>
								</div>
							</th>
							<td>
								<input type="checkbox" class="toggle" name="noti_yn" id="noti_yn">
								<label class="toggle" for="noti_yn">
									<div></div>
								</label>
							</td>
							<th>알림발송시점</th>
							<td>
								<select class="txtBox" name="noti_time" id="noti_time" disabled>
									<option value="1min">1분 전</option>
									<option value="5min">5분 전</option>
									<option value="10min">10분 전</option>
									<option value="30min">30분 전</option>
									<option value="1hrs">1시간 전</option>
									<option value="1day">1일 전</option>
								</select>
							</td>
						</tr>
					<?php } ?>
					<tr>
						<?php if($user["auth_code"] == "001" || $user["auth_code"] == "002"){ ?>
							<th>전체공유여부
								<div class="miniGuideWrap" data-class="full_sharing"></div>
							</th>
							<td colspan="3">
								<input type="checkbox" class="toggle" name="share_all_yn" id="share_all_yn" <?=($view["share_all_yn"] == "Y") ? "checked" : ""?>>
								<label class="toggle" for="share_all_yn"><div></div></label>
							</td>
						<?php } ?>
						
						<?php if($user["auth_code"] == "004"){ ?>
							<th>팀내공유여부
								<div class="miniGuideWrap" data-class="team_sharing"></div>
							</th>
							<td colspan="3">
								<input type="checkbox" class="toggle" name="share_tm_yn" id="share_tm_yn" <?=($view["share_tm_yn"] == "Y") ? "checked" : ""?>>
								<label class="toggle" for="share_tm_yn"><div></div></label>
							</td>
						<?php } ?>
						
						<?php if($user["auth_code"] == "005" || $user["auth_code"] == "002"){ ?>
							<td></td>
							<td></td>
						<?php } ?>

					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnBlack submitBtn" data-target="write">완료</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="write">취소</button>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			$('input:text[timeonly]').timepicker({
				timeFormat: 'HH:mm',
				interval: 30,
				minTime: '9',
				maxTime: '21',
				startTime: '09:00',
				dynamic: false,
				dropdown: true,
				scrollbar: true
			});
			
		});

		// 알림발송시점 on/off 기능
		$('#noti_yn').change(function() {
			if ($(this).is(':checked')) {
				$('#noti_time').prop('disabled', false);
				$('#noti_time').css('color', '#666');
			} else {
				$('#noti_time').prop('disabled', true);
				$('#noti_time').css('color', '#cccccc');
				$('#noti_time').val();
			}
		});

		// 알림여부 가이드 on/off 기능
		$(document).on("click", ".popupOpenBtn", function() {
			var guidePopup = $(".guidePopup", window.parent.document);
			guidePopup.toggle();
		});

	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>