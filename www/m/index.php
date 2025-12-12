<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/header.php"; ?>
<?php

	$nowDate = date("Y-m-d");

	# 권한에 따른 데이터 정리표
	switch($user['auth_code']){
		case "001" :
		case "002" :
			$dataCnt = view_sql("
				SELECT 
					  ( SELECT COUNT(*) FROM mt_db WHERE 1 = 1 ) AS totalDB
					, ( SELECT COUNT(*) FROM mt_db WHERE use_yn = 'N' ) AS totalDeleteDB
					, ( SELECT COUNT(*) FROM mt_db WHERE dist_code = '002' AND dist_date LIKE '{$nowDate}%' ) AS distDB
					, ( SELECT COUNT(*) FROM mt_db WHERE dist_code = '002' AND dist_date LIKE '{$nowDate}%' AND use_yn = 'N' ) AS distDeleteDB
				FROM dual
			");
			break;
		case "004" :
			$dataCnt = view_sql("
				SELECT 
					  ( SELECT COUNT(*) FROM mt_db WHERE tm_code = '{$user['tm_code']}' AND dist_code = '002' ) AS totalDB
					, ( SELECT COUNT(*) FROM mt_db WHERE tm_code = '{$user['tm_code']}' AND dist_code = '002' AND use_yn = 'N' ) AS totalDeleteDB
					, ( SELECT COUNT(*) FROM mt_db WHERE tm_code = '{$user['tm_code']}' AND dist_code = '002' AND dist_date LIKE '{$nowDate}%' ) AS distDB
					, ( SELECT COUNT(*) FROM mt_db WHERE tm_code = '{$user['tm_code']}' AND dist_code = '002' AND dist_date LIKE '{$nowDate}%' AND use_yn = 'N' ) AS distDeleteDB
				FROM dual
			");
			break;
		case "005" :
			$dataCnt = view_sql("
				SELECT 
					  ( SELECT COUNT(*) FROM mt_db WHERE tm_code = '{$user['tm_code']}' AND dist_code = '002' AND m_idx = '{$user['idx']}' ) AS totalDB
					, ( SELECT COUNT(*) FROM mt_db WHERE tm_code = '{$user['tm_code']}' AND dist_code = '002' AND m_idx = '{$user['idx']}' AND use_yn = 'N' ) AS totalDeleteDB
					, ( SELECT COUNT(*) FROM mt_db WHERE tm_code = '{$user['tm_code']}' AND dist_code = '002' AND m_idx = '{$user['idx']}' AND dist_date LIKE '{$nowDate}%' ) AS distDB
					, ( SELECT COUNT(*) FROM mt_db WHERE tm_code = '{$user['tm_code']}' AND dist_code = '002' AND m_idx = '{$user['idx']}' AND dist_date LIKE '{$nowDate}%' AND use_yn = 'N' ) AS distDeleteDB
				FROM dual
			");
			break;
	}

?>

	<!-- 대시보드 -->
	 <div id="dashboardWrap">
	 	<div class="background" <?=(!$user['pm_code']) ? 'style="height: 280px;"' : ""?>></div>
	 	
	 	<div class="userInfoWrap">
	 		<div class="left"><i class="far fa-user-circle"></i>안녕하세요!</div>
	 		<div class="right"><?=$user['m_name']?>(<?=$user['m_id']?>)님!</div>
	 	</div>
	 	
	 	<div class="dataCntWrap">
	 		<ul>
	 			<li>
	 				<span class="cnt"><?=number_format($dataCnt['totalDB'])?> <span style="opacity: 0.5; font-weight: 400;">( <i class="fas fa-trash-alt"></i> <?=number_format($dataCnt['totalDeleteDB'])?> )</span> </span>
	 				<span class="label">전체 DB</span>
	 			</li>
	 			<li>
	 				<span class="cnt"><?=number_format($dataCnt['distDB'])?> <span style="opacity: 0.5; font-weight: 400;">( <i class="fas fa-trash-alt"></i> <?=number_format($dataCnt['distDeleteDB'])?> )</span></span>
	 				<span class="label">오늘의 분배</span>
	 			</li>
	 		</ul>
	 	</div>
	 	
	 	<div class="quickMenuWrap">
	 		<ul>
	 		<?php if($user['auth_code'] == "001" || $user['auth_code'] == "002"){ ?>
	 			<li>
	 				<a href="/m/sub/db/dbAllL">
	 					<img src="/m/images/dashboard/cloud.jpg">
	 					<span>DB통합관리</span>
	 				</a>
	 			</li>
	 			<li>
	 				<a href="/m/sub/db/dbTeamL">
	 					<img src="/m/images/dashboard/users.jpg">
	 					<span>DB분배관리</span>
	 				</a>
	 			</li>
	 		<?php } else { ?>
	 			<li>
	 				<a href="/m/sub/db/dbL">
	 					<img src="/m/images/dashboard/cloud.jpg">
	 					<span>DB통합관리</span>
	 				</a>
	 			</li>
				<?php if($user['auth_code'] == "004"){ ?>
					<li>
						<a href="/m/sub/db/dbDistL">
							<img src="/m/images/dashboard/users.jpg">
							<span>DB분배관리</span>
						</a>
					</li>
				<?php } ?>
	 		<?php } ?>
	 		</ul>
	 	</div>
	 	
		<div class="noticeListWrap">
			<div class="titWrap">
				<div class="left">공지사항</div>
				<div class="right">
					<a href="/m/sub/bbs/bbs?inc=L"><i class="fas fa-ellipsis-v"></i></a>
				</div>
			</div>
			<ul>
			<?php
				$sql = "
					SELECT MT.*
					FROM mt_bbs MT
					WHERE use_yn = 'Y'
					AND bbs_code = '001'
					ORDER BY idx DESC
					LIMIT 0, 3
				";
				$result = list_sql($sql);
				foreach ( $result as $row ){
			?>
				<li>
					<a href="/m/sub/bbs/bbs?inc=V&idx=<?=$row['idx']?>"><?=$row['title']?></a>
					<span class="date"><?=date("Y-m-d", strtotime($row['reg_date']))?></span>
				</li>
			<?php } ?>
			</ul>
		</div>
	 </div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/footer.php"; ?>