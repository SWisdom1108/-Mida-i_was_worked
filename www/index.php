<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php"; ?>
<?php if($user['auth_code'] > 003){ ?>
<?php

	# 긴급공지사항
	$notiSQL = "
		SELECT MT.*
		FROM mt_bbs MT
		WHERE use_yn = 'Y'
		{$groupQuery}
		AND bbs_code = '001'
		AND date_format(now(), '%Y-%m-%d') >= date_format(etc1, '%Y-%m-%d')
		AND date_format(now(), '%Y-%m-%d') <= date_format(etc2, '%Y-%m-%d')
		ORDER BY idx DESC
	";
	$notiInfo = list_sql($notiSQL);
	$notiInfo2 = list_sql($notiSQL);

?>

	<?php if(!$_COOKIE["importantPopup{$user['idx']}"]){ ?>
	<div id="importantPopupWrap">
		<div id="importantPopupBox">
			<div class="topWrap">
				<?=dhtml($cmpy['company_name'])?> 공지 안내
			</div>
			<ul class="conWrap">
			<?php $i = 0; foreach ( $notiInfo as $row ){ $i++; ?>
				<li class="noti_<?=$row['idx']?>"<?=($i == 1) ? ' style = "display: block;"' : ""?>>
					<div class="titWrap">
					 	<span class="big"><?=dhtml($row['title'])?></span>
					 	<span class="small"><?=date("Y년 m월 d일 H시 i분", strtotime($row['reg_date']))?></span>
					</div>
					<div class="infoWrap">
						<?php
              $value = array(':bbs_idx'=>$row['idx']);
              $query = "SELECT * FROM mt_bbs_file WHERE bbs_idx = :bbs_idx";
              $fileInfo = list_pdo($query, $value); 

              $query = "SELECT count(*) FROM mt_bbs_file WHERE bbs_idx = :bbs_idx";
              $fileCnt = view_pdo($query, $value)['count(*)'];
              if($fileCnt > 0){
            ?>
            <div class="fileWrap">
              <div class="fileBtn" onclick="$('.fileListWrap').toggle();" style="float: left;">
                <i class="fas fa-save"></i>첨부파일(<?=number_format($fileCnt)?>)
              </div>
              <div class="fileListWrap" style="position: relative; border: none;">
                <ul style="border: 1px solid #EEE; margin-top: 10px;">  
									<?php foreach ($fileInfo as $file) { ?>
                    <li>
                      <span class="filename"><?=$file['filename_r']?></span>
                      <a href="/mida/module/bbs/fileDown.php?idx=<?=$file['idx']?>"><i class="fas fa-download"></i></a>
                    </li>
									<?php } ?>
								</ul>
							</div>
						</div>
					<?php } ?>
					<div style="width: 100%; float: left; margin-top: 10px;">
						<?=dhtml2(dhtml($row['content']))?>
					</div>
				</li>
			<?php } ?>
			</ul>
			<ul class="navWrap">
			<?php $i = 0; foreach ( $notiInfo2 as $row ){ $i++; ?>
				<li data-idx="<?=$row['idx']?>"<?=($i == 1) ? ' class = "active"' : ""?>></li>
			<?php } ?>
			</ul>
			<div class="btmWrap">
				<a href="#" class="importantPopupTodayCloseBtn">오늘하루 열지않기</a>
				<a href="#" class="importantPopupCloseBtn">닫기</a>
			</div>
		</div>
		<div class="prev_btn popup_btns" style="left: 25%;">
			<i class="fas fa-chevron-left"></i>
		</div>
		<div class="next_btn popup_btns" style="right: 25%;">
			<i class="fas fa-chevron-right"></i>
		</div>
	</div>
	<?php } ?>

	
<style>
  .fileWrap > .fileBtn { float: right; cursor: pointer; font-size: 15px; font-weight: 400; letter-spacing: -0.5px; color: #555; border: 1px solid #EEE; padding: 8px 13px; transition: border 0.5s; }
  .fileWrap > .fileBtn:hover { border: 1px solid #CCC; }
  .fileWrap > .fileBtn > i { margin-right: 7px; color: #3366CC; }
  .fileWrap > .fileListWrap { position: absolute; border: 1px solid #EEE; top: 100%; right: 0; background-color: #FFF; z-index: 10; width: 250px; display: none; }
  .fileWrap > .fileListWrap > ul { width: 100%; float: left; padding: 15px; padding-top: 10px; }
  .fileWrap > .fileListWrap > ul > li { width: 100%; float: left; padding-top: 5px; }
  .fileWrap > .fileListWrap > ul > li > span { float: left; width: 92%; font-size: 13px; color: #666; letter-spacing: -0.5px; font-weight: 400; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
  .fileWrap > .fileListWrap > ul > li > a { float: left; width: 8%; font-size: 13px; color: #333; letter-spacing: -0.5px; font-weight: 400; text-align: right; }
</style>

	<script>
		function setCookie(cname, value, expire) {
		   var todayValue = new Date();

		   todayValue.setDate(todayValue.getDate() + expire);
		   document.cookie = cname + "=" + encodeURI(value) + "; expires=" + todayValue.toGMTString() + "; path=/;";
		}
		
		// 이전 이후 버튼 함수
		function changeNav(nav){ 
			if(nav.length == 0){
				nav = $("#importantPopupBox .navWrap > li:first");
			}
			nav.trigger("click");
		}

		$(function(){

			// 이전 이후 버튼 클릭 이벤트
			$(".prev_btn").click(function(){
				var $activeNav = $("#importantPopupBox .navWrap > li.active");
				var $prevNav = $activeNav.prev();
				changeNav($prevNav);
			});
			
			$(".next_btn").click(function(){
				var $activeNav = $("#importantPopupBox .navWrap > li.active");
				var $nextNav = $activeNav.next();
				changeNav($nextNav);
			});
			
			if($("#importantPopupWrap").length > 0){
				if($("#importantPopupBox .conWrap > li").length > 0){
					$("#importantPopupWrap").css("opacity", 1);
				} else {
					$("#importantPopupWrap").remove();
				}
			}
			
			$("#importantPopupBox .navWrap > li").click(function(){
				var idx = $(this).data("idx");
				
				$("#importantPopupBox .conWrap > li").hide();
				$("#importantPopupBox .navWrap > li").removeClass("active");
				
				$("#importantPopupBox .conWrap > li.noti_" + idx).show();
				$(this).addClass("active");
			});
			
			$(".importantPopupCloseBtn").click(function(e){
				e.preventDefault();
				
				$("#importantPopupWrap").remove();
			});
			
			$(".importantPopupTodayCloseBtn").click(function(e){
				e.preventDefault();
				
				setCookie("importantPopup<?=$user['idx']?>", "end" , 1);
				$("#importantPopupWrap").remove();
			});
			
		})
	</script>

<?php } ?>
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/dashboard.php"; ?>
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>