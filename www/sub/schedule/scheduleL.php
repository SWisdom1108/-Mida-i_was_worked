<?php

# 메뉴 접근 권한설정
# 001(최고관리자) 002(관리자) 003(생산마스터)
# 004(팀마스터) 005(영업자)
$menuAuth = ["001", "002", "004", "005"];

# 메뉴설정
$secMenu = "schedule";

# 공용 헤더 가져오기
include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

# andQuery
$andQuery = "";

switch ($user["auth_code"]) {
	case "004":
		$andQuery .= " AND ( tm_code = '{$user["tm_code"]}' OR share_all_yn = 'Y' )";
		break;
	case "005":
		$andQuery .= " AND ( ( reg_idx = '{$user["idx"]}' OR share_all_yn = 'Y' ) OR ( share_tm_yn = 'Y' AND tm_code ='{$user["tm_code"]}') )";
		break;
}

# 데이터 정리
$scheduleDataList = [];
$scheduleSMSList = [];
$scheduleTotalList = [];
$value = array('' => '');
$query = "
		SELECT MT.*
		FROM mt_schedule MT
		WHERE use_yn = 'Y'
		AND s_date LIKE '{$year}-{$month}%'
		{$andQuery}
		ORDER BY s_date ASC
	";
$sql = list_pdo($query, $value);
while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
	$thisDate = date("d", strtotime($row["s_date"]));
	$thisTime = date("H:i", strtotime($row["s_date"]));
	$scheduleTotalList[$thisDate]++;

	$row['memo'] = dhtml($row['memo']);
	if ($row["schedule_type"] == "sms") {
		$scheduleSMSList[$thisDate]++;
	} else {
		$scheduleDataList[$thisDate] .= ($scheduleDataList[$thisDate]) ? "{@#data@#}" : "";
		$scheduleDataList[$thisDate] .= "{$thisTime}@#@#{$row["memo"]}@#@#{$row["idx"]}@#@#userItem{$row["reg_idx"]}@#@#typeItem{$row["type_code"]}@#@#{$row["noti_yn"]}@#@#{$row["call_yn"]}@#@#{$row["db_idx"]}@#@#";
	}
}

# 메인번호 가져오기
$value = array('' => '');
$query = "SELECT sent_tel FROM mt_sms_tel WHERE use_yn = 'Y' AND main_yn = 'Y'";
$mainTel = view_pdo($query, $value)["sent_tel"];

?>

<style>
	#mainContentsWrap {
		overflow: hidden;
	}

	#mainContentsWrap>.contentsWrap {
		padding: 0;
		padding-bottom: 100px;
	}

	#scheduleMainCalendarWrap>.dayList>li>.scheduleItemWrap>li.sms.active {
		background-color: <?= $site["main_color"] ?>;
	}

	#scheduleMainCalendarWrap>.dayList>li>.scheduleItemWrap>li.basic>.circle {
		background-color: <?= $site["main_color"] ?>;
	}

	#scheduleDayCalendarWrap>div>.titWrap>.tit {
		color: <?= $site["main_color"] ?>;
	}

	#scheduleDayCalendarWrap>div>.titWrap>.btnList>.type01 {
		background-color: <?= $site["main_color"] ?>;
		color: #FFF;
	}

	#scheduleDayCalendarWrap>div>.titWrap>.btnList>.type02 {
		color: <?= $site["main_color"] ?>;
		border: 1px solid <?= $site["main_color"] ?>;
		background-color: #FFF;
	}

	#scheduleDayCalendarWrap>div>.scheduleWrap>.itemWrap>.scheduleListWrap>li.basic {
		border: 1px solid <?= $site["main_color"] ?>;
	}

	#leftSideWrap>.subMenuListWrap .trdMenu>li>a::before {
		content: "\f14a";
		font-weight: 900;
		font-family: "Font Awesome 5 Free";
		margin-right: 5px;
	}

	#leftSideWrap>.subMenuListWrap .trdMenu>li.active>a::before {
		content: "\f0c8";
		font-weight: 400;
	}

	#leftSideWrap>.subMenuListWrap .trdMenu>li>a {
		opacity: 1;
		color: #FFF !important;
	}

	#leftSideWrap>.subMenuListWrap .trdMenu>li.active>a {
		opacity: 0.4;
	}

	.db_class{
		cursor: pointer;
	}
	.db_class:hover {
		background-color: #FBFBFB !important;
	}
</style>

<div id="scheduleDayCalendarWrap">
	<div>

		<div class="titWrap">
			<span class="tit">
				<i class="fas fa-arrow-circle-left dayCalendarCloseBtn"></i>
				<span>일정</span>
			</span>
			<div class="btnList">
				<button type="button" class="popupBtn type01" data-type="open" data-target="write" data-url="" data-name="새로운 일정등록">
					<i class="fas fa-plus-circle"></i>일정 등록
				</button>
				<?php if ($mainTel) { ?>
					<button type="button" class="popupBtn type02" data-type="open" data-target="write" data-url="" data-name="새로운 SMS일정등록">
						<i class="fas fa-plus-circle"></i>SMS일정 등록
					</button>
				<?php } ?>
			</div>
		</div>

		<!-- 251208 차현우 -->
		<!-- 이전 로직 -->
		<!-- <div class="scheduleWrap">
		<?php for ($i = 9; $i < 21; $i++) { ?>
					<div class="itemWrap time<?= ($i < 10) ? "0{$i}" : $i ?>">
						<div class="timeInfo"><?= ($i < 10) ? "0{$i}" : $i ?>:00</div>
						<ul class="scheduleListWrap">
						</ul>
					</div>
				<?php } ?>
		</div> -->
		
		<div class="scheduleWrap">
			<?php for ($i = 9; $i < 22; $i++) { ?>
				<?php for ($m = 0; $m < 60; $m += 30) { ?>
					<?php if ($m == 0) { ?>
						<div class="itemWrap time<?= ($i < 10) ? "0{$i}:00" : "{$i}:00" ?>">
							<div class="timeInfo"><?= ($i < 10) ? "0{$i}:00" : "{$i}:00" ?></div>
							<ul class="scheduleListWrap">
							</ul>
						</div>
					<?php } else { ?>
						<div class="itemWrap time<?= ($i < 10) ? "0{$i}:{$m}" : "{$i}:{$m}" ?>">
							<div class="timeInfo"><?= ($i < 10) ? "0{$i}:{$m}" : "{$i}:{$m}" ?></div>
							<ul class="scheduleListWrap">
							</ul>
						</div>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</div>
		<!-- 251208 차현우 끝. -->

		<div class="scheduleWrap">
			<?php for ($i = 0; $i < 24; $i++) { ?>
				<div class="itemWrap time<?= ($i < 10) ? "0{$i}" : $i ?>">
					<div class="timeInfo"><?= ($i < 10) ? "0{$i}" : $i ?>:00</div>
					<ul class="scheduleListWrap">
					</ul>
				</div>
			<?php } ?>
		</div>

	</div>
</div>

<div id="scheduleMainCalendarWrap">
	<ul class="labelList">
		<li class="type0">일</li>
		<li class="type1">월</li>
		<li class="type2">화</li>
		<li class="type3">수</li>
		<li class="type4">목</li>
		<li class="type5">금</li>
		<li class="type6">토</li>
	</ul>
	<?php for ($n = 1, $i = 0; $i < $totalWeek; $i++) { ?>
		<ul class="dayList">
			<?php for ($k = 0; $k < 7; $k++) { ?>
				<?php
				$nn = ($n < 10) ? "0{$n}" : $n;

				$class = "";
				$class .= (date("Y-m-d") == "{$year}-{$month}-{$nn}") ? " today" : "";
				$class .= (strtotime(date("Y-m-d")) > strtotime("{$year}-{$month}-{$nn}")) ? " end" : "";

				$smsCnt = number_format($scheduleSMSList[$nn]);
				$itemList = explode("{@#data@#}", $scheduleDataList[$nn]);
				?>
				<li class="type<?= $k ?> <?= $class ?> dayItem<?= $nn ?>" data-date="<?= "{$year}-{$month}-{$nn}" ?>">
					<?php if (($n > 1 || $k >= $startWeek) && ($totalDay >= $n)) { ?>
						<div class="dayCalendarOpenWrap">
							<span class="dayCalendarOpenBtn" data-date="<?= $year ?>-<?= $month ?>-<?= $nn ?>"><?= $n ?></span>
						</div>
						<div class="dayCalendarCntWrap">(<?= number_format($scheduleTotalList[$nn]) ?>)</div>
						<ul class="scheduleItemWrap">
							<li class="sms <?= ($smsCnt) ? "active" : "" ?>">
								<span class="label"><i class="fas fa-comments"></i>SMS 전송</span>
								<span class="value"><?= $smsCnt ?>건</span>
							</li>
							<?php if ($scheduleDataList[$nn]) { ?>
								<?php foreach ($itemList as $data) {
									$data = explode("@#@#", $data); ?>
									<li class="basic item<?= $data[2] ?> <?= $data[3] ?> <?= $data[4] ?>" data-idx="<?= $data[2] ?>">
										<!-- 251208 차현우 OX 표시 및 상태 및 담당자 -->
										<?php 
                      $value = array(':db_idx' => $data[7] );
                      $query = "SELECT * FROM mt_db WHERE idx = :db_idx";
                      $scheduleInfo = view_pdo($query, $value);

                      $value = array(':idx' => $scheduleInfo['m_idx'] );
                      $query = "SELECT * FROM mt_member WHERE idx = :idx";
                      $memberInfo = view_pdo($query, $value);
                    ?>

										<span class="circle"></span>
										<?php if ($data[6] == 'Y') { ?>
											<span><i class="far fa-circle" style="color: #DC3333; width: 15px; margin-top: 9px; margin-right: 5px; float: left;"></i></span>
										<?php } else { ?>
											 <span><i class="fas fa-times" style="color: #DC3333; width: 15px; margin-top: 9px; margin-right: 5px; float: left;"></i></span>
										<?php } ?>
										<span class="time"><?= $data[0] ?></span>
										<!-- 알림 작업_2024.10.17 문지호 -->
										<?php if ($data[5] == 'Y') { ?>
											<span class="bell"><i class="fas fad fa-bell"></i></span>
											<!-- 251208 차현우 OX 표시 및 상태 및 담당자 -->
											<span class="memo" style="width: calc(100% - 77px);"><?=($scheduleInfo['cs_etc02']) ? $scheduleInfo['cs_etc02'] : "-" ?> / <?=($memberInfo['m_name']) ? $memberInfo['m_name'] : "-" ?> </span>
										<?php } else {?>
                    <span class="memo" style="width: calc(100% - 60px);"><?=($scheduleInfo['cs_etc02']) ? $scheduleInfo['cs_etc02'] : "-" ?> / <?=($memberInfo['m_name']) ? $memberInfo['m_name'] : "-" ?> </span>
										<!-- <span class="memo" style="width: calc(100% - 100px);"><?= dhtml($data[1]) ?></span> -->
										<?php } ?>
										<!-- 251208 차현우 OX 표시 및 상태 및 담당자 끝. -->
									</li>
								<?php } ?>
							<?php } ?>
						</ul>
					<?php $n++;
					} ?>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
</div>

<!-- 일림여부(W,U) 가이드_2024.10.02 문지호 -->
<div class="guidePopup">
	<div class="popupContent">
		<div class="popupTitle">알림여부</div>
		<div class="content content_01">
			<div class="info">
				<p>- 알림여부 선택 시 2가지 형태로 알림을 받을실 수 있습니다.<p>
				<br>
				<p>- 브라우저 좌측 상단에 나타나는 알림 권한을</p>
				<p style='margin-left:7px;'>'허용'으로 설정해주셔야 알림 기능을 사용 가능합니다.</p>
			</div>
			<div class="img_box">
				<img src="/images/WindowsNotificationGuide_01.jpg">
			</div>
		</div>
		<!-- <div class="content content_02">
			<div class="left">
				<div class="info">(1) 윈도우 알림 : 등록된 일정 시작시간에 알림</div>
				<img src="/images/WindowsNotificationGuide_02.jpg">
			</div>
			<div class="right">
				<div class="info">(2) 메인 팝업 알림 : 페이지 진입 시 팝업 알림
					<div>*확인 버튼 누를 시 더 이상 알림이 발생하지 않습니다.</div>
				</div>
				<img src="/images/WindowsNotificationGuide_03.jpg">
			</div>
		</div> -->
	</div>
</div>

<script type="text/javascript">
	var url = "<?= explode("?", $_SERVER["REQUEST_URI"])[0] ?>";
	var auth_code = "<?= $user["auth_code"] ?>";

	function openCalendar(date, data_idx) {
		setDate = date;

		var date_r = date.split("-");
		$("#scheduleDayCalendarWrap").show();
		$("#scheduleDayCalendarWrap > div > .titWrap > .tit > span").text(date_r[0] + "년 " + date_r[1] + "월 " + date_r[2] + "일 일정");
		$("#scheduleDayCalendarWrap > div > .titWrap > .btnList > .type01").attr("data-url", "/sub/schedule/scheduleW?date=" + date);
		$("#scheduleDayCalendarWrap > div > .titWrap > .btnList > .type02").attr("data-url", "/sub/schedule/scheduleSMSW?cs_name=<?= $_GET["cs_name"] ?>&cs_tel=<?= $_GET["cs_tel"] ?>&date=" + date);
		history.pushState(null, null, url + "?year=" + date_r[0] + "&month=" + date_r[1] + "&day=" + date_r[2]);

		<?php if ($_GET["cs_name"] && $_GET["cs_tel"]) { ?>
			$("#scheduleDayCalendarWrap > div > .titWrap > .btnList > .type02").click();
		<?php } ?>

		<?php if ($_GET["db"]) {
			$_SESSION["scheduleGetDB"] = $_GET["db"]; ?>
			$("#scheduleDayCalendarWrap > div > .titWrap > .btnList > .type01").click();
		<?php } ?>

		loading();
		$.ajax({
			url: "/ajax/schedule/getScheduleList",
			type: "POST",
			data: {
				date: date
			},
			success: function(result) {
				$("#scheduleDayCalendarWrap > div > .scheduleWrap  > .itemWrap  > .scheduleListWrap").html("");

				if (result.length) {
					$.each(result, function(index, data) {

						var infodetail = ''
						var db_class = ''
						if (data.cs_name && data.cs_tel) {
							infodetail = `<span class="schedule_cs_info">${data.cs_name}(${data.cs_tel})</span>`;
							db_class =" db_class"

						}

						var code = '<li class="' + data.type + ' item' + data.idx + ' ' + data.class + db_class +'"data-idx="' + data.idx + '" style="display: ' + data.display + ';">';
						code += '<div class="infoWrap">';
						code += '<div class="left">';
						
						if (data.call) {
							code += '<span><i class="far fa-circle" style="color: #DC3333;"></i></span>';
						} 
						else {
							code += '<span><i class="fas fa-times" style="color: #DC3333;"></i></span>';
						}

						if (data.new) {
							code += '<span class="new">신규일정</span>';
						}

						if (data.type == "basic") {
							if (data.notice) {
								code += '<span><i class="fas fa-bell" style="color: #DC3333;"></i>' + data.notice + '</span>';
							}
							code += '<span><i class="fas fa-calendar-alt"></i>' + data.typeName + '</span>';
						}

						code += `<span class="schedule_regName">담당자 : ${data.regName}</span>`;

						code += infodetail

						if (data.type == "sms") {
							code += '<span><i class="fas fa-envelope"></i>SMS</span>';
							code += '<span><i class="fas fa-clock"></i>' + data.time + '</span>';
						}

						code += '</div>';

						code += '<div class="right">';
						// code += '<span><i class="fas fa-user-circle"></i>' + data.regName + '</span>';

						if (data.btn) {
							code += '<div class="line"></div>';
							if (data.type == "basic") {
								code += '<button type="button" class="popupBtn" data-type="open" data-target="write" data-url="/sub/schedule/scheduleU?idx=' + data.idx + '" data-name="기존일정 수정" title="수정"><i class="fas fa-edit"></i></button>';
							}
							code += '<button type="button" class="scheduleDataDeleteBtn" title="삭제"><i class="fas fa-trash-alt"></i></button>';
						}
						code += '</div>';
						code += '</div>';

						code += '<div class="memoWrap">';

						if (data.type == "basic") {
							code += '<span class="schedule_info_clock"><i class="fas fa-clock"></i>' + data.time + '</span>';
						}

						// 알림 작업_2024.10.17 문지호
						if (data.noti_yn == "Y") {
							code += '<span class="schedule_info_bell"><i class="fas fad fa-bell"></i></span>';
						}

						// 251208 차현우
						code += "진료과목: " + data.treat + " / 담당자: " + data.member;
						code += " / 메모: " + data.memo;
						// code += data.memo;
						code += '</div>';
						code += '</li>';

						// $("#scheduleDayCalendarWrap > div > .scheduleWrap  > .itemWrap.time" + data.timeClass + "  > .scheduleListWrap").append(code);
						$("#scheduleDayCalendarWrap > div > .scheduleWrap > .itemWrap[class*='time" + data.timeClass + "'] > .scheduleListWrap").append(code);
						// 251208 차현우 끝.
					});
				}

				$("html, body").animate({
					scrollTop: 0
				}, 0);
				setTimeout(function() {
					loadingClose();

					var height = $("#scheduleDayCalendarWrap").outerHeight() + 100;
					$("#mainContentsWrap").css("height", height + "px");
					$("#scheduleDayCalendarWrap").addClass("active");

					if (data_idx) {
						var top = $("#scheduleDayCalendarWrap > div > .scheduleWrap > .itemWrap > .scheduleListWrap > li.item" + data_idx).offset().top;
						$("html, body").animate({
							scrollTop: top + "px"
						}, 0);
					}
				}, 150);
			}
		});
	}

	$(function() {

		setCookie("scheduleCheckTypeData", "");
		setCookie("scheduleCheckUserData", "");

		/* 설정일시 */
		var year = "<?= $year ?>";
		var month = "<?= $month ?>";
		var day = "<?= $day ?>";

		if (day) {
			openCalendar(year + "-" + month + "-" + day);
		}

		/* 일정팝업 열기 */
		$(".dayCalendarOpenBtn").click(function(e) {
			e.preventDefault();

			var date = $(this).attr("data-date");
			openCalendar(date);
		});

		$("#scheduleMainCalendarWrap > .dayList > li > .scheduleItemWrap > li.basic").click(function(e) {
			e.preventDefault();

			var date = $(this).closest(".scheduleItemWrap").closest("li").attr("data-date");
			var idx = $(this).attr("data-idx");
			openCalendar(date, idx);
		});

		$("#scheduleMainCalendarWrap > .dayList > li > .scheduleItemWrap > li.sms.active").click(function(e) {
			e.preventDefault();

			var date = $(this).closest(".scheduleItemWrap").closest("li").attr("data-date");
			openCalendar(date);
		});

		/* 일정팝업 닫기 */
		$(".dayCalendarCloseBtn").click(function() {
			history.pushState(null, null, url + "?year=<?= $year ?>&month=<?= $month ?>");

			$("#mainContentsWrap").css("height", "");
			$("#scheduleDayCalendarWrap").removeClass("active");
			setTimeout(function() {
				$("#scheduleDayCalendarWrap").hide();
			}, 500);
		});

		/* 삭제 */
		$(document).on("click", ".scheduleDataDeleteBtn", function(e) {
			e.stopPropagation();
			var idx = $(this).closest("li").attr("data-idx");

			if (confirm("해당 일정을 삭제하시겠습니까?")) {
				loading(function() {
					$.ajax({
						url: "/ajax/schedule/scheduleDP",
						type: "POST",
						data: {
							idx: idx
						},
						success: function(result) {
							if (result.msg == "success") {
								alert("일정 삭제가 완료되었습니다.");
								$("#scheduleMainCalendarWrap > .dayList > li > .scheduleItemWrap > li.basic.item" + idx).remove();
								$("#scheduleDayCalendarWrap > div > .scheduleWrap > .itemWrap > .scheduleListWrap > li.item" + idx).remove();
								$("#scheduleMainCalendarWrap > .dayList > li.dayItem" + result.date).find(".dayCalendarCntWrap").text("(" + result.totalCnt + ")");
								if (result.smsCnt) {
									$("#scheduleMainCalendarWrap > .dayList > li.dayItem" + result.date).find(".scheduleItemWrap > li.sms > span.value").text(result.smsCnt + "건");
								}
							} else {
								alert("알 수 없는 이유로 삭제에 실패하였습니다.");
							}

							loadingClose();
						}
					});
				});
			}
		});
		$(document).on("click", ".popupBtn", function(e) {
			e.stopPropagation();
			var type = $(this).attr("data-type");
			var target = $(this).attr("data-target");
			var url = $(this).attr("data-url");
			var name = $(this).attr("data-name");

			popupControl(type, target, url, name);
		});

		$(".subMenuListWrap a").click(function(e) {
			e.preventDefault();
		});

		$(".scheduleItemSelect").click(function() {
			$(this).toggleClass("active");

			if ($(this).hasClass("userItem")) {
				var target = $(this).attr("class").split("userItem ")[1];
				target = target.split(" active")[0];

				var userId = target.split("userItem")[1];
				var targetMore = $("#scheduleDayCalendarWrap ." + target);
				target = $("#scheduleMainCalendarWrap ." + target);
			}

			if ($(this).hasClass("typeItem")) {
				var target = $(this).attr("class").split("typeItem ")[1];
				target = target.split(" active")[0];
				var targetMore = $("#scheduleDayCalendarWrap ." + target);
				target = $("#scheduleMainCalendarWrap ." + target);
			}

			if (!$(this).hasClass("active")) {
				$(target).show();
				$(targetMore).show();
			} else {
				$(target).hide();
				$(targetMore).hide();
			}

			var checkUser = [];
			var itemList = $(".subMenuListWrap .trdMenu .userItem");
			for (var i = 0; i < itemList.length; i++) {
				if ($(itemList[i]).hasClass("active")) {
					var target = $(itemList[i]).attr("class").split("userItem ")[1];
					target = target.split(" active")[0];
					target = target.split("userItem")[1];

					checkUser.push(target);
				}
			}
			setCookie("scheduleCheckUserData", checkUser.join(","));

			var checkType = [];
			var itemList = $(".subMenuListWrap .trdMenu .typeItem");
			for (var i = 0; i < itemList.length; i++) {
				if ($(itemList[i]).hasClass("active")) {
					var target = $(itemList[i]).attr("class").split("typeItem ")[1];
					target = target.split(" active")[0];
					target = target.split("typeItem")[1];

					checkType.push(target);
				}
			}
			setCookie("scheduleCheckTypeData", checkType.join(","));
		});

		$("#leftSideWrap > .subMenuListWrap .secMenu > li > a").click(function() {
			$(this).parent("li").toggleClass("active");
		});

		$(document).on("click",".scheduleListWrap > li", function(){
			if ( auth_code <= 2) {
				window.location = '/sub/db/dbTeamL?label=cs_tel&value='+ $(this).find(".schedule_cs_info").text().split("(")[1].replace(")","");
			} else {
				window.location = '/sub/db/dbMyL?label=cs_tel&value=' + $(this).find(".schedule_cs_info").text().split("(")[1].replace(")","");
			}
		});

	});

</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>