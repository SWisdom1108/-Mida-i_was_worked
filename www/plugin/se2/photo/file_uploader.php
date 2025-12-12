<?php
// default redirection
$url = 'callback.html?callback_func='.$_REQUEST["callback_func"];
$bSuccessUpload = is_uploaded_file($_FILES['Filedata']['tmp_name']);

	system("mkdir -p {$_SERVER['DOCUMENT_ROOT']}/upload/se2");
	system("chmod -R 777 {$_SERVER['DOCUMENT_ROOT']}/upload/se2");

// SUCCESSFUL
if(bSuccessUpload) {
	$tmp_name = $_FILES['Filedata']['tmp_name'];
	$name = $_FILES['Filedata']['name'];
	
	$filename_ext = strtolower(array_pop(explode('.',$name)));
	$allow_file = array("jpg", "png", "bmp", "gif");
	
	if(!in_array($filename_ext, $allow_file)) {
		$url .= '&errstr='.$name;
	} else {
		$uploadDir = $_SERVER['DOCUMENT_ROOT']."/upload/se2/";
		if(!is_dir($uploadDir)){
			umask(0);
			mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/se2', 0777);
		}
		
		$name = "se2_".date("YmdHisw").".{$filename_ext}";
		$newPath = $uploadDir.$name;
		
		@move_uploaded_file($tmp_name, $newPath);
		
		$url .= "&bNewLine=true";
		$url .= "&sFileName=".urlencode(urlencode($name));
		$url .= "&sFileURL=/upload/se2/".urlencode(urlencode($name));
	}
}
// FAILED
else {
	$url .= '&errstr=error';
}
	
header('Location: '. $url);
?>