<?php 

    include_once "{$_SERVER['DOCUMENT_ROOT']}/css/common.php";

    if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {		
        $protocol = "https";
    } else {
        $protocol = "http";
    }

?>
<link rel="stylesheet" type="text/css" href="https://pro.fontawesome.com/releases/v5.15.4/css/all.css">
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body { display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
        .container { width: 1200px; text-align:center; top:-30px; }
        .container > .content { width:100%; float:left; }
        .container > .content > .icon { width:100px; margin: auto; height:100px; border-radius:50%; border:2px solid #ccc; }
        .container > .content > .icon > i { font-size:40px; color:#ccc; line-height:100px; }
        .container > .content > h1 { font-size:24px; color:#333; margin:40px 0 0 0; }
        .container > .content > p { font-size:18px; color:#AEAEAE; margin:10px 0; line-height:26px; }
    </style>
</head>
<body>

<body>
    <div class="container">
        <div class="content">
            <div class="icon">
                <i class="fas fa-exclamation"></i>
            </div>
        </div>
        <div class="content">
            <h1>요청하신 페이지를 찾을 수 없습니다.</h1>
        </div>
        <div class="content">
            <p>입력한 주소가 잘못되었거나, 사용이 일시 중단되어 요청하신 페이지를 찾을 수 없습니다.<br>서비스 이용에 불편을 드려 죄송합니다.</p>
        </div>
    </div>
</body>
</html>
	