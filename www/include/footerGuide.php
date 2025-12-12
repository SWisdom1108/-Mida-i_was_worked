<?php
	
	# 가이드 내용 설정
	$guideInfo = [];
	switch($guideName){
		case "sms" :
			array_push($guideInfo, "- 요청 및 등록한 발신번호가 없을 경우 발신번호 요청 버튼을 통해 등록 후 이용이 가능합니다.");
			array_push($guideInfo, "- 템플릿 내용의 길이가 <b>80 Byte</b>보다 클 경우 MMS로 전송이 됩니다.");
			array_push($guideInfo, "- MMS의 내용길이 제한은 <b>2000 Byte</b>입니다.");
			array_push($guideInfo, "- 템플릿 내용에 <b>#{고객명}</b>을 입력하시면 발송시 수신자명으로 바뀌어 전송됩니다.");
			break;
	}

?>
<?php if($guideName && count($guideInfo)){ ?>
			
				<div class="guideWrap">
					<div>
						<div class="iconWrap">
							<i class="fas fa-info-circle"></i>
						</div>
						<ul class="conWrap">
						<?php for($i = 0; $i < count($guideInfo); $i++){ ?>
							<li class="basic"><?=$guideInfo[$i]?></li>
						<?php  } ?>
						</ul>
					</div>
				</div>
				
<?php } ?>