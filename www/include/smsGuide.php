<?php
	
	# 가이드 내용 설정
	$guideInfo = [];
	array_push($guideInfo, "보내는 문자 종류에 따라 참가되는 문자건수가 차이가 있습니다.");
	array_push($guideInfo, "일반 1건, LMS 3건, MMS 7건으로 차감되므로 충전 시 잔여수량을 확인 한 뒤 발송하시기 바랍니다.");


?>
<?php if(count($guideInfo)){ ?>
			
				<div class="smsGuideWrap">
					<div>
						<ul class="smsConWrap">
							<div class="smsIconWrap">
								<i class="fas fa-info-circle"></i>
								<span> 잠깐! 사용시 주의사항</span>
							</div>
						<?php for($i = 0; $i < count($guideInfo); $i++){ ?>
							<li class="basic"><?=$guideInfo[$i]?></li>
						<?php  } ?>
						</ul>
					</div>
				</div>
				
<?php } ?>