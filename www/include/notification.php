<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

    $value = array(':idx'=>$user['idx']);
	$query = "
		SELECT
            idx
          , s_date
          , cs_name
          , cs_tel
          , memo
          , noti_time
		FROM mt_schedule MT
		WHERE use_yn = 'Y'
		AND noti_send_yn = 'N'
		AND reg_idx = :idx
		ORDER BY idx ASC
	";
	$push = list_pdo($query, $value);

    $day = array("일","월","화","수","목","금","토");
    $target_day = $day[date('w', strtotime(date('Y-m-d')))];

?>

<style>
    html, body { min-width: 100%; background-color:rgba(204, 154, 129, 0); }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<?php

    $num = 0;

    foreach($push as $row) {    
        
        $now_date = date('Y-m-d H:i');
        $noti_time = $row['noti_time'];
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

    $time = date('Y-m-d H:i', strtotime($row['s_date'] . $push_time));

    if(strtotime($now_date) >= strtotime($time)) {
        if($num < 1){
            $last_idx = $row['idx'];
        }
        $num++;

?>
    <div class="noti">
        <div class="top">
            <div class="left">일정</div>
            <div class="right">
                <i class="fas fa-sharp fa-solid fa-x popupCloseBtn" data-idx='<?=$row['idx']?>'></i>
            </div>
        </div>
        <div class="middle">
            <div class="dateTime">
                <i class="fas fa-solid fa-clock"></i>
                <span class="date"><?=date('Y-m-d');?></span>
                <span><?=$target_day?>요일</span>
                <span class="time"><?=(date('H') <= 12) ? '오전' : '오후'?> <?=date('H:i')?></span>
            </div>
            <div class="content">
                <div class="box">
                    <div class="info">
                        <div class="left">
                            <span><?=$row['cs_name'] ? $row['cs_name'] : '-'?></span>
                            <span><?=$row['cs_tel'] ? '('.$row['cs_tel'].')' : ''?></span>
                        </div>
                        <div class="right">
                            <span class="time"><?=date('H:i', strtotime($row['s_date']))?></span>
                            <span class="date"><?=date('Y-m-d', strtotime($row['s_date']))?></span>
                        </div>
                    </div>
                    <div class="memo">
                        <span><?=$row['memo']?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="bottom">
            <button type="button" class="typeBtn btnGray02 popupCloseBtn" data-idx='<?=$row['idx']?>'>확인</button>
        </div>	
    </div>
<?php } } ?>
<input type="hidden" class="last_idx" value="<?=$last_idx?>">

<script type="text/javascript">

    $('.popupCloseBtn').click(function(){

        var idx = $(this).data('idx');
        var last_idx = $(".last_idx").val();

        $(this).closest(".noti").hide();

        $.ajax({ 
            url : "/ajax/schedule/push",
            data : {
                idx : idx
            },
            type : "POST",
            success : function(result){
                console.log("알람 완료");
                if(idx == last_idx){
                    window.parent.location.reload();
                }
            }
        });

    });

</script>
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>