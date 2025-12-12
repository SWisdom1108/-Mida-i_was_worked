<?php

	$view = view_sql("
		SELECT MT.*
			, ( SELECT {$userNameColum} FROM {$userTable} WHERE idx = MT.reg_idx ) AS reg_name
			, ( SELECT count(*) FROM mt_bbs_file WHERE bbs_idx = MT.idx ) AS file_cnt
			, ( SELECT COUNT(*) FROM mt_bbs_comment WHERE bbs_idx = MT.idx AND use_yn = 'Y' ) AS commentCnt 
		FROM mt_bbs MT
		WHERE use_yn = 'Y'
		{$groupQuery}
		AND idx = '{$_GET['idx']}'
		AND bbs_code = '{$bbsCode}'
	");

	if(!$view){
		include_once $_SERVER['DOCUMENT_ROOT']."/mida/module/bbs/bbsNoData.php";
		return false;
	}

	excute("UPDATE mt_bbs SET hit = {$view['hit']} + 1 WHERE idx = '{$view['idx']}'");

?>
<div class="bbsViewWrap">
	<div class="titWrap">
		<span class="tit"><?=bbsDhtml($view['title'])?></span>
		<p class="regInfo">
			<i class="fas fa-user-circle"></i>
			<span class="name"><?=$view['reg_name']?></span>
			<span class="etc">
				<span class="date"><?=date("Y-m-d H:i", strtotime($view['reg_date']))?></span>
				<span class="hit">조회 <?=number_format($view['hit'] + 1)?></span>
			</span>
		</p>
	</div>
	<div class="conWrap">
		<?=dhtml2(dhtml($view['content']))?>
		
		<div class="commentWrap">
			<div class="cntWrap">
				댓글 <?=number_format($view['commentCnt'])?> <i class="fas fa-angle-right"></i>
			</div>
			<div class="commentConWrap">
				<ul>
				<?php
					$sql = list_sql("
						SELECT MT.*
							, ( SELECT {$userNameColum} FROM {$userTable} WHERE idx = MT.reg_idx ) AS reg_name
						FROM mt_bbs_comment MT
						WHERE use_yn = 'Y'
						AND bbs_idx = '{$_GET['idx']}'
						ORDER BY idx ASC
					");
					foreach ( $sql as $row ){
				?>
					<li>
						<div class="infoWrap">
							<span class="name"><i class="fas fa-user-circle"></i><?=$row['reg_name']?></span>
							<span class="date"><?=$row['reg_date']?></span>
						<?php if($user['idx'] == $row['reg_idx']){ ?>
							<a href="#" class="commentDeleteBtn" data-idx="<?=$row['idx']?>">삭제</a>
						<?php } ?>
						</div>
						<div class="conWrap">
							<?=dhtml2($row['contents'])?>
						</div>
					</li>
				<?php } ?>
				</ul>
				<form id="commentFrm">
					<input type="hidden" name="idx" value="<?=$view['idx']?>">
					<textarea name="con"></textarea>
					<button type="submit">등록</button>
				</form>
			</div>
		</div>
	</div>
</div>