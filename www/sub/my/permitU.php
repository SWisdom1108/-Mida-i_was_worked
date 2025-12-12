<?php
    # 공용 헤더 가져오기
    include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";
    
    # 데이터 정보추출
    $value = array(':idx'=>$_GET['idx']);
	$query = "
        SELECT MT.*
        , ( SELECT m_name FROM mt_member WHERE MT.reg_id = idx ) AS m_name
        FROM mt_permit_ip MT
        WHERE idx = :idx
    ";
    $view = view_pdo($query, $value);

    if(!$view){
        include_once "{$_SERVER['DOCUMENT_ROOT']}/sub/error/popup.php";
        return false;
    }
?>

<div class="writeWrap">
    <form enctype="multipart/form-data" id="writeFrm" data-ajax="/ajax/my/permitUP" data-callback="close" data-type="수정">
        <input type="hidden" name="idx" value="<?=$view['idx']?>">
        <div class="tit">IP정보</div>
        <table>
            <colgroup>
                <col width="20%">
                <col width="80%">
            </colgroup>
            <tbody>
                <tr>
                    <th class="label">허용 IP</th>
                    <td class="value">
                        <input class="txtBox" type="text" id="permit_ip" name="permit_ip" value="<?=dhtml($view['permit_ip'])?>" maxlength="45">
                        <ul id="blockTelListWrap"></ul>
                    </td>
                </tr>
                <tr>
                    <th class="label">IP 이름</th>
                    <td class="value">
                        <input class="txtBox" type="text" id="ipName" name="ipName" value="<?=dhtml($view['ip_name'])?>" maxlength="20">
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>

<div id="popupBtnWrap">
    <button type="button" class="typeBtn btnBlack submitBtn" data-target="write">수정</button>
    <button type="button" class="typeBtn btnGray02 popupCloseBtn closeBtn" data-target="mod" data-reload="false">취소</button>
</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>