<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002", "004", "005"];

	# 메뉴설정
	$secMenu = "send";
	
	# 콘텐츠설정
	$contentsTitle = "SMS 전송";
	$contentsInfo = "수신자를 설정하여 SMS를 전송하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "SMS전송");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 메인번호 가져오기
	$value = array(''=>'');
	$query = "SELECT sent_tel FROM mt_sms_tel WHERE use_yn = 'Y' AND main_yn = 'Y'";
	$mainTel = view_pdo($query, $value)["sent_tel"];
	$value = array(''=>'');
	$query = "SELECT idx FROM mt_sms_tel WHERE use_yn = 'Y' AND main_yn = 'Y'";
	$mainTelIdx = view_pdo($query, $value)["idx"];
	$mainTel = ($mainTel) ? $mainTel : "-";
	$mainTelIdx = ($mainTelIdx) ? $mainTelIdx : 0;

	$guideName = "sms";

?>

	<?php if($user["auth_code"] <= 002){ ?>
		<!-- 데이터 간단정리표 -->
		<div class="dataInfoSimpleWrap">
			<div>
				<div class="iconWrap">
					<i class="fas fa-chart-pie"></i>
				</div>
				<div class="conWrap">
					<ul class="dataCntList">
						<li>
							<span class="label">총 SMS 수량</span>
							<span class="value" id="smsTotalCnt">0</span>
						</li>
						<li>
							<span class="label">사용 수량</span>
							<span class="value" id="smsUseCnt">0</span>
						</li>
						<li>
							<span class="label">잔여 수량</span>
							<span class="value" id="smsFinishCnt">0</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	<?php } ?>

	<!-- sms가이드 내용 설정 -->
	<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/smsGuide.php"; ?>

	<div id="smsSendFrmWrap">
		<form>
			<div class="left" ondragstart="return false">
				<div class="backgroundWrap">
					<span class="timeSet"><?=date("H:i")?></span>
					<span class="sendTelInfo"><?=$mainTel?> </span>
					<img src="/images/smsSendFrmBG.jpg">
				</div>
				
				<div class="smsLogVisualWrap">
					<ul>
						<li class="receive">
							<img src="/images/smsSendFrmReceiveBtm.png">
							<p>
								[전송절차]<br>
								1) 보낼 내용입력<br>
								2) 수신자 설정<br>
							<?php if($user["auth_code"] <= 002){ ?>
								3) 발신번호 선택<br>
								4) 전송하기 버튼 클릭
							<?php } else { ?>
								3) 전송하기 버튼 클릭
							<?php } ?>
							</p>
						</li>
					</ul>
				</div>
				
				<div class="smsSendMsgWrap">
					<p style="width: 100%; float: left; font-size: 12px; letter-spacing: -0.5px; color: #CCC; top: -1px; padding-left: 20px;">0 Byte</p>
					<textarea class="txtBox" placeholder="보낼 내용 입력" name="contents"></textarea>
					<button type="button" class="smsSubmitBtn"><i class="fas fa-paper-plane"></i></button>
				</div>
			</div>
			
			<div class="right smsReceiveSetting">
				<div class="titWrap">SMS 수신자 설정</div>
				<div class="settingWrap">
					<input type="text" class="txtBox receiveName" placeholder="이름">
					<input type="text" class="txtBox receiveTel" placeholder="연락처">
					<button type="button" class="typeBtn btnMain receiveAddBtn">추가</button>
					<?php if($user["auth_code"] <= 002){ ?>
						<button type="button" class="typeBtn btnGray01 rightSectionOpenBtn" data-target="memberTelList">회원목록</button>
					<?php } ?>
					<!-- <button type="button" class="typeBtn btnGray01 rightSectionOpenBtn" data-target="dbTelList">DB목록</button> -->
					<button type="button" class="typeBtn rightSectionOpenBtn" data-target="templateList">템플릿불러오기</button>
				</div>

				<div class="tableWrap head">
					<table>
						<colgroup>
							<col width="10%">
							<col width="30%">
							<col width="60%">
						</colgroup>
						<thead>
							<tr>
								<th>
									<input type="checkbox" id="listDataAllCheck">
									<label class="ch" for="listDataAllCheck">
										<i class="fas fa-check-square on"></i>
										<i class="far fa-square off"></i>
									</label>
								</th>
								<th>이름</th>
								<th>연락처</th>
							</tr>
						</thead>
					</table>
				</div>
				
				<div class="tableWrap body">
					<table>
						<colgroup>
							<col width="10%">
							<col width="30%">
							<col width="60%">
						</colgroup>
						<tbody>
						<?php if($_POST["smsReceiveData"]){ ?>
							<?php
								$receiveDataNum = 0;
								$_POST["smsReceiveData"] = implode(",", $_POST["smsReceiveData"]);
								$value = array(''=>'');
								$query = "SELECT cs_name, cs_tel FROM mt_db WHERE idx IN ( {$_POST["smsReceiveData"]} )";
								$receiveDataSQL = list_pdo($query, $value);
								while($row = $receiveDataSQL->fetch(PDO::FETCH_ASSOC)){
									$receiveDataNum++;
							?>
								<tr>
									<td>
										<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$receiveDataNum?>" data-idx="<?=$receiveDataNum?>">
										<label class="ch" for="listDataCheck_<?=$receiveDataNum?>">
											<i class="fas fa-check-square on"></i>
											<i class="far fa-square off"></i>
										</label>
									</td>
									<td><input type="hidden" name="name[]" value="<?=$row["cs_name"]?>"><?=$row["cs_name"]?></td>
									<td>
										<input type="hidden" name="tel[]" value="<?=$row["cs_tel"]?>"><?=$row["cs_tel"]?>
										<input type="hidden" name="type[]" value="단체문자발송">
									</td>
								</tr>
							<?php } ?>
						<?php } else { ?>
							<tr class="firstData">
								<td colspan="3">
									수신자 정보를 추가해주시길 바랍니다.
								</td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
				
				<div class="sendWrap">
					<button type="button" class="typeBtn btnGray01 receiveDeleteBtn">삭제</button>
					<button type="button" class="typeBtn btnRed smsSubmitBtn"><i class="fas fa-paper-plane"></i>전송하기</button>
					<div type="button" class="smsSubmitBtn" style="border-right: 1px solid #DCDCDC; height: 33px; line-height:33px; margin: 0 12px;"></div>
					<button type="button" class="smsTypeBtn" onclick="location.href='/sub/my/smsRequestL'">발신번호 등록</button>
					<?php if($user["auth_code"] <= 002){ ?>
						<select class="txtBox" name="send_tel" id="sendTelSelect">
						<?php
							$value = array(''=>'');
							$query = "SELECT * FROM mt_sms_tel WHERE use_yn = 'Y' ORDER BY idx ASC";
							$sql = list_pdo($query, $value);
							while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						?>
							<option value="<?=$row["idx"]?>" <?=($row["sent_tel"] == $mainTel) ? "selected" : ""?>>
								<?=$row["sent_name"]?>(<?=$row["sent_tel"]?>)
							</option>
						<?php } ?>
						</select>
						<span class="telLabel">발신번호</span>
					<?php } else { ?>
						<input type="hidden" id="sendTelSelect" value="<?=$mainTelIdx?>">
					<?php } ?>
				</div>
			</div>
			
			<?php if($user["auth_code"] <= 002){ ?>
			<div class="right memberTelList" style="display: none;">
				<div class="titWrap">회원 선택</div>
				<div class="tableWrap head" style="margin-top: 20px;">
					<table>
						<colgroup>
							<col width="10%">
							<col width="30%">
							<col width="60%">
						</colgroup>
						<thead>
							<tr>
								<th>
									<input type="checkbox" id="listDataAllCheck2">
									<label class="ch" for="listDataAllCheck2">
										<i class="fas fa-check-square on"></i>
										<i class="far fa-square off"></i>
									</label>
								</th>
								<th>이름(아이디)</th>
								<th>연락처</th>
							</tr>
						</thead>
					</table>
				</div>
				
				<div class="tableWrap body" style="height: 495px;">
					<table>
						<colgroup>
							<col width="10%">
							<col width="30%">
							<col width="60%">
						</colgroup>
						<tbody>
						<?php
							$value = array(''=>'');
							$query = "SELECT * FROM mt_member WHERE use_yn = 'Y' ORDER BY auth_code ASC";
							$sql = list_pdo($query, $value);
							while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						?>
							<tr>
								<td>
									<?php if($row["m_tel"]){ ?>
										<input type="checkbox" class="listDataCheck2" id="listDataCheck_<?=$row["idx"]?>" data-idx="<?=$row["idx"]?>">
										<label class="ch" for="listDataCheck_<?=$row["idx"]?>">
										<i class="fas fa-check-square on"></i>
										<i class="far fa-square off"></i></label>
									<?php } else { ?>
										<span>-</span>
									<?php } ?>
								</td>
								<td><span class="name"><?=dhtml($row["m_name"])?></span><span class="lp05">(<?=$row["m_id"]?>)</span></td>
								<td class="lp05 tel"><?=($row["m_tel"]) ? $row["m_tel"] : "-"?></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
				
				<div class="sendWrap">
					<button type="button" class="typeBtn btnGray01 rightSectionCloseBtn">닫기</button>
					<button type="button" class="typeBtn btnMain receiveMemberAddBtn" style="float: right;">추가</button>
				</div>
			</div>
			<?php } ?>
			
			<div class="right dbTelList" style="display: none;">
				<div class="titWrap">DB 선택</div>
					<div class="searchWrap dbTelListSearchWrap">
						<ul class="formWrap">
							<li>
								<select class="txtBox" name="label">
									<option value="">구분</option>
									<option value="cs_name"><?=$customLabel["cs_name"]?></option>
									<option value="cs_tel"><?=$customLabel["cs_tel"]?></option>
								</select>
								<input type="text" class="txtBox value" name="value" value="<?=$_GET['value']?>">
							</li>
							<li class="drag">
								<select class="txtBox" name="setDate">
									<option value="">기간 구분</option>
									<option value="made">생산일자</option>
									<option value="reg">등록일시</option>
								</select>
								<input type="text" class="txtBox s_date" name="s_date" value="<?=$_GET['s_date']?>" dateonly>
								<span class="hypen">~</span>
								<input type="text" class="txtBox e_date" name="e_date" value="<?=$_GET['e_date']?>" dateonly>
								<span class="dateBtn" data-s="<?=date("Y-m-d")?>" data-e="<?=date("Y-m-d")?>">오늘</span>
								<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 7 days"))?>" data-e="<?=date("Y-m-d")?>">7일</span>
								<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 1 month"))?>" data-e="<?=date("Y-m-d")?>">1개월</span>
								<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 3 month"))?>" data-e="<?=date("Y-m-d")?>">3개월</span>
								<div class="btnWrap">
									<button type="button" class="typeBtn dbTelListSearchBtn">조회</button>
								</div>
							</li>
						</ul>
					</div>
				<div class="dbTelListLeftTit">
					<span class="cnt">TOTAL 0</span>
				</div>
				<div class="tableWrap head" style="margin-top: 0;">
					<table>
						<colgroup>
							<col width="10%">
							<col width="25%">
							<col width="35%">
							<col width="15%">
							<col width="15%">
						</colgroup>
						<thead>
							<tr>
								<th>
									<input type="checkbox" id="listDataAllCheck2">
									<label class="ch" for="listDataAllCheck2">
										<i class="fas fa-check-square on"></i>
										<i class="far fa-square off"></i>
									</label>
								</th>
								<th>이름</th>
								<th>연락처</th>
								<th>생산일자</th>
								<th>등록일시</th>
							</tr>
						</thead>
					</table>
				</div>
				
				<div class="tableWrap body" style="height: 400px;">
					<table>
						<colgroup>
							<col width="10%">
							<col width="25%">
							<col width="35%">
							<col width="15%">
							<col width="15%">
						</colgroup>
						<tbody>
						</tbody>
					</table>
				</div>
				
				<div class="sendWrap">
					<button type="button" class="typeBtn btnGray01 rightSectionCloseBtn">닫기</button>
					<button type="button" class="typeBtn btnMain receiveDbAddBtn" style="float: right;">추가</button>
				</div>
			</div>

			<div class="right templateList" style="display: none;">
				<div class="titWrap">템플릿 선택</div>
				<ul>
				<?php
					$value = array(''=>'');
					$query = "SELECT * FROM mt_sms_template WHERE use_yn = 'Y' AND basic_yn = 'N' ORDER BY idx DESC";
					$sql = list_pdo($query, $value);
					while($row = $sql->fetch(PDO::FETCH_ASSOC)){
				?>
					<li>
						<input type="text" class="txtBox" value="<?=dhtml($row["title"])?>" readonly>
						<textarea class="txtBox con" readonly><?=$row["contents"]?></textarea>
						<button type="button" class="typeBtn btnMain templateSelectBtn">선택</button>
					</li>
				<?php } ?>
				</ul>
				<div class="sendWrap">
					<button type="button" class="typeBtn btnGray01 rightSectionCloseBtn">닫기</button>
				</div>
			</div>
		</form>
	</div>
	
	<script type="text/javascript">
		function nowDate(){
			var date = new Date();
			var hour = date.getHours();
			var min = date.getMinutes();
			
			hour = (hour < 10) ? "0" + hour : hour;
			min = (min < 10) ? "0" + min : min;
			
			$("#smsSendFrmWrap .left  > .backgroundWrap > .timeSet").text(hour + ":" + min);
		}
		
		$(function(){
			
			setInterval(nowDate, 500);
			
			setTimeout(function(){
				$("#smsSendFrmWrap .left  > .smsLogVisualWrap > ul > li").addClass("active");
			}, 150);
			
			$("#smsSendFrmWrap .left  > .smsSendMsgWrap > textarea").focusin(function(){
				$("#smsSendFrmWrap .left  > .smsLogVisualWrap").addClass("active");
				
				var height = $("#smsSendFrmWrap .left  > .smsLogVisualWrap > ul").innerHeight();
				$("#smsSendFrmWrap .left  > .smsLogVisualWrap").animate({ scrollTop : height + "px" }, 500);
			});
			
			$("#smsSendFrmWrap .left  > .smsSendMsgWrap > textarea").focusout(function(){
				$("#smsSendFrmWrap .left  > .smsLogVisualWrap").removeClass("active");
				
				var height = $("#smsSendFrmWrap .left  > .smsLogVisualWrap > ul").innerHeight();
				$("#smsSendFrmWrap .left  > .smsLogVisualWrap").animate({ scrollTop : height + "px" }, 500);
			});
			
			$(document).on("change", ".listDataCheck", function(e){
				e.stopPropagation();

				var itemLen = $(".listDataCheck").length;
				var checkLen = $(".listDataCheck:checked").length;

				var item = $(".listDataCheck:checked");
				var idx = [];
				for(var i = 0; i < item.length; i++){
					idx.push($(item[i]).data("idx"));
				}

				idx.join(",");

				if(itemLen == checkLen){
					$("#listDataAllCheck").prop("checked", true);
				} else {
					$("#listDataAllCheck").prop("checked", false);
				}
			});
			
			$("#listDataAllCheck2").change(function(){
				$(".listDataCheck2").prop("checked", $(this).prop("checked"));

				var item = $(".listDataCheck2:checked");
				var idx = [];
				for(var i = 0; i < item.length; i++){
					idx.push($(item[i]).data("idx"));
				}

				idx.join(",");
			});
			
			$(document).on("change", ".listDataCheck2", function(e){
				e.stopPropagation();

				var itemLen = $(".listDataCheck2").length;
				var checkLen = $(".listDataCheck2:checked").length;

				var item = $(".listDataCheck2:checked");
				var idx = [];
				for(var i = 0; i < item.length; i++){
					idx.push($(item[i]).data("idx"));
				}

				idx.join(",");

				if(itemLen == checkLen){
					$("#listDataAllCheck2").prop("checked", true);
				} else {
					$("#listDataAllCheck2").prop("checked", false);
				}
			});
			
			$("#sendTelSelect").change(function(){
				var val = $(this).val();
				var name = $(this).find('option[value="' + val + '"]').text();
				name = name.split("(")[1];
				name = name.split(")")[0];
				
				$("#smsSendFrmWrap .left  > .backgroundWrap > .sendTelInfo").text(name);
			});
			
			$(".receiveAddBtn").click(function(){
				var name = $(this).closest("form").find(".receiveName").val();
				var tel = $(this).closest("form").find(".receiveTel").val();
				
				if(!name || !tel){
					alert("빈 칸을 입력해주시길 바랍니다.");
					return false;
				}
				
				$("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody .firstData").remove();
				var num = $("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody > tr").length + 1;
				var code = '<tr>';
				code += '<td>';
				code += '<input type="checkbox" class="listDataCheck" id="listDataCheck_' + num + '" data-idx="' + num + '">';
				code += '<label class="ch" for="listDataCheck_' + num + '">';
				code += '<i class="fas fa-check-square on"></i>';
				code += '<i class="far fa-square off"></i></label></td>';
				code += '<td><input type="hidden" name="name[]" value="' + name + '">' + name + '</td>';
				code += '<td>';
				code += '<input type="hidden" name="tel[]" value="' + tel + '">' + tel + '';
				code += '<input type="hidden" name="type[]" value="문자발송">';
				code += '</td>';
				code += '</tr>';
				
				$("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody").append(code);
			});
			
			$(".receiveDeleteBtn").click(function(){
				var item = $("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody > tr");
				for(var i = 0; i < item.length; i++){
					if($(item[i]).find(".listDataCheck").prop("checked")){
						$(item[i]).remove();
					}
				}
				
				var item = $("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody > tr");
				for(var i = 0; i < item.length; i++){
					var num = i + 1;
					$(item[i]).find(".listDataCheck").attr("id", "listDataCheck_" + num);
					$(item[i]).find("label").attr("for", "listDataCheck_" + num);
					$(item[i]).find(".listDataCheck").attr("data-idx", num);
				}
			});
			
			$(".receiveMemberAddBtn").click(function(){
				$("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody .firstData").remove();
				
				var item = $("#smsSendFrmWrap .right.memberTelList > .tableWrap > table tbody > tr");
				for(var i = 0; i < item.length; i++){
					if($(item[i]).find(".listDataCheck2").prop("checked")){
						var name = $(item[i]).find(".name").text();
						var tel = $(item[i]).find(".tel").text();
						
						var num = $("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody > tr").length + 1;
						var code = '<tr>';
						code += '<td>';
						code += '<input type="checkbox" class="listDataCheck" id="listDataCheck_' + num + '" data-idx="' + num + '">';
						code += '<label class="ch" for="listDataCheck_' + num + '">';
						code += '<i class="fas fa-check-square on"></i>';
						code += '<i class="far fa-square off"></i></label></td>';
						code += '<td><input type="hidden" name="name[]" value="' + name + '">' + name + '</td>';
						code += '<td>';
						code += '<input type="hidden" name="tel[]" value="' + tel + '">' + tel + '';
						code += '<input type="hidden" name="type[]" value="문자발송">';
						code += '</td>';
						code += '</tr>';

						$("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody").append(code);
					}
				}
				
				$(this).closest(".right").hide();
				$(this).closest("form").find("input[type='checkbox']").prop("checked", false);
				$("#smsSendFrmWrap .right.smsReceiveSetting").show();
			});
			
			$(".rightSectionCloseBtn").click(function(){
				$(this).closest(".right").hide();
				$(this).closest("form").find("input[type='checkbox']").prop("checked", false);
				$("#smsSendFrmWrap .right.smsReceiveSetting").show();
			});
			
			$(".rightSectionOpenBtn").click(function(){
				var target = $(this).data("target");
				
				$("#smsSendFrmWrap .right").hide();
				$("#smsSendFrmWrap .right." + target).show();
			});
			
			$(".templateSelectBtn").click(function(){
				var con = $(this).closest("li").find(".con").val();
				
				$("#smsSendFrmWrap .left  > .smsSendMsgWrap > textarea").val(con);
				$("#smsSendFrmWrap .left  > .smsSendMsgWrap > textarea").focus();
				
				$(this).closest(".right").hide();
				$(this).closest("form").find("input[type='checkbox']").prop("checked", false);
				$("#smsSendFrmWrap .right.smsReceiveSetting").show();
			});
			
			$(".smsSubmitBtn").click(function(){
				if(!$("#smsSendFrmWrap .left  > .smsSendMsgWrap > textarea").val()){
					alert("보낼 내용을 입력해주시길 바랍니다.");
					return false;
				}
				
				if(!$("#sendTelSelect").val()){
					alert("발신번호를 선택해주시길 바랍니다.");
					return false;
				}
				
				if($("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody > tr.firstData").length){
					alert("수신자를 설정해주시길 바랍니다.");
					return false;
				}
				
				if(!$("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody > tr").length){
					alert("수신자를 설정해주시길 바랍니다.");
					return false;
				}
				
				var data = $(this).closest("form").serialize();
				if(confirm("해당 내용으로 SMS를 전송하시겠습니까?")){
					console.log(data);
					loading(function(){
						$.ajax({
							url : "/ajax/sms/sendWP",
							type : "POST",
							data : data,
							success : function(result){
								var str = $("#smsSendFrmWrap .left  > .smsSendMsgWrap > textarea").val();
								str = str.replace(/(?:\r\n|\r|\n)/g, '<br />');
								var code = '<li class="send"><img src="/images/smsSendFrmSendBtm.png"><p>';
								code += "[Web발신]<br>" + str;
								code += '</p></li>';

								$("#smsSendFrmWrap .left  > .smsLogVisualWrap > ul").append(code);
								$("#smsSendFrmWrap .left  > .smsSendMsgWrap > textarea").val("");

								var height = $("#smsSendFrmWrap .left  > .smsLogVisualWrap > ul").innerHeight();
								$("#smsSendFrmWrap .left  > .smsLogVisualWrap").stop().animate({ scrollTop : height + "px" }, 500);

								setTimeout(function(){
									$("#smsSendFrmWrap .left  > .smsLogVisualWrap > ul > li").addClass("active");
									
									code = '<li class="receive"><img src="/images/smsSendFrmReceiveBtm.png"><p>';
									code += "[전송결과]<br>";
									code += "수신자 수 : " + result.totalCnt + "<br>";
									code += "성공 : " + result.successCnt + "<br>";
									code += "실패 : " + result.failCnt;
									code += '</p></li>';
									
									$("#smsSendFrmWrap .left  > .smsLogVisualWrap > ul").append(code);
									
									var height = $("#smsSendFrmWrap .left  > .smsLogVisualWrap > ul").innerHeight();
									$("#smsSendFrmWrap .left  > .smsLogVisualWrap").stop().animate({ scrollTop : height + "px" }, 500);
									
									setTimeout(function(){
										$("#smsSendFrmWrap .left  > .smsLogVisualWrap > ul > li").addClass("active");
									}, 300);
								}, 150);
								
								
								loadingClose();
							}
						})
					});
				}
			});
			
			$(document).on("focus focusin focusout change keyup keydown", "#smsSendFrmWrap .left > .smsSendMsgWrap > textarea", function(){
				$(this).prev().text(byteCheck($(this)) + " Byte");
			});
			
			$(".dbTelListSearchBtn").click(function() {
				let target = $(this).closest(".dbTelList").find("tbody");
				let formData = $('.dbTelListSearchWrap').find('input, select, textarea').serialize();

				if($("input[name='value']").val() && !$("select[name='label']").val()) {
					alert("구분을 선택해주세요.");
					return false;
				}

				if(($("input[name='s_date']").val() || $("input[name='e_date']").val()) && !$("select[name='setDate']").val()) {
					alert("기간 구분을 선택해주세요.");
					return false;
				}

				loading(function(){
					$.ajax({
						url : "/ajax/sms/getSmsDbList.php",
						type : "POST",
						data : formData,
						success : function(result) {
							if(result.status == "success") {
								let keyArray = Object.keys(result.data);
								let dbListHtml = '';
								keyArray.forEach(key => {
									if(result.data[key].cs_tel) {
										dbListHtml += `
											<tr>
												<td>
													<input type="checkbox" class="listDataCheck2" id="listDataCheck_${key}" data-idx="${key}">
													<label class="ch" for="listDataCheck_${key}">
													<i class="fas fa-check-square on"></i>
													<i class="far fa-square off"></i></label>
												</td>
										`;
									} else {
										dbListHtml += `<tr><td><span>-</span></td>`;
									}
									dbListHtml += `
											<td><span class="name">${result.data[key].cs_name}</span></td>
											<td class="lp05 tel">${result.data[key].cs_tel}</td>
											<td class="lp05">${result.data[key].made_date}</td>
											<td class="lp05">${result.data[key].reg_date}</td>
										</tr>
									`;
	
								});
								target.html(dbListHtml);
								$(".dbTelListLeftTit .cnt").html(`TOTAL ${keyArray.length.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`);
								loadingClose();
							} else {
								alert("검색된 데이터가 없습니다.");
								target.html("");
								$(".dbTelListLeftTit .cnt").html("TOTAL 0");
								loadingClose();
							}
						},
						error : function(reject) {
							alert("오류가 발생했습니다. 관리자에게 문의하세요.");
							target.html("");
							$(".dbTelListLeftTit .cnt").html("TOTAL 0");
							loadingClose();
						}
					})
				})
			})

			$(".receiveDbAddBtn").click(function(){
				$("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody .firstData").remove();
				
				var item = $("#smsSendFrmWrap .right.dbTelList > .tableWrap > table tbody > tr");
				for(var i = 0; i < item.length; i++){
					if($(item[i]).find(".listDataCheck2").prop("checked")){
						var name = $(item[i]).find(".name").text();
						var tel = $(item[i]).find(".tel").text();
						
						var num = $("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody > tr").length + 1;
						var code = '<tr>';
						code += '<td>';
						code += '<input type="checkbox" class="listDataCheck" id="listDataCheck_' + num + '" data-idx="' + num + '">';
						code += '<label class="ch" for="listDataCheck_' + num + '">';
						code += '<i class="fas fa-check-square on"></i>';
						code += '<i class="far fa-square off"></i></label></td>';
						code += '<td><input type="hidden" name="name[]" value="' + name + '">' + name + '</td>';
						code += '<td>';
						code += '<input type="hidden" name="tel[]" value="' + tel + '">' + tel + '';
						code += '<input type="hidden" name="type[]" value="문자발송">';
						code += '</td>';
						code += '</tr>';

						$("#smsSendFrmWrap .right.smsReceiveSetting > .tableWrap > table tbody").append(code);
					}
				}
				
				$(this).closest(".right").hide();
				$(this).closest("form").find("input[type='checkbox']").prop("checked", false);
				$("#smsSendFrmWrap .right.smsReceiveSetting").show();
			});

			<?php if($user["auth_code"] <= 002){ ?>
				/* 잔여수량 체크 */
				$.ajax({
					url : "/ajax/sms/getSmsCnt",
					success : function(result){
						$("#smsTotalCnt").text(result.totalCnt);
						$("#smsUseCnt").text(result.useCnt);
						$("#smsFinishCnt").text(result.finishCnt);
					}
				});
			<?php } ?>
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>