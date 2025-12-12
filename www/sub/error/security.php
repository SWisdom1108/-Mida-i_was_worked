<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            top:-30px;
        }
        .container img {
            width: 150px;
            height: 150px;
        }
        .container h1 {
            font-size: 24px;
            color: #333;
            margin: 20px 0;
        }
        .container div {
            width:500px;
            padding:10px;
            border-bottom:1px solid #ccc;
            border-top:1px solid #ccc;
        }
        .container p {
            font-size: 16px;
            color: #AEAEAE;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<body>
    <div class="container">
		<img src="/images/security.png" alt="보안이미지">
        <h1>페이지에 <span style="color:#0061CB;">접근할 수 없습니다.</span></h1>
		<div>
			<p>인가되지 않은 사용자는 본 페이지에 접근할 수 없습니다.</p>
		</div>
    </div>
</body>
</html>
	