<?php
 	$sFileInfo = '';
	$headers = array();

	system("mkdir -p {$_SERVER['DOCUMENT_ROOT']}/upload/se2");
	system("chmod -R 777 {$_SERVER['DOCUMENT_ROOT']}/upload/se2");
	 
	foreach($_SERVER as $k => $v) {
		if(substr($k, 0, 9) == "HTTP_FILE") {
			$k = substr(strtolower($k), 5);
			$headers[$k] = $v;
		} 
	}

	$filename = rawurldecode($headers['file_name']);
	$filename_ext = strtolower(array_pop(explode('.',$filename)));
	$allow_file = array("jpg", "png", "bmp", "gif"); 

	if(!in_array($filename_ext, $allow_file)) {
		echo "NOTALLOW_".$filename;
	} else {
		$file = new stdClass;
		$file->name = "se2_".date("YmdHis").mt_rand().".".$filename_ext;
		$file->content = file_get_contents("php://input");

		$uploadDir = $_SERVER['DOCUMENT_ROOT']."/upload/se2/";
		if(!is_dir($uploadDir)){
			umask(0);
			mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/se2', 0777);
		}
		
		$newPath = $uploadDir.$file->name;
		
		if(file_put_contents($newPath, $file->content)) {
			$sFileInfo .= "&bNewLine=true";
			$sFileInfo .= "&sFileName=".$filename;
			$url .= "&sFileURL=/upload/se2/".urlencode(urlencode($name));
		}
		
		echo $sFileInfo;
	}
?>