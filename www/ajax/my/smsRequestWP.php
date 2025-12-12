<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$file = $_FILES['file'];

	if(!$file['type']){
		echo "통신가입증명원을 첨부해주시길 바랍니다.";
		return false;
	}

	$value = array(':sent_name'=> $sent_name, ':sent_tel'=> $sent_tel, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip );

	$sql = "
		INSERT INTO mt_sms_request
			( sent_name, sent_tel, reg_idx, reg_ip )
		VALUES
			( :sent_name, :sent_tel, :proc_id, :proc_ip )
	";

	$exec = execute_pdo($sql, $value);

    if($exec['data']->rowCount() > 0){
		$idx= $exec['insertIdx'];
		
		// # 파일첨부
		// makeDir("/upload/sms/");
		// $fileExt = explode(".", $file['name']);
		// $fileName = "request_".date("YmdHis").".{$fileExt[count($fileExt)-1]}";
		// $excelFile = $_SERVER['DOCUMENT_ROOT']."/upload/sms/{$fileName}";
		// if(move_uploaded_file($file['tmp_name'], $excelFile)){
		// 	excute("
		// 		UPDATE mt_sms_request SET
		// 			  filename = '{$fileName}'
		// 			, filename_r = '{$file['name']}'
		// 		WHERE idx = '{$idx}'
		// 	");
		// }

		$directoryName = "sms";
		$uploadResult = fileUpload($file, $directoryName);
		if($uploadResult['result']) {
			$query = "UPDATE mt_sms_request SET filename = :filename, filename_r = :filename_r, file_ext = :file_ext WHERE idx = :idx";
			$value = array(':filename'=>$uploadResult['fileName'], ':filename_r'=>$uploadResult['originalFileName'], 'file_ext'=>$uploadResult['fileExt'], ':idx'=>$idx);
			execute_pdo($query, $value);
		}
		
		# 인트라넷으로 전송
		$data = [];
		$data['sent_name'] = $sent_name; # 이름
		$data['sent_tel'] = $sent_tel; # 연락처
		$data['fileurl'] = "//{$_SERVER["HTTP_HOST"]}/upload/sms/{$uploadResult['fileName']}"; # 파일주소
		$data['dbInfo'] = "{$dbHost}@#@#{$dbUser}@#@#{$dbName}@#@#{$dbPasswd}"; # DB정보
		$data['idx'] = $idx; # 고유번호

		# 연결
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_URL, "https://api.mdworks.kr/mida/sms/request");
		$res = curl_exec($ch);
		$resCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// curl_close();
		
		if($resCode == 200){
			echo "success";
		} else {
			echo "fail";
		}
	} else {
		echo "fail";
	}

?>