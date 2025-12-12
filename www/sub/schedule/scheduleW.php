<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 변수설정
	$date = ($_GET["date"]) ? $_GET["date"] : date("Y-m-d");

	# 201130 기본정보에 추가내용
	if($_SESSION["scheduleGetDB"]){
		$db_idx = $_SESSION["scheduleGetDB"];
		$value = array(':idx'=>$_SESSION['scheduleGetDB']);
		$query = "SELECT cs_name, cs_tel FROM mt_db WHERE idx = :idx";
		$view = view_pdo($query, $value);
		$writeTitleContents = " {$view["cs_name"]} ({$view["cs_tel"]})";
		// unset($_SESSION["scheduleGetDB"]);
	}

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
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/schedule/scheduleWP" data-callback="/sub/schedule/scheduleR?date=<?=$date?>" data-type="등록">
			<input type="hidden" name="db_idx" value="<?=(isset($db_idx))? $db_idx : "";?>">
			<input type="hidden" name="cs_name" value="<?=(isset($view["cs_name"]))? $view["cs_name"] : "";?>">
			<input type="hidden" name="cs_tel" value="<?=(isset($view["cs_tel"]))? $view["cs_tel"] : "";?>">
			<?php if($_SESSION["scheduleGetDB"]) { ?>
				<div class="db_info">
					<i class="fas fa-user-circle"></i>
					<span class="title">DB정보</span>
					<span class="info"><?=$writeTitleContents?></span>
				</div>
			<?php unset($_SESSION["scheduleGetDB"]); } ?>
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
							<input type="text" class="txtBox" name="date" id="changeDate" value="<?=$date?>" dateonly>
						</td>
					</tr>
					<tr>
						<th class="important">시작시간</th>
						<td><input type="text" class="txtBox" name="s_time" timeonly value="<?=date("H:i")?>"></td>
						<th class="important">종료시간</th>
						<td><input type="text" class="txtBox" name="e_time" timeonly value="<?=date("H:i", strtotime("+ 1 hour"))?>"></td>
					</tr>
					<tr>
						<th class="important">일정구분</th>
						<td colspan="1">
							<select class="txtBox txtBox100" name="type_code">
								<option value="">구분선택</option>
							<?php
								$value = array(''=>'');
								$query = "SELECT * FROM mc_schedule_type WHERE use_yn = 'Y' ORDER BY sort ASC";
								$sql = list_pdo($query, $value);
								while($row = $sql->fetch(PDO::FETCH_ASSOC)){
							?>
								<option value="<?=$row["type_code"]?>"><?=dhtml($row["type_name"])?></option>
							<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th>일정내용</th>
						<td colspan="3">
							<textarea class="txtBox" name="memo"></textarea>
						</td>
					</tr>
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
					<tr>
						<?php if($user["auth_code"] == "001" || $user["auth_code"] == "002"){ ?>
							<th>전체공유여부
								<div class="miniGuideWrap" data-class="full_sharing"></div>
							</th>
							<td colspan="3">
								<input type="checkbox" class="toggle" name="share_all_yn" id="share_all_yn">
								<label class="toggle" for="share_all_yn">
									<div></div>
								</label>
							</td>
						<?php } ?>
						
						<?php if($user["auth_code"] == "004"){ ?>
							<th>팀내공유여부
								<div class="miniGuideWrap" data-class="team_sharing"></div>
							</th>
							<td colspan="3">
								<input type="checkbox" class="toggle" name="share_tm_yn" id="share_tm_yn">
								<label class="toggle" for="share_tm_yn">
									<div></div>
								</label>
							</td>
						<?php } ?>
						
						<?php if($user["auth_code"] == "005" || $user["auth_code"] == "002"){ ?>
							<td></td>
							<td></td>
						<?php } ?>
					</tr>
				</tbody>
			</table>
			</div>
		</form>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnBlack submitBtn" data-target="write">완료</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="write">취소</button>
	</div>

	<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>

	<script type="text/javascript">
		$(function(){
			
			$(document).on("focus focusin focusout change keyup keydown", "#changeDate", function(){
				$("#writeFrm").attr("data-callback", "/sub/schedule/scheduleR?date=" + $(this).val());
			});
			
			$('input:text[timeonly]').timepicker({
				timeFormat: 'HH:mm',
				interval: 60,
				minTime: '0',
				maxTime: '23',
				startTime: '00:00',
				dynamic: false,
				dropdown: true,
				scrollbar: true
			});

			// 알림발송시점 on/off 기능
			$('#noti_yn').change(function() {
				if ($(this).is(':checked')) {
					$('#noti_time').prop('disabled', false);
					$('#noti_time').css('color', '#666');
				} else {
					$('#noti_time').prop('disabled', true);
					$('#noti_time').css('color', '#cccccc');
					$('#noti_time').val('1min');
				}
			});

			// 알림여부 가이드 on/off 기능
			$(document).on("click", ".popupOpenBtn", function() {
				var guidePopup = $(".guidePopup", window.parent.document);
				guidePopup.toggle();
			});

		});
	</script>