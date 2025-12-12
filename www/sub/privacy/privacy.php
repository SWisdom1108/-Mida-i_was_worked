<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";?>
<style>
    #popupWrap{float:left; width:100%; height:100%;padding:0;};
</style>
<?php
function get_privacy(){
    $url = "https://intra.midatest.kr/privacy_access/privacy_access.php";
    $system_name = "디비매니저";
    $privacy_type = "privacy_policy";
    $data = [
        'system_name' => $system_name,
        'privacy_type' => $privacy_type
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);
    if (curl_errno($ch)){
        $res = "약관을 불러오는 중 오류가 발생했습니다: ".curl_error($ch);
    }
    return $res;
}
?>
<div style="color:#666666; font-family:Noto Sans CJK KR; line-height:22px; letter-spacing:-0.6px; width:582px;padding-top: 35px; margin: 0 auto; font-size:12px;">
<?=get_privacy();?>
</div>
<div id="popupBtnWrap" style="padding:0; padding-bottom:10px;">
	<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="privacy" data-reload="false">닫기</button>
</div>



