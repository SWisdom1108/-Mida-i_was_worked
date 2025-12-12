<?php
# 메뉴 접근 권한설정
# 001(최고관리자) 002(관리자) 003(생산마스터)
# 004(팀마스터) 005(영업자)
$menuAuth = ["001", "002", "004", "005", "006"];

# 공용 헤더 가져오기
include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

# 권한에 따른 추가 쿼리문
switch ($user['auth_code']) {
	case "004":
		$andQuery .= " AND tm_code = '{$user['tm_code']}'";
		break;
	case "005":
		$andQuery .= " AND tm_code = '{$user['tm_code']}' AND m_idx = '{$user['idx']}'";
		break;
}

# 데이터 정보추출

$value = array(':use_yn' => 'Y', ':dist_code' => '002', ':idx' => $_GET['idx']);
$query = "		
		SELECT MT.*
		FROM mt_db MT
		WHERE use_yn = :use_yn
		AND (dist_code = :dist_code OR dist_code = '003')
		AND idx = :idx
		{$andQuery}
	";
$view = view_pdo($query, $value);

if (!$view) {
	www("/sub/error/popup");
}

$value = array('' => '');
$query = "SELECT * FROM mc_db_cs_status WHERE use_yn = 'Y' AND sms_yn = 'N' ORDER BY sort ASC";
$csStatusList = list_pdo($query, $value);

if ($_GET["csStatusCode"]) {
	$andQuerys .= " AND status_code = '{$_GET["csStatusCode"]}'";
}


if ($_GET['gradeCode']) {
	$andQuerys .= "AND grade_code = {$_GET['gradeCode']}";
}


# 상담기록

$value = array(':use_yn' => 'Y', ':db_idx' => "{$view['idx']}");
$query = "
		SELECT COUNT(*) as totalCnt
		FROM mt_db_cs_log MT
		WHERE use_yn = :use_yn
		AND db_idx = :db_idx
		ORDER BY idx DESC
	";

$totalCnt = view_pdo($query, $value)['totalCnt'];


$query = "
		SELECT MT.*
			, ( SELECT status_name FROM mc_db_cs_status WHERE MT.status_code = status_code ) AS status_name
			, ( SELECT number_yn FROM mc_db_cs_status WHERE MT.status_code = status_code ) AS number_yn
			, ( SELECT color FROM mc_db_cs_status WHERE MT.status_code = status_code ) AS color
			, ( SELECT number_label FROM mc_db_cs_status WHERE MT.status_code = status_code ) AS number_label
			, ( SELECT m_name FROM mt_member WHERE MT.reg_idx = idx ) AS m_name
			, ( SELECT m_id FROM mt_member WHERE MT.reg_idx = idx ) AS m_id
		FROM mt_db_cs_log MT
		WHERE use_yn = :use_yn
		AND db_idx = :db_idx
		{$andQuerys}
		ORDER BY idx DESC
	";
$cs = list_pdo($query, $value);

# 컬럼 정리
$columnCnt = 0;
$columnArr = [];
$value = array(':use_yn' => 'Y');
$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = :use_yn
		AND list_yn = 'Y'
		ORDER BY sort ASC
	";
$columnData = list_pdo($query, $value);

while ($row = $columnData->fetch(PDO::FETCH_ASSOC)) {
	$columnCnt++;

	$thisdatas = [];
	$thisdatas['name'] = $row['column_name'];
	$thisdatas['code'] = $row['column_code'];
	$thisdatas['type'] = $row['column_type'];

	$columnArr[$columnCnt] = $thisdatas;
}

# 메인번호 가져오기
$value = array(':use_yn' => 'Y', ':main_yn' => 'Y');
$query = "
		SELECT sent_tel FROM mt_sms_tel WHERE use_yn = :use_yn AND main_yn = :main_yn
	";

$mainTel = view_pdo($query, $value)["sent_tel"];
$value = array('' => '');
$query = "SELECT * FROM mc_db_grade_info WHERE use_yn = 'Y' ORDER BY grade_code ASC";
$grade = list_pdo($query, $value);

# 오늘일자
$year = date("Y");
$month = date("m");
$day = date("d");

?>

<div class="dbCheckDataInfoWrap">
	<div class="titWrap">
		<div class="left">
			<i class="fas fa-arrow-circle-right"></i>
			D-<?= $view['idx'] ?>
		</div>
		<div class="right">
			<div class="value" style="color: <?= $site["main_color"] ?>; border: 1px solid <?= $site["main_color"] ?>; cursor: pointer; margin-right: 5px;" onclick="parent.www('/sub/schedule/scheduleL?year=<?= $year ?>&month=<?= $month ?>&day=<?= $day ?>&db=<?= $view["idx"] ?>');">
				<i class="fas fa-arrow-circle-right" style="margin-right: 5px;"></i>
				일정등록하기
			</div>

			<div class="value checkTel" style="background-color: <?= $site["main_color"] ?>; cursor: pointer;" <?php if ($mainTel) { ?>onclick="parent.www('/sub/schedule/scheduleL?year=<?= $year ?>&month=<?= $month ?>&day=<?= $day ?>&cs_name=<?= $view["cs_name"] ?>&cs_tel=<?= $view["cs_tel"] ?>');"> <?php }
																																																																								?>
			<i class="fas fa-arrow-circle-right" style="margin-right: 5px;"></i>
			SMS전송하기
			</div>
		</div>
	</div>

	<div class="infoWrap">
		<ul>
			<li>
				<span class="label">이름</span>
				<span class="value"><?= $view['cs_name'] ?></span>
			</li>
			<li>
				<span class="label">연락처</span>
				<span class="value lp05"><?= $view['cs_tel'] ?></span>
			</li>
			<?php foreach ($columnArr as $val) { ?>
				<li>
					<span class="label"><?= $val['name'] ?></span>
					<span class="value lp05"><?php
												if ($val['type'] == "file") {
													if ($view["{$val['code']}"]) {
														$value = explode('@#@#', $view["{$val['code']}"]);
														echo "<a href='/upload/db_etc/{$value[0]}' class='db_csdwon' download='{$value[1]}'>{$value[1]}<i class=\"fas fa-download\"></i></a>";
													} else {
														echo "-";
													}
												} else {
													echo ($view["{$val['code']}"]) ? $view["{$val['code']}"] : "-";
												}
												?></span>
				</li>
			<?php } ?>
		</ul>
	</div>
</div>

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<div class="writeWrap">
	<form enctype="multipart/form-data">
		<input type="hidden" name="db_idx" value="<?= $view['idx'] ?>">
		<input type="hidden" name="cs_name" value="<?= $view['cs_name'] ?>">
		<input type="hidden" name="cs_tel" value="<?= $view['cs_tel'] ?>">

		<ul class="listMiniWriteWrap">
			<li style="width: 100%; margin-bottom: 5px; ">
				<select class="txtBox" name="status_code" style="width: 20%;" id="statusCode">
					<option value="">상담상태선택</option>
					<?php
					# 추가 쿼리문
					$calculateList = [];
					$value = array(':use_yn' => 'Y', ':sms_yn' => 'N');
					$query = "
							SELECT MT.*
							FROM mc_db_cs_status MT
							WHERE use_yn = :use_yn
							AND sms_yn = :sms_yn
							ORDER BY sort ASC
						";
					$sql = list_pdo($query, $value);
					while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
						$value = array(':use_yn' => 'Y', ':db_idx' => $view['idx']);
						$query = "SELECT status_code FROM mt_db_cs_log WHERE use_yn = :use_yn AND db_idx = :db_idx ORDER BY idx DESC limit 0,1";
						$lastCsStatus = view_pdo($query, $value)['status_code'];
						if ($row["number_yn"] == "Y") {
							array_push($calculateList, $row["status_code"]);
						}
					?>
						<option value="<?= $row['status_code'] ?>" <?= ($row['status_code'] == $lastCsStatus) ? "selected" : "" ?>><?= dhtml($row['status_name']) ?></option>
					<?php } ?>
				</select>
				<select name="grade_code" class="txtBox" style="width:20%;" id="gradeCode">
					<option value="">고객등급선택</option>
					<?php while ($row = $grade->fetch(PDO::FETCH_ASSOC)) { ?>
						<option value="<?= $row["grade_code"] ?>" <?= ($row["grade_code"] == $view['grade_code']) ? "selected" : "" ?>><?= dhtml($row["grade_name"]) ?></option>
					<?php } ?>
				</select>
				
				<!-- 20251203 차현우 일정등록 작업 -->
				<div class="scheduleBtn">
					<span style="margin-left: 15px">일정등록</span>
					<input type="checkbox" class="toggle" id="scheduleChk" name="scheduleChk">
					<label class="toggle" for="scheduleChk" style="margin: 3px 5px; margin-right: 15px;">
						<div></div>
					</label>
				</div>
			</li>
			
			<li style="width: 100%; margin-bottom: 5px;">
				<div style="width: 65%;" class="left">
					<input type="text" class="txtBox" name="memo" id="csMemo" placeholder="상담내용" onkeyup="enterkey()">
					<input type="text" style="display:none;" />
				</div>
				<div style="width: 20%;" class="left">
					<input type="file" class="getFileName" id="csFile" data-target="cs" name="file">
					<label for="csFile" class="typeBtn btnGray01" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%; padding: 0 5px; font-size: 13px; font-weight: 500;">
						<i class="fas fa-save"></i>
						<span id="csFileName" class="lp05">첨부파일업로드</span>
					</label>
				</div>
				<div style="width: 15%;" class="left">
					<button type="button" class="typeBtn" style="width: 100%;" id="csSubmitBtn">등록</button>
				</div>
			</li>

			<li style="display:none;" class="regSchedule">
				<!-- 시작일 -->
				<div class="sDate" style="width: 10%; min-width: 140px; margin-left: 0; float:left;">
					<input type="text" class="txtBox" name="date" id="changeDate" dateonly placeholder="시작일">
					<i class="fas fa-calendar-alt"></i>
				</div>
				<!-- 시작시간 -->
				<input type="text" class="txtBox" name="s_time" style="width: 10%; min-width: 140px; margin-left: 5px;" timeonly readonly placeholder="시작시간" autocomplete="off">
				<!-- 종료시간 -->
				<input type="text" class="txtBox" name="e_time" style="width: 10%; min-width: 140px; margin-left: 5px;" timeonly readonly placeholder="종료시간" autocomplete="off">
				
				<select class="txtBox txtBox100" name="type_code" style="width: 100px; margin-left: 5px;">
					<option value="">일정구분</option>
					<?php
						$value = array(''=>'');
						$query = "SELECT * FROM mc_schedule_type WHERE use_yn = 'Y' ORDER BY sort ASC";
						$sql = list_pdo($query, $value);
						while($row = $sql->fetch(PDO::FETCH_ASSOC)){
					?>
					<option value="<?=$row["type_code"]?>"><?=dhtml($row["type_name"])?></option>
					<?php } ?>
				</select>

				<span style="margin-left: 15px; widith: 60px;">알림등록</span>
				<input type="checkbox" class="toggle" name="noti_yn" id="noti_yn">
				<label class="toggle" for="noti_yn" style="margin: 3px 5px; margin-right: 15px;">
					<div></div>
				</label>

				<select class="txtBox" name="noti_time" id="noti_time" style="width: 10%; margin-left: 5px; min-width: 100px; color: #666; display:none;">
					<option value="1min">1분 전</option>
					<option value="5min">5분 전</option>
					<option value="10min">10분 전</option>
					<option value="30min">30분 전</option>
					<option value="1hrs">1시간 전</option>
					<option value="1day">1일 전</option>
				</select>
				
				<?php if($user["auth_code"] == "001" || $user["auth_code"] == "002"){ ?>
				<span style="margin-left: 15px">전체공유</span>
				<input type="checkbox" class="toggle" name="share_all_yn" id="share_all_yn">
				<label class="toggle" for="share_all_yn" style="margin: 3px 5px; margin-right: 15px;">
					<div></div>
				</label>
				<?php } ?>
					
				<?php if($user["auth_code"] == "004"){ ?>
				<span style="margin-left: 15px">팀내공유</span>
				<input type="checkbox" class="toggle" name="share_tm_yn" id="share_tm_yn"> 
				<label class="toggle" for="share_tm_yn" style="margin: 3px 5px; margin-right: 15px;">
					<div></div>
				</label>
				<?php } ?>
			</li>
			<!-- 20251203 차현우 일정등록 작업 끝. -->
			
		</ul>

		<div class="listWrap">
			<div class="listEtcWrap">
				<div class="left">
					<!-- <select class="listSet" id="gradeCode" style="margin-right:5px; width: 130px;">
							<option value="">고객등급별 보기</option>
						<?php
						$value = array('' => '');
						$query = "SELECT * FROM mc_db_grade_info WHERE use_yn = 'Y' AND del_yn = 'N' ORDER BY grade_code DESC";
						$grades = list_pdo($query, $value);

						while ($row = $grades->fetch(PDO::FETCH_ASSOC)) {
						?>
							<option value="<?= $row["grade_code"] ?>" <?= ($_GET["gradeCode"] == $row["grade_code"]) ? "selected" : "" ?>><?= dhtml($row["grade_name"]) ?></option>
						<?php } ?>
						</select> -->
					<select class="listSet" id="csStatusCode" style="width: 130px;">
						<option value="">상담상태별 보기</option>
						<?php while ($row = $csStatusList->fetch(PDO::FETCH_ASSOC)) { ?>
							<option value="<?= $row["status_code"] ?>" <?= ($_GET["csStatusCode"] == $row["status_code"]) ? "selected" : "" ?>><?= dhtml($row["status_name"]) ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div></div>
			<table>
				<colgroup>
					<col width="5%">
					<col width="12%">
					<col width="43%">
					<col <?php if ($user['auth_code'] == '001') { ?> width="5%" <?php } else { ?> width="10%" <?php } ?>>
					<col width="15%">
					<col width="15%">
					<?php if ($user['auth_code'] == '001') { ?>
						<col width="5%">
					<?php } ?>
				</colgroup>
				<thead>
					<tr>
						<th>NO</th>
						<th>상담상태</th>
						<th>내용</th>
						<th>첨부파일</th>
						<th>상담자 정보</th>
						<th>상담일시</th>
						<?php if ($user['auth_code'] == '001') { ?>
							<th>삭제</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php if (!$totalCnt) { ?>
						<tr>
							<td <?php if ($user['auth_code'] == '001') { ?> colspan="7" <?php } else { ?> colspan="6" <?php } ?>>등록된 상담기록이 존재하지 않습니다.</td>
						</tr>
					<?php } ?>



					<?php while ($row = $cs->fetch(PDO::FETCH_ASSOC)) { ?>
						<tr>
							<td class="lp05"><?= $totalCnt--; ?></td>
							<td class="lp05" style="font-weight:bold; color:<?= $row['color']; ?>"><?= dhtml($row['status_name']) ?></td>
							<td class="lp05" style="text-align: left;"><?= ($row["number_yn"] == "Y") ? number_format($row["memo"]) . $row["number_label"] : dhtml($row['memo']) ?></td>
							<td>
								<?php if ($row['filename']) { ?>
									<a href="/sub/down/csFile?idx=<?= $row['idx'] ?>" title="<?= $row['filename_r'] ?>"><i class="fas fa-download click"></i></a>
								<?php } else { ?>
									<i class="fas fa-download" style="color: #DDD;"></i>
								<?php } ?>
							</td>
							<td class="lp05">
								<?= dhtml($row['m_name']) ?>(<?= $row['m_id'] ?>)
							</td>
							<td class="lp05"><?= $row['reg_date'] ?></td>
							<?php if ($user['auth_code'] == '001') { ?>
								<td class="lp05"><i style="cursor: pointer;" data-idx="<?= $row['idx'] ?>" class="far fa-times-circle trash_go"></i></td>
							<?php } ?>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</form>
</div>

<div id="popupBtnWrap">
	<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="csLog">닫기</button>
</div>

<script type="text/javascript">
	function enterkey() {
		if (window.event.keyCode == 13) {
			event.preventDefault();
			$("#csSubmitBtn").trigger("click");
		}
	}
	$(function() {

		$(".trash_go").click(function() {
			var idx = $(this).data("idx");
			if (confirm("DB상담내역을 삭제하시겠습니까?")) {
				$("#loadingWrap").fadeIn(350, function() {
					$.ajax({
						url: "/ajax/db/csLogDP",
						type: "POST",
						data: {
							idx: idx
						},
						success: function(result) {
							switch (result) {
								case "success":
									alert("DB상담내역 삭제를 완료하였습니다.");
									window.location.reload();
									break;
								case "fail":
									alert("알 수 없는 이유로 삭제를 실패하였습니다.");
									$("#loadingWrap").fadeOut(350);
									break;
								default:
									alert(result);
									$("#loadingWrap").fadeOut(350);
									break;
							}
						}
					});
				});
			}
		});

		var calculateCodeList = <?= json_encode($calculateList) ?>;

		function csMemoPlaceholderSetting() {
			var memo = "상담내용입력";
			if (jQuery.inArray($("#statusCode").val(), calculateCodeList) >= 0) {
				memo = "금액 또는 수량기재";
			}

			$("#csMemo").attr("placeholder", memo);
		}

		csMemoPlaceholderSetting();

		$('#scheduleChk').change(function() {
			$checked = $('#scheduleChk').is(':checked');
			var selectChange = $(this).closest(".listMiniWriteWrap");
			if ($checked) {
				selectChange.find(".regSchedule").show();
			}
			else {
				selectChange.find(".regSchedule").hide();
			}
		});

		$('#noti_yn').change(function() {
			$checked = $('#noti_yn').is(':checked');
			if ($checked) {
				$("#noti_time").show();
			}
			else {
				$("#noti_time").hide();
			}
		});

		$("#statusCode").change(function() {
			csMemoPlaceholderSetting();
		});

		$("#csSubmitBtn").click(function() {
			var datas = new FormData($("form")[0]);

			if (!$("#statusCode").val() || !$("#csMemo").val()) {
				alert("등록할 내용을 입력해주시길 바랍니다.");
				return false;
			}

			// 20251203 차현우 일정등록 작업
			if ($("#scheduleChk").is(':checked')) {
				if (!datas.get('type_code')) {
					alert("일정구분값을 선택해주시기 바랍니다.");
					return false;
				}

				if (!datas.get('date')) {
					alert("시작일을 선택해주시기 바랍니다.");
					return false;
				}

				if (!datas.get('s_time')) {
					alert("시작시간을 선택해주시기 바랍니다.");
					return false;
				}
			}
			// 20251203 차현우 일정등록 작업 끝.


			$("#loadingWrap").fadeIn(350, function() {
				$.ajax({
					url: "/ajax/db/csLogWP",
					type: "POST",
					data: datas,
					processData: false,
					contentType: false,
					success: function(result) {
						switch (result) {
							case "success":
								window.location.reload();
								break;
							case "fail":
								alert("알 수 없는 이유로 등록을 실패하였습니다.");
								$("#loadingWrap").fadeOut(350);
								break;
							default:
								alert(result);
								$("#loadingWrap").fadeOut(350);
								break;
						}
					}
				});
			});
		});

		<?php if (!$mainTel) { ?>

			$(".checkTel").click(function() {
				alert("발신번호를 등록한 뒤 사용가능합니다.");
				return false;
			})

		<?php } ?>
		
		// 20251208 차현우 일정등록 시간 커스텀 
		$('input:text[timeonly]').timepicker({
			timeFormat: 'HH:mm',
			interval: 30,
			minTime: '9',
			maxTime: '21',
			startTime: '09:00',
			dynamic: false,
			dropdown: true,
			scrollbar: true,
			noneOption: true
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
		// 20251208 차현우 일정등록 시간 커스텀 끝.

	});
</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>