<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$cnt = view_sql("SELECT count(*) as cnt FROM mt_db WHERE m_idx = '{$user['idx']}' AND use_yn = 'Y' AND alarm_yn = 'N'")['cnt'];

	echo $cnt;


?>