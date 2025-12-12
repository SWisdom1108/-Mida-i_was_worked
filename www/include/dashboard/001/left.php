<?php

	$nowDate = date("Y-m-d");

	# 접속현황
	$visitInfo = view_sql("
		SELECT
			  ( SELECT COUNT(*) FROM mt_member ) AS memberCnt
			, ( SELECT COUNT(*) FROM mt_login_log WHERE reg_date LIKE '{$nowDate}%' ) AS loginCnt
		FROM dual
	");

	# 조직현황
	$groupInfo = view_sql("
		SELECT
			  ( SELECT COUNT(*) FROM mt_member WHERE use_yn = 'Y' AND auth_code = '002' ) AS 002Cnt
			, ( SELECT COUNT(*) FROM mt_member WHERE use_yn = 'Y' AND auth_code = '003' ) AS 003Cnt
			, ( SELECT COUNT(*) FROM mt_member WHERE use_yn = 'Y' AND auth_code IN ( 004, 005 ) ) AS 004Cnt
		FROM dual
	");

	# DB통합현황
	$dbInfo = view_sql("
		SELECT
			  ( SELECT COUNT(*) FROM mt_db ) AS totalCnt
			, ( SELECT COUNT(*) FROM mt_db WHERE use_yn = 'Y' AND dist_code = '002' ) AS successCnt
			, ( SELECT COUNT(*) FROM mt_db WHERE use_yn = 'N' ) AS deleteCnt
		FROM dual
	");

?>
	
		<?php if(($programDateInfo->days) <= 10){ ?>
			<ul class="dashType_1" id="programEndDateInfoWrap">
				<li>
					<div class="dash_section">
						<div class="iconWrap"><i class="fas fa-exclamation-triangle"></i></div>
						<div class="infoWrap">
						<?php if(($programDateInfo->days) == 0){ ?>
							<b>프로그램 만료일이 오늘까지입니다.</b>
							<span class="lp05">(<?=date("Y-m-d", strtotime($site['e_date']))?>)</span>
						<?php } else { ?>
							프로그램 만료일까지 <b class="lp05"><?=($programDateInfo->days)?>일</b> 남았습니다.
							<span class="lp05">(<?=date("Y-m-d", strtotime($site['e_date']))?>)</span>
						<?php } ?>
						</div>
					</div>
				</li>
			</ul>
		<?php } ?>

		<ul class="dashType_3">
			<li>
				<div class="dash_section">
					<div class="section_tit">
						접속현황
					</div>
					<div class="section_con">
						<div class="dataListTable">
							<div class="iconWrap">
								<i class="fas fa-portrait"></i>
								<span>VISIT</span>
							</div>
							<ul class="infoWrap">
								<li>
									<span class="label">총 회원 수</span>
									<span class="value"><?=number_format($visitInfo['memberCnt'])?></span>
								</li>
								<li>
									<span class="label">오늘 로그인 수</span>
									<span class="value"><?=number_format($visitInfo['loginCnt'])?></span>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="dash_section">
					<div class="section_tit">
						조직현황
					</div>
					<div class="section_con">
						<div class="dataListTable">
							<div class="iconWrap">
								<i class="fas fa-sitemap"></i>
								<span>GROUP</span>
							</div>
							<ul class="infoWrap">
								<li>
									<span class="label">관리자 수</span>
									<span class="value"><?=number_format($groupInfo['002Cnt'])?></span>
								</li>
								<li>
									<span class="label">생산업체 수</span>
									<span class="value"><?=number_format($groupInfo['003Cnt'])?></span>
								</li>
								<li>
									<span class="label"><?=$customLabel['fc']?> 수</span>
									<span class="value"><?=number_format($groupInfo['004Cnt'])?></span>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="dash_section">
					<div class="section_tit">
						DB통합현황
					</div>
					<div class="section_con">
						<div class="dataListTable">
							<div class="iconWrap">
								<i class="fas fa-server"></i>
								<span>DB</span>
							</div>
							<ul class="infoWrap">
								<li>
									<span class="label">전체 DB</span>
									<span class="value"><?=number_format($dbInfo['totalCnt'])?></span>
								</li>
								<li>
									<span class="label">분배된 DB</span>
									<span class="value"><?=number_format($dbInfo['successCnt'])?></span>
								</li>
								<li>
									<span class="label">삭제된 DB</span>
									<span class="value"><?=number_format($dbInfo['deleteCnt'])?></span>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</li>
		</ul>
		
		<ul class="dashType_1">
			<li>
				<div class="dash_section">
					<div class="section_tit">
						DB통합현황 통계
					</div>
					<div class="section_con" style="padding-left: 10px;">
						<div class="dashChartWrap"></div>
					</div>
				</div>
			</li>
		</ul>
		
		<script type="text/javascript">
			$(function(){
				
				var totalChart = bb.generate({
					data: {
						x: "x",
						columns: [
							["x"
								<?php
									$newDate = date("Y-m-d", strtotime("-1 day", strtotime($startDate)));
									while(true) {
										 $newDate = date("Y-m-d", strtotime("+1 day", strtotime($newDate)));
										echo ',"'.$newDate.'"';
										 if($newDate == $endDate) break;
									}
								 ?>
							],
							["등록된 DB"
								<?php
									$newDate = date("Y-m-d", strtotime("-1 day", strtotime($startDate)));
									while(true) {
										$newDate = date("Y-m-d", strtotime("+1 day", strtotime($newDate)));
										$num = view_sql("SELECT count(*) AS num FROM mt_db WHERE reg_date LIKE '{$newDate}%'")['num'];
										echo ',"'.$num.'"';
										if($newDate == $endDate) break;
									}
								 ?>
							]
						],
						types: {
							"등록된 DB" : "spline"
						},
						colors: {
							"등록된 DB" : "#41C560"
						}
					},
					legend: {
						"show": false
					},
					grid: {
						y: {
							show: true
						}
					},
					axis: {
						x: {
							type: "category",
							tick: {
								multiline: false,
								tooltip: false
							}
						}
					},
					bindto: ".dashChartWrap"
				});
				
			})
		</script>