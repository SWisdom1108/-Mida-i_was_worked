<?php

	# 200928 일정카운팅
	$todayCnt = date("Y-m-d");
	$scheduleLogList = [];
	$value = array(':use_yn'=>'Y',':reg_idx'=>$user["idx"]);
	$query = "SELECT schedule_idx FROM mt_schedule_log WHERE use_yn = :use_yn AND reg_idx = :reg_idx AND reg_date LIKE '{$todayCnt}%'";
	$sql = list_pdo($query, $value);
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		array_push($scheduleLogList, $row["schedule_idx"]);
	}
	$scheduleLogList = implode(",", $scheduleLogList);
	$scheduleQuery = ($scheduleLogList) ? " AND idx NOT IN ( {$scheduleLogList} )" : "";
	$scheduleLogList = explode(",", $scheduleLogList);

	switch($user["auth_code"]){
		case "004" :
			$scheduleQuery .= " AND ( tm_code = '{$user["tm_code"]}' OR share_all_yn = 'Y' )";
			break;
		case "005" :
			$scheduleQuery .= " AND ( ( reg_idx = '{$user["idx"]}' OR share_all_yn = 'Y' ) OR ( share_tm_yn = 'Y' AND tm_code ='{$user["tm_code"]}') )";
			break;
		case "006" :
			$scheduleQuery .= " AND ( reg_idx = '{$user["idx"]}' OR share_all_yn = 'Y' )";
			break;
		case "007" :
			$scheduleQuery .= " AND ( reg_idx = '{$user["idx"]}' OR share_all_yn = 'Y' )";
			break;
			
	}

	$value = array(':use_yn'=>'Y');
	$query = "
		SELECT COUNT(*) AS cnt
		FROM mt_schedule
		WHERE use_yn = 'Y'
		AND s_date LIKE '{$todayCnt}%'
		{$scheduleQuery}
	";
	$todayScheduleCnt = view_pdo($query, $value)["cnt"];
	$todayScheduleCnt = ($todayScheduleCnt) ? "<span class='cnt'>{$todayScheduleCnt}</span>" : "";

?>

<?php if($user['auth_code'] == "001"){ # 최고관리자일 경우 ?>
	<li id="mainMenu_my"><a href="/sub/my/myV" title="기본설정"><i class="fas fa-cog"></i>기본설정</a></li>
	<li id="mainMenu_group"><a href="/sub/group/adminL" title="조직관리"><i class="fas fa-sitemap"></i>조직관리</a></li>
	<li id="mainMenu_db_setting"><a href="/sub/db_setting/columnU" title="DB관리설정"><i class="fas fa-user-cog"></i>DB관리설정</a></li>
	<li id="mainMenu_db"><a href="/sub/db/dbAllL" title="DB관리"><i class="fas fa-id-card"></i>DB관리</a></li>
	<li id="mainMenu_db_recall"><a href="/sub/db_recall/dbRecallL" title="회수DB관리"><i class="fas fa-id-card"></i>회수DB관리</a></li>
	<li id="mainMenu_schedule"><a href="/sub/schedule/scheduleL" title="일정관리"><i class="fas fa-calendar-alt"></i>일정관리<?=$todayScheduleCnt?></a></li>
  <li id="mainMenu_db_dent"><a href="/sub/db_dent/dbDentL" title="덴트웹DB관리"><i class="fas fa-id-card"></i>덴트웹DB관리</a></li>
	<li id="mainMenu_chart"><a href="/sub/chart/groupL" title="통계"><i class="fas fa-chart-line"></i>통계</a></li>
	<li id="mainMenu_bbs"><a href="/sub/bbs/bbs?bbs=001&inc=L" title="커뮤니티"><i class="fas fa-headset"></i>커뮤니티</a></li>
	<li id="mainMenu_sms" style="float: right;"><a href="/sub/sms/sendW" title="SMS"><i class="fas fa-envelope"></i>SMS</a></li>
<?php } ?>

<?php if($user['auth_code'] == "002"){ # 관리자일 경우 ?>
	<li id="mainMenu_my"><a href="/sub/my/myV" title="기본설정"><i class="fas fa-cog"></i>기본설정</a></li>
	<li id="mainMenu_group"><a href="/sub/group/pmL" title="조직관리"><i class="fas fa-sitemap"></i>조직관리</a></li>
	<li id="mainMenu_db_setting"><a href="/sub/db_setting/columnU" title="DB관리설정"><i class="fas fa-user-cog"></i>DB관리설정</a></li>
	<li id="mainMenu_db"><a href="/sub/db/dbAllL" title="DB관리"><i class="fas fa-id-card"></i>DB관리</a></li>
	<li id="mainMenu_schedule"><a href="/sub/schedule/scheduleL" title="일정관리"><i class="fas fa-calendar-alt"></i>일정관리<?=$todayScheduleCnt?></a></li>
  <li id="mainMenu_db_dent"><a href="/sub/db_dent/dbDentL" title="덴트웹DB관리"><i class="fas fa-id-card"></i>덴트웹DB관리</a></li>
	<li id="mainMenu_chart"><a href="/sub/chart/groupL" title="통계"><i class="fas fa-chart-line"></i>통계</a></li>
	<li id="mainMenu_bbs"><a href="/sub/bbs/bbs?bbs=001&inc=L" title="커뮤니티"><i class="fas fa-headset"></i>커뮤니티</a></li>
	<li id="mainMenu_sms" style="float: right;"><a href="/sub/sms/sendW" title="SMS"><i class="fas fa-envelope"></i>SMS</a></li>
<?php } ?>

<?php if($user['auth_code'] == "003"){ # 생산마스터일 경우 ?>
	<li id="mainMenu_my"><a href="/sub/my/myV" title="기본설정"><i class="fas fa-cog"></i>기본설정</a></li>
	<li id="mainMenu_db"><a href="/sub/db/dbDistL" title="DB관리"><i class="fas fa-id-card"></i>DB관리</a></li>
	<li id="mainMenu_chart"><a href="/sub/chart/dbDistL" title="통계"><i class="fas fa-chart-line"></i>통계</a></li>
	<li id="mainMenu_bbs"><a href="/sub/bbs/bbs?bbs=001&inc=L" title="커뮤니티"><i class="fas fa-headset"></i>커뮤니티</a></li>
<?php } ?>

<?php if($user['auth_code'] == "004"){ # 팀마스터일 경우 ?>
	<li id="mainMenu_my"><a href="/sub/my/myV" title="기본설정"><i class="fas fa-cog"></i>기본설정</a></li>
	<li id="mainMenu_group"><a href="/sub/group/myTeamMemberL" title="조직관리"><i class="fas fa-sitemap"></i>조직관리</a></li>
	<li id="mainMenu_db"><a href="/sub/db/dbMyTeamL" title="DB관리"><i class="fas fa-id-card"></i>DB관리</a></li>
	<li id="mainMenu_chart"><a href="/sub/chart/dbMyTeamL" title="통계"><i class="fas fa-chart-line"></i>통계</a></li>
	<li id="mainMenu_bbs"><a href="/sub/bbs/bbs?bbs=001&inc=L" title="커뮤니티"><i class="fas fa-headset"></i>커뮤니티</a></li>
	<li id="mainMenu_schedule" style="float: right;">
		<a href="/sub/schedule/scheduleL" title="일정관리">
			<i class="fas fa-calendar-alt"></i>일정관리<?=$todayScheduleCnt?>
		</a>
	</li>
	<li id="mainMenu_sms" style="float: right;"><a href="/sub/sms/sendW" title="SMS"><i class="fas fa-envelope"></i>SMS</a></li>
<?php } ?>

<?php if($user['auth_code'] == "005"){ # 담당자일 경우 ?>
	<li id="mainMenu_my"><a href="/sub/my/myV" title="기본설정"><i class="fas fa-cog"></i>기본설정</a></li>
	<li id="mainMenu_db"><a href="/sub/db/dbMyL" title="DB관리"><i class="fas fa-id-card"></i>DB관리</a></li>
	<li id="mainMenu_chart"><a href="/sub/chart/dbMyL" title="통계"><i class="fas fa-chart-line"></i>통계</a></li>
	<li id="mainMenu_bbs"><a href="/sub/bbs/bbs?bbs=001&inc=L" title="커뮤니티"><i class="fas fa-headset"></i>커뮤니티</a></li>
	<li id="mainMenu_schedule" style="float: right;">
		<a href="/sub/schedule/scheduleL" title="일정관리">
			<i class="fas fa-calendar-alt"></i>일정관리<?=$todayScheduleCnt?>
		</a>
	</li>
	<li id="mainMenu_sms" style="float: right;"><a href="/sub/sms/sendW" title="SMS"><i class="fas fa-envelope"></i>SMS</a></li>
<?php } ?>

<?php if($user['auth_code'] == "006"){ # 실장일 경우 ?>
	<li id="mainMenu_my"><a href="/sub/my/myV" title="기본설정"><i class="fas fa-cog"></i>기본설정</a></li>
	<li id="mainMenu_db"><a href="/sub/db/dbMdL" title="DB관리"><i class="fas fa-id-card"></i>DB관리</a></li>
	<li id="mainMenu_chart"><a href="/sub/chart/dbMyL" title="통계"><i class="fas fa-chart-line"></i>통계</a></li>
	<li id="mainMenu_bbs"><a href="/sub/bbs/bbs?bbs=001&inc=L" title="커뮤니티"><i class="fas fa-headset"></i>커뮤니티</a></li>
	<!-- <li id="mainMenu_schedule" style="float: right;">
		<a href="/sub/schedule/scheduleL" title="일정관리">
			<i class="fas fa-calendar-alt"></i>일정관리<?=$todayScheduleCnt?>
		</a>
	</li>
	<li id="mainMenu_sms" style="float: right;"><a href="/sub/sms/sendW" title="SMS"><i class="fas fa-envelope"></i>SMS</a></li> -->
<?php } ?>

<?php if($user['auth_code'] == "007"){ # 닥터일 경우 ?>
	<li id="mainMenu_my"><a href="/sub/my/myV" title="기본설정"><i class="fas fa-cog"></i>기본설정</a></li>
	<li id="mainMenu_db"><a href="/sub/db/dbDrL" title="DB관리"><i class="fas fa-id-card"></i>DB관리</a></li>
	<li id="mainMenu_chart"><a href="/sub/chart/dbMyL" title="통계"><i class="fas fa-chart-line"></i>통계</a></li>
	<li id="mainMenu_bbs"><a href="/sub/bbs/bbs?bbs=001&inc=L" title="커뮤니티"><i class="fas fa-headset"></i>커뮤니티</a></li>
	<!-- <li id="mainMenu_schedule" style="float: right;">
		<a href="/sub/schedule/scheduleL" title="일정관리">
			<i class="fas fa-calendar-alt"></i>일정관리<?=$todayScheduleCnt?>
		</a>
	</li>
	<li id="mainMenu_sms" style="float: right;"><a href="/sub/sms/sendW" title="SMS"><i class="fas fa-envelope"></i>SMS</a></li> -->
<?php } ?>