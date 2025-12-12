<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$loginLogo = $_FILES['loginLogo'];
	$loginBg = $_FILES['loginBg'];
	$mainLogo = $_FILES['mainLogo'];
	$favicon = $_FILES['favicon'];

	$value = array(':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':site_name'=> $site_name, ':main_color'=> $main_color, ':idx'=> $site['idx'] );

	$sql = "
		UPDATE mt_site_info SET
			  site_name = :site_name
			, main_color = :main_color
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx = :idx
	";

	$exec = execute_pdo($sql, $value);

    if($exec['data']->rowCount() > 0){
		makeDir("/upload/theme/");
		
		# 로그인로고
		if($loginLogo){
			$directoryName = 'theme';
			$uploadResult = fileUpload($loginLogo, $directoryName);

			if($uploadResult['result']) {
				$query = "UPDATE mt_site_info SET members_logo = :filename, members_logo_ext = :members_logo_ext WHERE idx = :idx";
				$value = array(':filename'=>$uploadResult['fileName'], ':members_logo_ext'=>$uploadResult['fileExt'], ':idx'=>$site['idx']);
				execute_pdo($query, $value);
			}
		}
		
		# 로그인배경
		if($loginBg){
			$directoryName = 'theme';
			$uploadResult = fileUpload($loginBg, $directoryName);

			if($uploadResult['result']) {
				$query = "UPDATE mt_site_info SET members_bg = :filename, members_bg_ext = :members_bg_ext WHERE idx = :idx";
				$value = array(':filename'=>$uploadResult['fileName'], ':members_bg_ext'=>$uploadResult['fileExt'], ':idx'=>$site['idx']);
				execute_pdo($query, $value);
			}
		}
		
		# 메인로고
		if($mainLogo){
			$directoryName = 'theme';
			$uploadResult = fileUpload($mainLogo, $directoryName);

			if($uploadResult['result']) {
				$query = "UPDATE mt_site_info SET top_logo = :filename, top_logo_ext = :top_logo_ext WHERE idx = :idx";
				$value = array(':filename'=>$uploadResult['fileName'], ':top_logo_ext'=>$uploadResult['fileExt'], ':idx'=>$site['idx']);
				execute_pdo($query, $value);
			}
		}
		
		# 파비콘
		if($favicon){
			$directoryName = 'theme';
			$uploadResult = fileUpload($favicon, $directoryName);

			if($uploadResult['result']) {
				$query = "UPDATE mt_site_info SET favicon = :filename, favicon_ext = :favicon_ext WHERE idx = :idx";
				$value = array(':filename'=>$uploadResult['fileName'], ':favicon_ext'=>$uploadResult['fileExt'], ':idx'=>$site['idx']);
				execute_pdo($query, $value);
			}
		}
		
		echo "success";
	}  else {
		echo "fail";
	}

?>