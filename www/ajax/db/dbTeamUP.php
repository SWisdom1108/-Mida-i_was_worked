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
	$chk_site = view_sql("
		SELECT *
		FROM mt_site_info
		WHERE idx = 1
	");

	# DB관리설정 -> 중복검사 설정 체크
	if($chk_site['overlap_yn'] == "Y") {
		$new_tel = preg_replace( "/[^0-9]/", "", $_POST['cs_tel'] );
	
		if($chk_site['overlap_days'] > 0) {
			$chk_list = view_sql("
				SELECT COUNT(*) as cnt
				FROM mt_db
				WHERE idx != {$idx}
				AND made_date >= DATE_SUB(NOW(), INTERVAL {$chk_site['overlap_days']} DAY)
				AND REPLACE(cs_tel, '-', '') = '{$new_tel}'
			");
		} else {
			$chk_list = view_sql("
				SELECT COUNT(*) as cnt
				FROM mt_db
				WHERE idx != {$idx}
				AND REPLACE(cs_tel, '-', '') = '{$new_tel}'
			");
		}
		
		$overlap_yn = ($chk_list['cnt'] > 0) ? 'Y' : 'N';

	} else {
		$overlap_yn = 'N';
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

	$value = array(':cs_name'=>$cs_name, ':cs_tel'=>$cs_tel, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx, ':chart_num'=>$chart_num);
	$query = "
		UPDATE mt_db SET
			  cs_name = :cs_name
			, cs_tel = :cs_tel
			, chart_num = :chart_num
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
			, overlap_yn = '{$overlap_yn}'
			{$andUpdate}
		WHERE idx = :idx
	";
	$exec = execute_pdo($query, $value);

	if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>