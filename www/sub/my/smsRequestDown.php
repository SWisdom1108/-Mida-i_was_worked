<?php include $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php



	$idx = $_GET['idx'];

	if(!$idx){
		echo "통신가입증명원이 존재하지 않습니다.";
		return false;
	}

	$value = array(':idx'=>$idx);
	$query = "SELECT * FROM mt_sms_request WHERE use_yn = 'Y' AND idx = :idx";
	$view = view_pdo($query, $value);
	$filepath = "{$_SERVER['DOCUMENT_ROOT']}/upload/sms/{$view["filename"]}";

	if(file_exists($filepath)){
		$filesize = filesize($filepath);
		header("Pragma: public");
		header("Expires: 0");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".$view["filename_r"]."\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: {$filesize}");

		ob_clean();
		flush();
		readfile($filepath);
	} else {
		www("/");
	}

?>