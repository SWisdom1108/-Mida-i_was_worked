<?php
# 공용 헤더 가져오기
include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

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

$gradeArr = [];
$value = array('' => '');
$query = "SELECT grade_name, grade_code FROM mc_db_grade_info WHERE use_yn = 'Y' ORDER BY grade_code ASC";
$grade = list_pdo($query, $value);

while($row = $grade->fetch(PDO::FETCH_ASSOC)){
	$thisdatas = [];
	$thisdatas['grade_name'] = $row['grade_name'];
	$thisdatas['grade_code'] = $row['grade_code'];
	
	array_push($gradeArr, $thisdatas);
}

$companyArr = [];
$value = array('' => '');
$query = "SELECT company_name, pm_code FROM mt_member_cmpy WHERE use_yn = 'Y' ORDER BY idx ASC";
$company = list_pdo($query, $value);

while($row = $company->fetch(PDO::FETCH_ASSOC)){
	$thisdatas = [];
	$thisdatas['company_name'] = $row['company_name'];
	$thisdatas['pm_code'] = $row['pm_code'];
	
	array_push($companyArr, $thisdatas);
}

# 오늘일자
$year = date("Y");
$month = date("m");
$day = date("d");

?>

<style>
	.sDate { width: 10%; min-width: 140px; float:left; }
	.sDate .fa-calendar-alt { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #D8D8D8; pointer-events: none; }
	.detailSearchElement { width: 33%; float:left; padding:20px; }
	.detailSearchInput { width: 100%; float:left; max-width: 280px; margin-top: 10px; border-radius: 5px;}
	.detailSearchSelect { width: 100%; float:left; max-width: 280px; height: 35px; margin-top: 10px; border: 1px solid #EBEBEB; color: #666; border-radius: 5px;}
	.detailElementTit { color: #666666; }
	.detailElementTit2 { color: #999999; margin: 20px 0px 10px 20px}
	.detailElementLine { border-top: 1px solid #EFEFEF;}
	.detailMultiple { background: #ffffff; padding-left: 10px; text-align: left; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;}

.multiple-dropdown {
  display: none;
  position: absolute;
  top: 50px;
  left: 20px;
  background: white;
  color: black;
  padding: 10px;
  border: 1px solid #ccc;
  width: 280px;
  border-radius: 5px;
	z-index: 999;
}
.multiple-dropdown > div > label:last-of-type { font-size: 15px; margin-left: 10px;}
.multiple-dropdown.active { display: block; }

</style>

<div class="searchWrap" style="border: none; padding-top: 0px; margin-bottom: 0px;">
		<form enctype="multipart/form-data">
			<div style="width: 100%; float:left; padding:10px; margin: 0 auto;">
				<div class="detailSearchElement">
					<p class="detailElementTit">이름</p>
					<input type="text" class="txtBox value detailSearchInput" name="searchName" placeholder="이름">
				</div>

				<div class="detailSearchElement">
					<p class="detailElementTit">연락처</p>
					<input type="text" class="txtBox value detailSearchInput" name="searchTel" placeholder="숫자만 입력해주세요">
				</div>

				<div class="detailSearchElement">
					<p class="detailElementTit">생산업체</p>
					<select class="detailSearchSelect" name="cs_status_code">
						<option>선택</option>
					<?php foreach($companyArr as $val){ ?>
						<option value="<?=$val['pm_code']?>"><?=$val['company_name']?></option>
					<?php } ?>
					</select>
				</div>

				<div class="detailSearchElement">
					<p class="detailElementTit">고객등급</p>
					<select class="detailSearchSelect" name="grade_code">
						<option>선택</option>
					<?php foreach($gradeArr as $val){ ?>
						<option value="<?=$val['grade_code']?>"><?=$val['grade_name']?></option>
					<?php } ?>
					</select>
				</div>

				<div class="detailSearchElement">
					<p class="detailElementTit">분배일</p>
					<div class="sDate detailSearchInput">
						<input type="text" class="txtBox" name="dist_date" id="dist_date" dateonly placeholder="분배일" style="border-radius: 5px;" value="<?=$date?>">
						<i class="fas fa-calendar-alt"></i>
					</div>
				</div>
				<div class="detailSearchElement">
					<p class="detailElementTit">등록일</p>
					<div class="sDate detailSearchInput">
						<input type="text" class="txtBox" name="reg_date" id="reg_date" dateonly placeholder="등록일" style="border-radius: 5px;" value="<?=$date?>">
						<i class="fas fa-calendar-alt"></i>
					</div>
				</div>
			</div>
			
			<?php 
				if ($columnCnt > 0) {
			?>
			<hr class="detailElementLine">

			<div style="width: 100%; float:left; padding:10px; margin: 0 auto;">
				<p class="detailElementTit2">DB 상세정보</p>
				<?php 
					$idx = 0;
					foreach ($columnArr as $val) {
						$idx++;

						// 텍스트 입력
						if ($val['type'] == 'text' || $val['type'] == 'textarea' || $val['type'] == 'number') { ?>
							<div class="detailSearchElement">
								<p class="detailElementTit"><?=$val['name']?></p>
								<input type="text" class="txtBox value detailSearchInput" name="<?=$val['code']?>" placeholder="<?=$val['name']?>">
							</div>
						<?php	} ?>
						
						<!-- 단일 선택 -->
						<?php if ($val['type'] == 'select' || $val['type'] == 'radio') { ?>
							<div class="detailSearchElement">
								<p class="detailElementTit"><?=$val['name']?></p>
									<?php
										$selectArr = [];
										$value = array(':idx' => $idx);
										$query = "SELECT info_val FROM mt_db_cs_info_detail WHERE use_yn = 'Y' AND info_idx = :idx ORDER BY sort ASC";
										$select_info = list_pdo($query, $value);

										while($row = $select_info->fetch(PDO::FETCH_ASSOC)){
											$thisdatas = [];
											$thisdatas['info_val'] = $row['info_val'];
											
											array_push($selectArr, $thisdatas);
										}	
									?>
								<select class="detailSearchSelect" name="<?=$val['code']?>">
									<option value="">선택</option>
								<?php foreach($selectArr as $r) { ?>
									<option value="<?=$r['info_val']?>"><?=$r['info_val']?></option>
								<?php } ?>
								</select>
							</div>
						<?php	} ?>

						<!-- 날짜선택 -->
						<?php if ($val['type'] == 'datepicker') { ?>
							<div class="detailSearchElement">
								<p class="detailElementTit"><?=$val['name']?></p>
								<div class="sDate detailSearchInput" style="width: 100%; margin-left: 0; float:left; max-width: 280px;">
									<input type="text" class="txtBox" style="border-radius: 5px;" name="<?=$val['code']?>" id="<?=$val['code']?>" dateonly placeholder="<?=$val['name']?>" value="<?=$date?>" >
									<i class="fas fa-calendar-alt"></i>
								</div>
							</div>
						<?php	} ?>
						
						<!-- 다중선택 -->
						<?php if ($val['type'] == 'checkbox') { 
								$tmpIdx = -1;	?>
							<div class="detailSearchElement">
								<p class="detailElementTit"><?=$val['name']?></p>
								<?php
									$detailArr = [];
									$value = array(':idx' => $idx);
									$query = "SELECT info_val FROM mt_db_cs_info_detail WHERE use_yn = 'Y' AND info_idx = :idx ORDER BY sort ASC";
									$detail_info = list_pdo($query, $value);

									while($row = $detail_info->fetch(PDO::FETCH_ASSOC)){
										$thisdatas = [];
										$thisdatas['info_val'] = $row['info_val'];
										array_push($detailArr, $thisdatas);
									}	?>

								<button type="button" class="dropdown-btn detailSearchSelect detailMultiple">옵션 선택</button>
								<div class="multiple-dropdown">
									<input type="hidden" name="<?=$val['code']?>" class="multi_etc" value="">
									<?php 
										foreach($detailArr as $r) { 
											$tmpIdx++; ?>
											<div>
												<input type="checkbox" class="item_box" id="check3_<?=$tmpIdx?>" value="<?=$r['info_val']?>">
												<label class="checkBox" for="check3_<?=$tmpIdx?>">
													<i class="fas fa-check-square on"></i>
													<i class="far fa-square off"></i>
												</label>
												<label for="check3_<?=$tmpIdx?>"><?=$r['info_val']?></label>
											</div>
									<?php }	?>
								</div>
							</div>
						<?php	} ?>
					<?php }
					}
				?>
		</form>
	</div>

<div id="popupBtnWrap" style="background: #F5F5F5; margin-top: 10px;">
	<label class="detailSearch right detailReload" style="border:none; color: #cccccc;">
		<i class="fas fa-redo"></i>초기화
	</label>
	<button type="button" class="typeBtn btnGray02 popupCloseBtn right" data-target="csLogL" style="margin-left: 700px; color: #8C8C8C; background-color: #ffffff;">닫기</button>
	<label class="detailSearch2 right" style="margin-left: 20px; background-color: #ffffff;">
		<i class="fas fa-search"></i>검색
	</label>
</div>

<script type="text/javascript">
	function enterkey() {
		if (window.event.keyCode == 13) {
			event.preventDefault();
			$("#csSubmitBtn").trigger("click");
		}
	}
	$(function() {
		$(".item_box").change(function () {
				const parent = $(this).closest(".detailSearchElement");
				const checkedValues = parent.find(".item_box:checked").map(function () {
						return $(this).val();
				}).get();
				
				// 버튼 텍스트 변경
				if (checkedValues.length > 0) {
					var text = `${checkedValues.length}개 선택 (${checkedValues.join(", ")})`;
					parent.find(".detailMultiple").text(text);
				} else {
					parent.find(".detailMultiple").text("옵션 선택");
				}
				parent.find(".multi_etc").val(checkedValues.join("@"));
		});

		$(".detailSearch2").click(function () {
			var formData = $(".searchWrap > form").serialize();
			
			$.ajax({
				url: "/ajax/db/dbDetailSearch",
				type: "POST",
				data: formData,
				dataType: "json",
				success: function(response) {
        if(response.status === "success") {
					window.parent.location.href = "./dbTeamL_chw?code=&label=" + response.queryString + "&orderBy=reg_date+DESC&listCnt=15";
        } else {
					alert(result.message || result);
        }
			}
		});


		$(".dropdown-btn").click(function (e) {
			e.preventDefault();
			e.stopPropagation();
			const box = $(this).siblings(".multiple-dropdown");
			$(".multiple-dropdown").not(box).removeClass("active");
			box.toggleClass("active");
		});

		$(".multiple-dropdown").click(function (e) {
			e.stopPropagation();
		});

		$(document).click(function () {
			$(".multiple-dropdown").removeClass("active");
		});

		$(".detailReload").click(function() {
			window.location.reload();
		});

		$(".popupCloseBtn").click(function () {
			window.parent.$(".popupWrap").fadeOut(350, function() {
				$(this).remove();
			});
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
						switch (result.status) {
							case "success":
								var targetUrl = 'dbTeamL_chw' + response.queryString;
								window.parent.location.href = targetUrl;
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