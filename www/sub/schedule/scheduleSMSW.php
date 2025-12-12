<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 변수설정
	$date = ($_GET["date"]) ? $_GET["date"] : date("Y-m-d");

	# 템플릿 목록
	$value = array(''=>'');
	$query = "SELECT * FROM mt_sms_template WHERE use_yn = 'Y' AND basic_yn = 'N' ORDER BY idx DESC";
	$templateList = list_pdo($query, $value);

?>

	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/schedule/scheduleSMSWP" data-callback="/sub/schedule/scheduleR?date=<?=$date?>" data-type="등록">
			<input type="hidden" name="type" value="일정문자발송">
			<div class="tit">기본정보</div>
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
						<td class="lp05"><input type="text" class="txtBox" name="date" id="changeDate" value="<?=$date?>" dateonly></td>
						<th class="important">발송시간</th>
						<td><input type="text" class="txtBox" name="s_time" timeonly value="<?=date("H:i", strtotime("+ 10 minute"))?>"></td>
					</tr>
					<tr>
						<th class="important">수신자명</th>
						<td><input type="text" class="txtBox" name="cs_name" value="<?=$_GET["cs_name"]?>"></td>
						<th class="important">수신자연락처</th>
						<td><input type="text" class="txtBox" name="cs_tel" numonly value="<?=$_GET["cs_tel"]?>"></td>
					</tr>
					<tr>
						<th>템플릿 선택</th>
						<td colspan="3">
							<select class="txtBox" id="template">
								<option value="0">템플릿 불러오기</option>
							<?php while($row = $templateList->fetch(PDO::FETCH_ASSOC)){ ?>
								<option value="<?=$row["idx"]?>"><?=$row["title"]?></option>
							<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th>문자내용</th>
						<td colspan="3">
							<textarea class="txtBox" name="memo" id="memo"></textarea>
							<p id="contentsByte" style="width: 100%; float: left; margin-top: 5px; text-align: left; letter-spacing: -0.5px;">0 Byte</p>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div class="guideWrap" style="margin-top: 20px;">
		<div>
			<div class="iconWrap">
				<i class="fas fa-info-circle"></i>
			</div>
			<ul class="conWrap">
				<li class="basic">- 템플릿 내용의 길이가 <b>80 Byte</b>보다 클 경우 MMS로 전송이 됩니다.</li>
				<li class="basic">- MMS의 내용길이 제한은 <b>2000 Byte</b>입니다.</li>
				<li class="basic">- 템플릿 내용에 <b>#{고객명}</b>을 입력하시면 발송시 수신자명으로 바뀌어 전송됩니다.</li>
			</ul>
		</div>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnBlack submitBtn" data-target="write">완료</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="write">취소</button>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			$(document).on("focus focusin focusout change keyup keydown", "#changeDate", function(){
				$("#writeFrm").attr("data-callback", "/sub/schedule/scheduleR?date=" + $(this).val());
			});
			
			$("#template").change(function(){
				$.ajax({
					url : "/ajax/schedule/getSmsTemplate",
					type : "POST",
					data : {
						idx : $(this).val()
					},
					success : function(con){
						$("#memo").val(con);
						$("#template").val("0");
					}
				})
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
			
			$(document).on("focus focusin focusout change keyup keydown", "#memo", function(){
				$("#contentsByte").text(byteCheck($(this)) + " Byte");
			});
			
		});
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>