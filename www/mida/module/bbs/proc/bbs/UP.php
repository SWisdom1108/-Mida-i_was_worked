<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$title = ehtml($title);
	$contents = ehtml($contents);
	$notice = ($notice) ? "Y" : "N";
	$file = $_FILES['files'];

	if(!$title){
		echo "제목을 입력해주시길 바랍니다.";
		return false;
	}

	$value = array( ':title'=>$title, ':content'=>$contents, ':notice'=>$notice, ':etc1'=>$etc1, ':etc2'=>$etc2, ':etc3'=>$etc3, ':etc4'=>$etc4, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx);
	$query = "
		UPDATE mt_bbs SET
			  title = :title
			, content = :content
			, noti_yn = :notice
			, etc1 = :etc1
			, etc2 = :etc2
			, etc3 = :etc3
			, etc4 = :etc4
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx = :idx
	";

	$exec = execute_pdo($query, $value);
	if($exec['data']->rowCount() > 0){
		
		# 파일삭제
		$value = array(':idx'=>$idx);
		$query = "
			SELECT *
			FROM mt_bbs_file
			WHERE bbs_idx = :idx
			ORDER BY filename_r ASC
		";
		$result = list_pdo($query, $value);
		while($row = $result->fetch(PDO::FETCH_ASSOC)){
			if(!${"fileItem_{$row['idx']}"}){
				unlink("{$_SERVER['DOCUMENT_ROOT']}/upload/bbs/{$row['filename']}");
				excute("DELETE FROM mt_bbs_file WHERE idx = '{$row['idx']}'");
			}
		}

		foreach($_FILES as $data) {
			$directoryName = 'bbs';
			$uploadResult = fileUpload($data, $directoryName);

			if($uploadResult['result']) {
				$query = "
					INSERT INTO mt_bbs_file 
						( bbs_idx, filename, filename_r, file_ext, file_size, reg_idx, reg_ip )
					VALUES
						( :idx, :filename, :filename_r, :fileExt, :fileSize, :proc_id, :proc_ip )";
				$value = array(':idx'=>$idx, ':filename'=>$uploadResult['fileName'], ':filename_r'=>$uploadResult['originalFileName'], ':fileExt'=>$uploadResult['fileExt'], ':fileSize'=>$uploadResult['fileSize'], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
				execute_pdo($query, $value);
			}
		}
		
		echo "success";
	}  else {
		echo "fail";
	}

?>