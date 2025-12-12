			</div>
			
			<div id="goToTopWrap">
				<button type="button" onclick="$('html, body').animate({ scrollTop : 0 }, 0);">TOP</button>
			</div>
			<div id="footerWrap">
				<ul>
					<li>
						<span>상호명 : <?=dhtml($mainCmpy['company_name'])?></span>
						<span class="line">|</span>
						<span>대표자 : <?=dhtml($mainCmpy['ceo_name'])?></span>
						<span class="line">|</span>
						<span>개인정보관리책임자 : <?=dhtml($mainCmpy['ceo_name'])?></span><br>
						<span>주소 : <?=dhtml($mainCmpy['company_addr'])?></span>
						<span class="noti">Copyrights(C) 2020 Midaworks. Ltd </span>
					</li>
				</ul>
			</div>
		</div>

		<?php 
			$topLogoPath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['top_logo'];
			$topLogo = getEncodedImage($topLogoPath);
			if($user['auth_code'] == '004'){
				$my_url = "/m/sub/db/dbDistL?code={$user['idx']}";
			}else{
				$my_url = "/m/sub/db/dbL";
			}
			
		?>

		<style type="text/css">
			.dist_alarm{ position: fixed; z-index: 999; width : 80%; height: 40px;/* border : 2px solid #ccc;*/ border-radius: 50px; background-color: #fff; left : 10%; top: -10%; padding : 5px 10px; transition: all 0.5s; box-shadow: 0 0 0px 1px #888; }
			.dist_alarm div{ float: left; }
			
		</style>
		<?php if($user['auth_code'] > 003 && $alarm_module == 'Y'){?>
			<div class="dist_alarm" onclick="location.href='<?=$my_url?>'">
				<div style="width : 100%;" >
					<img src="<?=($topLogo) ? "data:image/jpg;base64,".$topLogo : "/images/topLogo_01.png"?>" style="width : 30px; height : 30px; float : left;" alt="DBMG">
					<div style="width : calc(100% - 50px); float : left; padding-left : 45px; height : 30px; line-height: 30px;">새로운 DB가 분배되었습니다.</div>
				</div>
			</div>
		<?php }?>
		
		<script type="text/javascript">
			setCookie("listCheckData", "");
			var searchLabel = "<?=$_GET['label']?>";
			
			(searchLabel) ? $("select[name='label']").val(searchLabel) : "";
			$("#orderBy").val("<?=$orderBy?>");
			
			$(".simpleSearchWrap form").append('<input type="hidden" name="orderBy" value="<?=$orderBy?>">');
			$(".simpleSearchWrap form").append('<input type="hidden" name="listCnt" value="<?=$listCnt?>">');
			
			$(".searchWrap form").append('<input type="hidden" name="orderBy" value="<?=$orderBy?>">');
			$(".searchWrap form").append('<input type="hidden" name="listCnt" value="<?=$listCnt?>">');

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
									$(".dist_alarm").css("top","10%");
									setTimeout(function() {
										$.ajax({
											url : "/ajax/my/dbAlarmUP",
											type : "POST",
											data : { idx : "<?=$user['idx']?>" },
											success : function(result){
												console.log("초기화");
											}
										})
									},1000)
									setTimeout(function() {
										$(".dist_alarm").css("top","-10%");
										alarmStart(alarm_interval);
									},4000)
								}
							}
						})

					},5000)
				}

				alarmStart(alarm_interval);
			<?php }?>
		</script>