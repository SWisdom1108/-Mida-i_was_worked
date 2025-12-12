<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$andUpdate = "";
	$cs_name = ehtml($_POST['cs_name']);

	# 중복 번호 체크
	$value = array(''=>'');
	$query = "
		SELECT overlap_yn, overlap_days
		FROM mt_site_info
		WHERE idx = 1
	";
	$chk_site = view_pdo($query, $value);

	# DB관리설정 -> 중복검사 설정 체크
	if($chk_site['overlap_yn'] == "Y") {
		$new_tel = preg_replace( "/[^0-9]/", "", $_POST['cs_tel'] );
	
		if($chk_site['overlap_days'] > 0) {
			$value = array(':idx' => $idx, ':overlapDays' => $chk_site['overlap_days'], 'newTel' => $new_tel);
			$query = "
				SELECT COUNT(*) as cnt
				FROM mt_db
				WHERE idx != :idx
				AND made_date >= DATE_SUB(NOW(), INTERVAL :overlapDays DAY)
				AND REPLACE(cs_tel, '-', '') = :newTel
			";
			$chk_list = view_pdo($query, $value);
		} else {
			$value = array(':idx' => $idx, ':newTel' => $new_tel);
			$query = "
				SELECT COUNT(*) as cnt
				FROM mt_db
				WHERE idx != :idx
				AND REPLACE(cs_tel, '-', '') = :newTel
			";
			$chk_list = view_pdo($query, $value);
		}
		
		$overlap_yn = ($chk_list['cnt'] > 0) ? 'Y' : 'N';

	} else {
		$overlap_yn = 'N';
	}

	$value = array(''=>'');
	# 컬럼 정리
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = 'Y'
		ORDER BY idx ASC
	";
	$columnData = list_pdo($query, $value);
	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$data = "";
		if ( gettype($_POST[$row['column_code']]) == "array" ){
			$data = implode(",", $_POST[$row['column_code']]);

			if ( $row['column_code'] == "cs_etc03" ){
				$data = implode("@", $_POST[$row['column_code']]);
			}

		}else{
			if($row['column_type'] == "file"){
				$file = $_FILES[$row['column_code']];
				if($file){
					$directoryName = "db_etc";
					$uploadResult = fileUpload($file, $directoryName);
					if($uploadResult['result']) {
						$data = $uploadResult['fileName']."@#@#".$uploadResult['originalFileName'];
					}
				}else{
					continue;
				}
			}else{
				$data = $_POST[$row['column_code']];
			}
		}

		if($data || $row['column_type'] != "file"){
			$data = ehtml($data);
			$andUpdate .= ", {$row['column_code']} = '{$data}'";	
		}
	}

	$value = array(':cs_name'=>$cs_name, ':cs_tel'=>$cs_tel, ':pm_code'=>$pm_code, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx, ':made_date'=>$made_date);
	$sql = "
		UPDATE mt_db SET
			  cs_name = :cs_name
			, cs_tel = :cs_tel
			, pm_code = :pm_code
			, made_date = :made_date
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
			, overlap_yn = '{$overlap_yn}'
			{$andUpdate}
		WHERE idx =:idx
	";
	$exec = execute_pdo($sql, $value);

	if( $exec['data']->rowCount() > 0 ){
		echo "success";
	}  else {
		echo "fail";
	}

?>