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
		<span class="tit"><?=($view['important_yn'] == "Y") ? ' <b class="importantData">긴급</b>' : ''?><?=($view['noti_yn'] == "Y") ? ' <b class="notiData">공지</b>' : ''?><?=bbsDhtml($view['title'])?></span>
		<p class="regInfo">
			<span class="name"><i class="fas fa-user-circle"></i><?=$view['reg_name']?></span>
			<span class="date"><?=date("Y-m-d H:i", strtotime($view['reg_date']))?></span>
		</p>
	</div>
	<?php if($view['file_cnt'] > 0){ ?>
		<div class="fileWrap">
			<div class="fileBtn" onclick="$('.fileListWrap').toggle();" style="float: left;">
				<i class="fas fa-save"></i>첨부파일(<?=number_format($view['file_cnt'])?>)
			</div>
			<div class="fileListWrap" style="position: relative; border: none;">
				<ul style="border: 1px solid #EEE; margin-top: 10px;">
				<?php
					$sql = "
						SELECT *
						FROM mt_bbs_file
						WHERE bbs_idx = '{$view['idx']}'
						ORDER BY filename_r ASC
					";
					$result = list_sql($sql);
					foreach ( $result as $row ) {
				?>
					<li>
						<span class="filename"><?=$row['filename_r']?></span>
						<a href="/mida/module/bbs/fileDown.php?idx=<?=$row['idx']?>"><i class="fas fa-download"></i></a>
					</li>
				<?php } ?>
				</ul>
			</div>
		</div>
	<?php } ?>
	<div class="conWrap">
		<?=dhtml($view['content'])?>
		
		<div class="commentWrap">
			<div class="cntWrap">
				댓글 <?=number_format($view['commentCnt'])?>
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
							<?=dhtml($row['contents'])?>
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