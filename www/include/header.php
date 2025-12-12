<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php"; ?>
<?php

	if(!$programDateInfo->invert){
		# 모바일체크 // 2025-05-01부터 모바일 제공 x로 인해 주석
		// if(preg_match($mCheck, $_SERVER['HTTP_USER_AGENT'])) {
		// 	if ($_SESSION['idx'] || $_SERVER["PHP_SELF"] == "/account/login.php") {
		// 		www("/m/index");
		// 	} else {
		// 		www("/m/home");
		// 	}
		// 	exit;
		// }

		# 회원정보 존재여부에 따른 리턴 이벤트
		$accountPath = explode("/", $_SERVER['REQUEST_URI'])[1];
		$accountPath = explode("/", $accountPath)[0];
		if($accountPath == "account"){
			if($_SESSION['idx']){
				www("/index.php");
			}
		} else {
			if (!$_SESSION['idx'] && $_SERVER["PHP_SELF"] == "/m/account/login.php") {
				www("/account/login.php");
				die("로그인이 필요합니다.");
			} else {
				if (!$_SESSION['idx']) {
					www("/home");
				}			
			}
		}
	}

	# 콘텐츠 경로
	$contentsRoot = array();
	for($i = 0; $i < count($contentsRoots); $i++){
		array_push($contentsRoot, $contentsRoots[$i]);
	}

	# 설정파일 불러오기
	include_once "_setting.php";

	# 메뉴 접근 권한설정
	if(count($menuAuth)){
		# 메뉴 접근 권한설정값이 존재할 경우 체크
		if(!in_array($user['auth_code'], $menuAuth)){
			www("/sub/error/");
		}
	}

	$faviconpath = $_SERVER['DOCUMENT_ROOT'].'upload/theme/'.$site['favicon'];
	$favicon = getEncodedImage($faviconpath);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<meta name="robots" content="noindex">
	<title><?=$site['site_name']?></title>
	
	<!-- plugin -->
      
    <!-- jquery -->
    <script type="text/javascript" src="/plugin/jquery/jquery.min.js"></script>
    
    <!-- jquery ui -->
    <link rel="stylesheet" type="text/css" href="/plugin/jquery-ui/jquery-ui.css">
    <script type="text/javascript" src="/plugin/jquery-ui/jquery-ui.js"></script>
      
	<!-- jquery minicolors -->
	<script src="/plugin/jquery-minicolors/jquery.minicolors.min.js"></script>
	<link rel="stylesheet" href="/plugin/jquery-minicolors/jquery.minicolors.css">
	<!-- jquery Billboard -->
	<link rel="stylesheet" href="/plugin/billboard/billboard.css">
	<script type="text/javascript" src="/plugin/billboard/d3.js"></script>
	<script type="text/javascript" src="/plugin/billboard/billboard.js"></script>
      
	<!-- fontawesome -->
	<link rel="stylesheet" type="text/css" href="/plugin/fontawesome/all.min.css">
	<link rel="stylesheet" type="text/css" href="https://pro.fontawesome.com/releases/v5.15.4/css/all.css">
	
	<!-- se2 -->
	<script type="text/javascript" src="/plugin/se2/js/HuskyEZCreator.js"></script>

	<!-- spectrum color picker -->
	<link rel="stylesheet" type="text/css" href="/plugin/spectrum/spectrum.css">
	<script type="text/javascript" src="/plugin/spectrum/spectrum.js"></script>
	
	<!-- alert js -->
	<!-- <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> -->

	<!-- alert js -->
	<script src="/plugin/swiper/sweetalert.min.js"></script>
		
	<!-- script -->
	<script type="text/javascript" src="/js/common.js?v=250225"></script>
	
	<!-- stylesheet -->
	<link type="text/css" rel="stylesheet" href="/css/common.css?v=200928">
	<link type="text/css" rel="stylesheet" href="/css/style.css?v=200928">
	
	<!-- icon -->
	<link rel="icon" href="<?=($favicon) ? "data:image/jpg;base64,".$favicon : "/images/favicon.png"?>">
</head>

<style>

	#loadingWrap > #loading { border-top-color: <?=$site['main_color']?>; }
	
<?php if($accountPath != "account"){ ?>
	html, body { background-color: <?=$site['main_color']?>; }
	
	/* 버튼 */
	.btnMain { background-color: <?=$site['main_color']?>; color: #FFF; border: 1px solid rgba(0, 0, 0, 0.15); }
	
	/* 공용 상단 로고 영역 */
	#headerWrap > .logoWrap { background-color: <?=$site['main_color']?>; }
	
	/* 공용 상단 탑메뉴 영역 */
	#headerWrap > .conWrap > .topWrap > .right > li { background-color: <?=$site['main_color']?>; }
	.leftSideSizeControlBtn { background-color: <?=$site['main_color']?>; }
	
	/* 공용 상단 메인메뉴 영역 */
	#headerWrap > .conWrap > .mainMenuWrap > ul > li:hover { border-bottom: 3px solid <?=$site['main_color']?>; }
	#headerWrap > .conWrap > .mainMenuWrap > ul > li.active { border-bottom: 3px solid <?=$site['main_color']?>; }
	
	/* 공용 좌측 서브메뉴 영역 */
	#leftSideWrap { background-color: <?=$site['main_color']?>; }
	#leftSideWrap > .mainMenuNameWrap { background-color: <?=$site['main_color']?>; }
	#leftSideWrap > .subMenuListWrap { background-color: <?=$site['main_color']?>; }
	#leftSideWrap > .subMenuListWrap .trdMenu { background-color: <?=$site['main_color']?>; }
	
	/* 콘텐츠 타이틀 영역 */
	#mainContentsWrap > .contentsTitleWrap > .conWrap > .left > .sec { color: <?=$site['main_color']?>; }
	
	/* 200417 대시보드 */
	.dashboardWrap .dataListTable > .iconWrap > span { color: <?=$site['main_color']?>; }
	
	/* 페이징 */
	.pagingWrap > ul > li > span { color: <?=$site['main_color']?>; border: 1px solid <?=$site['main_color']?>; }
	
	/* 팝업 영역 */
	.popupWrap > .popupBox > .titWrap > .left { color: <?=$site['main_color']?>; }
	
	/* 데이터 간단정리표 */
	.dataInfoSimpleWrap .conWrap > ul > li > .value { color: <?=$site['main_color']?>; }
	
	#mainContentsWrap > .contentsTitleWrap > .exWrap { background-color: <?=$site['main_color']?>; }
	
	/* 데이터 목록영역 */
	.listWrap > table td i.dbCsBtnN { color: <?=$site['main_color']?>; }
	
	/* 나의 쪽지함 */
	#myNoteWrap > .listWrap > .tabWrap > ul > li.active { color: <?=$site['main_color']?>; }
	#myNoteWrap > .listWrap > .viewWrap > ul > li.active { border: 1px solid <?=$site['main_color']?> !important; }
	#myNoteWrap > .listWrap .sendListWrap > li:hover { border: 1px solid <?=$site['main_color']?>; }

	/* ip차단 */
	.ipblock > ul  li.active {min-width:180px; float:left; text-align: center; padding: 10px 0px; border-top: 1px solid <?=$site['main_color']?>; border-left: 1px solid <?=$site['main_color']?>; border-right: 1px solid <?=$site['main_color']?>; border-bottom:1px solid #FFF; font-size:14px; z-index:1;} 
	.ipblock > ul  li.active {color: <?=$site['main_color']?>; font-weight:500;}
<?php } else { ?>
	#membersWrap { background-color: <?=$site['main_color']?>; }
	#membersBox > .formWrap > .titWrap > span.point { color: <?=$site['main_color']?>; }
	#membersBox > .formWrap > .inputWrap > input:focus { border: 1px solid <?=$site['main_color']?>; }
	#membersBox > .formWrap > .saveWrap > label > i.on { color: <?=$site['main_color']?>; }
	#membersBox > .formWrap > .btnWrap > button { background-color: <?=$site['main_color']?>; }
<?php } ?>
	
</style>

<?php

	$pushvalue = array(':idx'=>$user['idx']);
	$query = "
		SELECT *
		  , COUNT(CASE WHEN noti_yn = 'Y' THEN 1 END) AS push_cnt
		FROM mt_schedule MT
		WHERE use_yn = 'Y'
		AND noti_yn = 'Y'
		AND noti_send_yn = 'N'
		AND reg_idx = :idx
		ORDER BY idx ASC
	";
	$push_data = [];
	$push = list_pdo($query, $pushvalue);
	$push_cnt = view_pdo($query, $pushvalue)['push_cnt'];

	while($push_row = $push->fetch(PDO::FETCH_ASSOC)) {

		$date = date('Y.m.d', strtotime($push_row['s_date']));
		$time = date('H:i:s', strtotime($push_row['s_date']));

		// noti_time 값에 따른 알림 시간 조정
		$noti_time = $push_row['noti_time'];
		$push_time = '';

		switch ($noti_time) {
			case '1min':
				$push_time = '-1 minute';
				break;
			case '5min':
				$push_time = '-5 minutes';
				break;
			case '10min':
				$push_time = '-10 minutes';
				break;
			case '30min':
				$push_time = '-30 minutes';
				break;
			case '1hrs':
				$push_time = '-1 hour';
				break;
			case '1day':
				$push_time = '-1 day';
				break;
		};

		$time = date('Y-m-d H:i', strtotime($push_row['s_date'] . $push_time));

		// 처리한 데이터를 push_data 배열에 추가
		$push_row['date'] = $date;
		$push_row['time'] = $time;
		array_push($push_data,$push_row);
	}
?>
<script>
$(function() {
    function getNotificationPermission() {
        // 브라우저 지원 여부 체크
        if (!("Notification" in window)) {
           alert("데스크톱 알림을 지원하지 않는 브라우저입니다.");
        }
        // 데스크탑 알림 권한 요청
        Notification.requestPermission(function (result) {
			// 권한 거절
			if(result == 'denied') {
				alert('알림권한을 허용해주세요.\n브라우저의 사이트 설정에서 변경하실 수 있습니다.');
				return false;
        	}
        });
    }

	// 알림여부 여부 체크
	var push_cnt = <?= $push_cnt ?>;

	if (push_cnt > 0) {
		getNotificationPermission();
	}

    var push_data = <?=json_encode($push_data)?>;

    // 알림을 한 번만 보내기 위해 추가한 플래그 설정
    push_data.forEach(function(values) {
        values.sent = false; // 알림이 아직 보내지 않았다는 플래그 설정
    });

    playAlert = setInterval(function() {
        var today = new Date();
        var year = today.getFullYear();
        var month = ('0' + (today.getMonth() + 1)).slice(-2);
        var day = ('0' + today.getDate()).slice(-2);
        var hours = ('0' + today.getHours()).slice(-2);
        var minutes = ('0' + today.getMinutes()).slice(-2);
        
        var current_time = `${year}-${month}-${day} ${hours}:${minutes}`; // 현재 시간 (YYYY-MM-DD HH:MM 형식)

		pushpopup = false;
        push_data.forEach(function(values) {
            var time = values['time']; // 미리 계산된 push_time과 비교
    
            // 현재 시간이 push_time보다 크거나 같고, noti_send_yn이 'N'이며, 아직 알림을 보내지 않은 경우
            if(values['noti_send_yn'] == 'N' && current_time >= time && !values.sent) {

				// 문자열을 잘라주는 함수
				function truncate(str, maxLength) {
					if (str.length > maxLength) {
						return str.substring(0, maxLength) + '...';
					} else {
						return str;
					}
				}
				
				pushpopup = true;
                // 푸시 알림 생성
                if(values['cs_name'] && values['cs_tel']) {
					var push = new Notification("알림", {
						body: `등록된 일정이 있습니다.\n\nDB정보 : ${truncate(values['cs_name'], 10)} / ${truncate(values['cs_tel'], 13)}\n내용 : ${truncate(values['memo'], 24)}`,
						requireInteraction: true,
                    });
                } else {
                    var push = new Notification("알림", {
                        body: `등록된 일정이 있습니다.\n\n내용 : ${values['memo']}`,
                        requireInteraction: true,
                    });
                }
    
                push.onclick = function(event) {
                    event.preventDefault();
                    push.close(); // 알림을 닫음
                };

                // 알림을 보낸 후 sent 플래그를 true로 설정
                values.sent = true;
            }
        });
		// 일정 팝업
		if (pushpopup) {
			popupControls2('open', 'noti', `/include/notification`, '일정');
		}
    }, 10000);
});

</script>

<body>
	<div id="loadingWrap">
		<div id="loading"></div>
	</div>
	
	<div id="wrap">
	<?php # 현재 경로가 멤버스 경로가 아닐경우 서브파일 불러오기 ?>
	<?php ($accountPath != "account") ? include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerSub.php" : ""; ?>