				<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerGuide.php"; ?>
			</div>
			
			<div id="footerWrap">
				<ul>
					<li>
						<span class="label">상호명</span>
						<span class="value"><?=(dhtml($mainCmpy['company_name'])) ? dhtml($mainCmpy['company_name']) : "-"?></span>
						<span class="line">|</span>
						<span class="label">사업자등록번호</span>
						<span class="value"><?=($mainCmpy['company_num']) ? $mainCmpy['company_num'] : "-"?></span>
						<span class="line">|</span>
						<span class="label">대표자</span>
						<span class="value"><?=($mainCmpy['ceo_name']) ? $mainCmpy['ceo_name'] : "-"?></span>
						<span class="line">|</span>
						<span class="label">개인정보관리책임자</span>
						<span class="value"><?=($mainCmpy['ceo_name']) ? $mainCmpy['ceo_name'] : "-"?></span>
						<button class="label" onclick='popupControl_privacy("open", "privacy", "/sub/privacy/privacy.php", " 디비매니저")' style="float:right;width: 62px; height:22px; border-radius:5px; background-color:#222222; color:#FFFFFF; font-size:10px; margin-left:5px;">상세보기</button>
						<span class="label" style="float:right; color:#CFCFCF; font-size:11px; padding-top:3px;">시스템 약관내용</span>
					</li>
					<li>
						<span class="label">연락처</span>
						<span class="value"><?=($mainCmpy['company_tel']) ? $mainCmpy['company_tel'] : "-"?></span>
						<span class="line">|</span>
						<span class="label">주소</span>
						<span class="value"><?=(dhtml($mainCmpy['company_addr'])) ? dhtml($mainCmpy['company_addr']) : "-"?></span>
					</li>
				</ul>
			</div>
		</div>
		<style type="text/css">
			.dist_alarm{ position: fixed; z-index: 999; width : 300px; height: 100px; border : 2px solid #ccc; background-color: #f7f7f7; right: 0; bottom: -100px; padding : 5px 10px; transition: all 0.5s linear; }
			.dist_alarm div{ float: left; }
			.dist_alarm .close{ position : absolute; right : 7px; top : 0; color : #666; cursor: pointer; font-size: 18px; }
			.dist_alarm .close:hover{ color : #999 !important; }
			
		</style>
		<?php if($user['auth_code'] > 003 && $alarm_module == 'Y'){?>
			<div class="dist_alarm" onclick="location.href='/sub/db/dbMyL'">
				<div class="close"><i class="fas fa-times"></i></div>
				<div><?=$site['site_name']?></div>
				<div style="width : 100%; margin-top: 5px;" >
					<img src="<?=($topLogo) ? "data:image/jpg;base64,".$topLogo : "/images/topLogo_01.png"?>" style="width : 50px; height : 50px; float : left;" alt="DBMG">
					<div style="width : calc(100% - 50px); float : left; padding-left : 25px; height : 50px; line-height: 50px;">새로운 DB가 분배되었습니다.</div>
				</div>
			</div>
		<?php }?>
		
		<script type="text/javascript">
			guideLabelSetting("<?=$customLabel["tm"]?>", "<?=$customLabel["fc"]?>");
			setCookie("listCheckData", "");
			var searchLabel = "<?=$_GET['label']?>";
			
			(searchLabel) ? $("select[name='label']").val(searchLabel) : "";
			$("#orderBy").val("<?=$orderBy?>");
			$("#listCnt").val("<?=$listCnt?>");
			$("#overlap_yn").val("<?=$overlap_yn?>");
			
			$(".simpleSearchWrap form").append('<input type="hidden" name="orderBy" value="<?=$orderBy?>">');
			$(".simpleSearchWrap form").append('<input type="hidden" name="listCnt" value="<?=$listCnt?>">');
			$(".simpleSearchWrap form").append('<input type="hidden" name="overlap_yn" value="<?=$overlap_yn?>">');
			
			$(".searchWrap form").append('<input type="hidden" name="orderBy" value="<?=$orderBy?>">');
			$(".searchWrap form").append('<input type="hidden" name="listCnt" value="<?=$listCnt?>">');
			$(".simpleSearchWrap form").append('<input type="hidden" name="overlap_yn" value="<?=$overlap_yn?>">');

			<?php if($user['auth_code'] > 003 && $alarm_module == 'Y'){?>
				var alarm_interval;

				function alarmStart(alarm) {
					alarm = setInterval(function() {
						$.ajax({
							url : "/ajax/my/dbAlarm",
							type : "POST",
							data : { idx : "<?=$user['idx']?>" },
							success : function(result){
								if(result > 0){
									clearInterval(alarm);
									$(".dist_alarm").css("bottom","0");
									setTimeout(function() {
										$.ajax({
											url : "/ajax/my/dbAlarmUP",
											type : "POST",
											data : { idx : "<?=$user['idx']?>" },
											success : function(result){
												console.log("초기화");
											}
										})
									},2000)
								}
							}
						})

					},5000)
				}

				alarmStart(alarm_interval);

					

				$(".dist_alarm .close").click(function(e) {
					e.stopPropagation();
					$(".dist_alarm").css("display","none");
					$(".dist_alarm").css("bottom","-100px");
					setTimeout(function() {
						$(".dist_alarm").css("display","block");
						alarmStart(alarm_interval);
					},1000)
				})
			<?php }?>

			function popupControl_privacy(type, target, url, name, closeReload, guide){
				switch(type){
					case "open" :
						closeReload = (closeReload == undefined) ? false : closeReload;
						var code = `<div class="popupWrap" style = "padding:0px 0px;"id="popupBox_` + target + `"><div class="popupBox" style = "width:640px; height:100%; margin-top:0; top:0; margin-left:-300px;"><div class="titWrap"><div class="left"><span>` + name +`</span><span style="margin-left:5px;">개인정보처리지침</span>`;
						if(guide){
							code += `<span class="guide">` + guide + `</span>`;
						}
						code += `</div><div class="right"><i class="far fa-times-circle popupCloseBtns" data-reload="` + closeReload + `"></i></div></div><div class="frameWrap"><iframe src="` + url + `"></iframe></div></div></div>`;
						$("body").append(code);
						target = $("#popupBox_" + target);
					
						$(target).fadeIn(350);
						break;
					case "close" :
						target = $("#popupBox_" + target);
					
						$(target).fadeOut(350, function(){
							$(target).remove();
						});
						break;
				}
			}
		</script>