<?php

	$sql = list_sql("SELECT * FROM mt_bbs {$andQuery} ORDER BY idx DESC {$limitSQL}");
	$sql2 = list_sql("SELECT * FROM mt_bbs WHERE use_yn = 'Y' AND bbs_code = '{$bbsCode}' AND noti_yn = 'Y' ORDER BY idx DESC");

?>
<div class="bbsFAQWrap">
	<ul>
		<?php foreach($sql2 as $row){ ?>
			<li class="notiRow">
				<div class="titWrap">
					<div class="iconWrap">Q</div>
					<div class="conWrap"><?=bbsDhtml($row['title'])?></div>
					<div class="statusWrap">
						<i class="fas fa-angle-up upIcon"></i>
						<i class="fas fa-angle-down downIcon"></i>
					</div>
				</div>
				<div class="infoWrap">
					<div class="iconWrap">A</div>
					<div class="conWrap">
						<?=dhtml2($row['content'])?>
					</div>
					<div class="dateWrap">
						<span class="label">작성일</span>
						<span class="value"><?=date("Y-m-d H:i", strtotime($row['reg_date']))?></span>
					</div>
				</div>
			</li>
		<?php } ?>
		<?php foreach ( $sql as $row ){?>
			<li>
				<div class="titWrap">
					<div class="iconWrap">Q</div>
					<div class="conWrap"><?=bbsDhtml($row['title'])?></div>
					<div class="statusWrap">
						<i class="fas fa-angle-up upIcon"></i>
						<i class="fas fa-angle-down downIcon"></i>
					</div>
				</div>
				<div class="infoWrap">
					<div class="iconWrap">A</div>
					<div class="conWrap">
						<?=dhtml2($row['content'])?>
					</div>
					<div class="dateWrap">
					<?php if($user['idx'] == $row['reg_idx']){ ?>
						<a class="label" href="<?=$bbsPath?>?bbs=<?=$bbsCode?>&inc=U&idx=<?=$row['idx']?>"><i class="fas fa-edit"></i>수정</a>
						<a class="label bbsDeleteBtn" href="#" data-bbs="<?=$bbsCode?>" data-idx="<?=$row['idx']?>"><i class="fas fa-trash-alt"></i>삭제</a>
					<?php } ?>
						<span class="label">작성일</span>
						<span class="value"><?=date("Y-m-d H:i", strtotime($row['reg_date']))?></span>
					</div>
				</div>
			</li>
		<?php } ?>
		<?php if($totalCnt == 0){ ?>
			<li class="nodata">
				게시글이 존재하지 않습니다.
			</li>
		<?php } ?>
	</ul>
</div>

<script>
	$(".bbsFAQWrap li > .titWrap").click(function(){
		if($(this).parent("li").hasClass("active")){
			$(".bbsFAQWrap li").removeClass("active");
		} else {
			$(".bbsFAQWrap li").removeClass("active");
			$(this).parent("li").addClass("active");
		}
	});
</script>