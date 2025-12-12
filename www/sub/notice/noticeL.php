<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 로그인된 계정 직급 영어이름 추출
	$value = array(':auth_code'=>$user['auth_code']);
	$query = "SELECT auth_type FROM mc_member_auth WHERE auth_code = :auth_code";
	$authEnName = view_pdo($query, $value)['auth_type'];

?>

	<style>
		
		#popupWrap { height: 100% !important; }
		
	</style>
	
	<div id="myNotificationListWrap">
		<ul class="notificationTypeWrap">
			<li data-type="all" class="this">전체</li>
		<?php
			$value = array(''=>'');
			$query = "SELECT * FROM mc_notification WHERE use_yn = 'Y' AND {$authEnName}_code = 'Y' ORDER BY sort ASC";
			$sql = list_pdo($query, $value);
			while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		?>
			<li data-type="<?=$row['type_code']?>"><?=$row['type_name']?></li>
		<?php } ?>
		</ul>
		
		<ul class="notificationWrap">
		<?php
			$value = array('m_idx'=>$user['idx']);
			$query = "
				SELECT MT.*
					, ( SELECT icon_color FROM mc_notification WHERE MT.type_code = type_code ) AS icon_color
					, ( SELECT icon_tag FROM mc_notification WHERE MT.type_code = type_code ) AS icon_tag
					, ( SELECT type_name FROM mc_notification WHERE MT.type_code = type_code ) AS type_name
				FROM mt_notification MT
				WHERE use_yn = 'Y' 
				AND m_idx = :m_idx
				ORDER BY idx DESC
			";
			$sql = list_pdo($query, $value);
			while($row = $sql->fetch(PDO::FETCH_ASSOC)){
				$class = "item{$row['type_code']}";
				$style = "";
				
				$class .= ($row['url']) ? " click" : "";
				$class .= (!$row['read_date']) ? " new" : "";
				
				$style .= (!$row['read_date']) ? "background-color: #{$row['icon_color']};" : "";
		?>
			<li class="<?=$class?>" data-url="<?=$row['url']?>">
				<div class="iconWrap" style="<?=$style?>">
					<?=$row['icon_tag']?>
				</div>
				<div class="infoWrap">
					<div class="title">
						<b><?=$row['type_name']?></b>
						<span><?=$row['content']?></span>
					</div>
					<div class="date"><?=$row['reg_date']?></div>
				</div>
			</li>
		<?php } ?>
		</ul>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			$(".notificationTypeWrap > li").click(function(){
				var type = $(this).data("type");
				
				$(".notificationTypeWrap > li").removeClass("this");
				$(this).addClass("this");
				
				$(".notificationWrap > li").hide();
				if(type == "all"){
					$(".notificationWrap > li").show();
				} else {
					$(".notificationWrap > li.item" + type).show();
				}
			});
			
			$(".notificationWrap > li.click").click(function(){
				var url = $(this).data("url");
				
				parent.window.location.href = url;
			});
			
		})
	</script>
	
	<?php

		# 모든 알림 읽음처리
		excute("UPDATE mt_notification SET read_date = now() WHERE m_idx = '{$user['idx']}' AND read_date IS NULL");

	?>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>	