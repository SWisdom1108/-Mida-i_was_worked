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
										$num = view_sql("SELECT count(*) AS num FROM mt_db WHERE reg_date LIKE '{$newDate}%' AND pm_code = '{$user['pm_code']}'")['num'];
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