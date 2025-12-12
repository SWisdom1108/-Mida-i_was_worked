<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$andColumns = "";
	$andValues = "";
	$checkAndQuery = "";
	$cs_name = ehtml($_POST['cs_name']);

	if($no_fc == 'N'){
		echo "fail2";
		return false;
	}

	# 컬럼 정리
	$value = array(''=>'');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = 'Y'
		ORDER BY sort ASC
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
				$directoryName = "db_etc";
				$uploadResult = fileUpload($file, $directoryName);
				if($uploadResult['result']) {
					$data = $uploadResult['fileName']."@#@#".$uploadResult['originalFileName'];
				}
			}else{
				$data = $_POST[$row['column_code']];
			}
		}
		$data = ehtml($data);
		$andColumns .= ", {$row['column_code']}";
		$andValues .= ", '{$data}'";


	}

	$chkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);
	$value = array(''=>'');
	$query = "SELECT * FROM mt_block_tel";
	$blackData = list_pdo($query, $value);
	while($row = $blackData->fetch(PDO::FETCH_ASSOC)){
		if($row['block_tel'] == $chkTel){
			echo "블랙리스트에 추가된 연락처입니다.";
			return false;
		}
	}


	# 중복검사
	$overlap_yn = "N";
	if($site['overlap_yn'] == "Y"){
		if($site['overlap_days'] > 0){
			$checkAndQuery = ($site['overlap_days']) ? " AND made_date > DATE_ADD(date_format('{$made_date}', '%Y-%m-%d'), INTERVAL - {$site['overlap_days']} day)" : "";
		}
		$checkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);

		$v_value = array(':checkTel'=>$checkTel);
		$v_query = "SELECT * FROM mt_db WHERE use_yn = 'Y' AND replace(cs_tel, '-', '') = :checkTel {$checkAndQuery}";
		$overDB = view_pdo($v_query, $v_value);

		if($overDB){
			$overlap_yn = "Y";
		}
		
		// $overDB = view_sql("SELECT * FROM mt_db WHERE use_yn = 'Y' AND cs_tel = '{$cs_tel}' AND tm_code = '{$tmCode}' AND m_idx = '{$fcCode}' {$checkAndQuery}");
		// if($overDB){
		// 	echo "이미 등록된 {$customLabel["cs_tel"]}입니다.";
		// 	return false;
		// }
	}

	



	$value = array(':tm_code'=>$tmCode, ':fcCode'=>$fcCode, ':cs_name'=>$cs_name, ':cs_tel'=>$cs_tel, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':overlap_yn'=>$overlap_yn);
	$query = "
		INSERT INTO mt_db
			( dist_code, dist_date, made_date, tm_code, m_idx, cs_name, cs_tel, reg_idx, reg_ip {$andColumns}, overlap_yn )
		VALUES
			( '002', now(), now(), :tm_code, :fcCode, :cs_name, :cs_tel, :proc_id, :proc_ip {$andValues}, :overlap_yn )
	";
	$exec = execute_pdo($query, $value);


	if($exec['data']->rowCount() > 0){
		$idx = $exec['insertIdx'];

		
		# 분배기록 등록
		if($fcCode > 0){
			$value2 = array(':tm_code'=>$tmCode, ':fcCode'=>$fcCode, ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query2 = "
				INSERT INTO mt_db_dist_log
					( tm_code, m_idx, db_idx, reg_idx, reg_ip )
				VALUES
					( :tm_code, :fcCode, :idx, :proc_id, :proc_ip )
			";
			execute_pdo($query2, $value2);

		}
		
		echo "success";
	}  else {
		echo "fail";
	}

?>