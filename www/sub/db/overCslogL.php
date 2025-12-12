<?php
# 메뉴 접근 권한설정
# 001(최고관리자) 002(관리자) 003(생산마스터)
# 004(팀마스터) 005(영업자)
$menuAuth = ["001", "002", "004", "005"];

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

$value = array(':use_yn' => 'Y', ':idx' => $_GET['idx']);
$query = "		
		SELECT MT.*
		FROM mt_db MT
		WHERE use_yn = :use_yn
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
        {$andQuerys}
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

<div class="writeWrap">
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
					<col width="10%">
					<col width="15%">
					<col width="15%">
				</colgroup>
				<thead>
					<tr>
						<th>NO</th>
						<th>상담상태</th>
						<th>내용</th>
						<th>첨부파일</th>
						<th>상담자 정보</th>
						<th>상담일시</th>
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
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
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

        $(".popupCloseBtn").click(function() {
            window.close();
        });

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

		$("#statusCode").change(function() {
			csMemoPlaceholderSetting();
		});

		$("#csSubmitBtn").click(function() {
			var datas = new FormData($("form")[0]);

			if (!$("#statusCode").val() || !$("#csMemo").val()) {
				alert("등록할 내용을 입력해주시길 바랍니다.");
				return false;
			}

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

	});
</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>