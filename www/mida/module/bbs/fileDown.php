<?php include $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	$idx = $_GET['idx'];
	$value = array(':idx'=>$idx);
	$query = "SELECT * FROM mt_bbs_file WHERE idx = :idx";
	$view = view_pdo($query, $value);
	$filePath = "{$_SERVER['DOCUMENT_ROOT']}upload/bbs/{$view["filename"]}";
	$fileName = $view['filename_r'];

	if(!downloadFile($filePath, $fileName)) {
		echo "<script>alert('다운로드를 실패하였습니다.'); history.back();</script>";
	} 
	
?>