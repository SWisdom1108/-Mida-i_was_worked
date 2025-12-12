<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php include $_SERVER['DOCUMENT_ROOT']."/plugin/excel/aaaa.php"; ?>
<?php
	set_time_limit(0);	
	ini_set('memory_limit','-1');
	ini_set('max_execution_time',0);
	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	$file = $_FILES['file'];

	$value = array(''=>'');
	$query = "SELECT * FROM mt_site_info WHERE idx = 1";
	$site_info = view_pdo($query, $value);

	if(!$file['type']){
		echo "return upload";
		return false;
	}

	# 컬럼 정리
	$lastCnt = 4;
	$columnArr = [];
	$value = array(''=>'');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = 'Y'
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);

	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$lastCnt++;
		
		$thisdatas = [];
		$thisdatas['code'] = $row['column_code'];
		
		array_push($columnArr, $thisdatas);
	}

	$success = 0;
	$fail = 0;
	$result = [];
	
	$failedData = [];

	makeDir("/excelLog/");
	$fileExt = explode(".", $file['name']);
	$fileName = date("YmdHis")."_{$user['idx']}_{$user['m_name']}.{$fileExt[count($fileExt)-1]}";
	$excelFile = $_SERVER['DOCUMENT_ROOT']."/excelLog/{$fileName}";
	if(!move_uploaded_file($file['tmp_name'], $excelFile)){
		echo "return upload";
		return false;
	}

	$objPHPExcel = PHPExcel_IOFactory::load($excelFile);
	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);


	$i = 1;
	foreach ( $sheetData as $row ){
		if($i++ == 1){
			continue;
		}
		$failed = [];

		$andColumns = "";
		$andValues = "";
		$lastColumns = 4;

		$pmCode = trim($row['A']);
		$made_date = trim($row['B']);
		$made_date = ($made_date) ? $made_date : date("Y-m-d");
		$cs_name = trim($row['C']);
		$cs_tel = trim($row['D']);
		



		$failed['cs_name'] = $cs_name;
		$failed['cs_tel'] = $cs_tel;
		
		foreach($columnArr as $index => $val){
			${$val['code']} = trim($row[etExcelColumnString($lastColumns++)]);
			$failed[$val['code']] = ${$val['code']};
			
			$andColumns .= ", {$val['code']}";
			$andValues .= ", '{${$val['code']}}'";
		}


		$gradeCode = trim($row[etExcelColumnString($lastColumns)]);
		$csStatusCode = trim($row[etExcelColumnString($lastColumns + 1)]);
		$csMemo = ehtml(trim($row[etExcelColumnString($lastColumns + 2)]));
		$tmCode = $_POST['tmCode'];
		$fcCode = trim($row[etExcelColumnString($lastColumns + 3)]);
		$failed['fc_code'] = $fcCode;




		# 한 행 데이터 체크
		$forStatus = true;
		for($ii = 0; $ii < ($lastCnt + 1); $ii++){
			if( trim($row[etExcelColumnString($ii)]) ){
				# 한 행에서 데이터가 1개라도 존재할 경우 for문 진행
				break;
			} else {
				if($ii == $lastCnt){
					# 마지막 행에서도 없으면 다음 for문 진행
					$forStatus = false;
				}
			}
		}



		
		if(!$forStatus){
			continue;
		}
		

		# 담당자 입력 안했을 시
		$value_1 = array(':idx'=>$tmCode);
		$query_1 = "SELECT m_idx FROM mt_member_team WHERE idx = :idx";
		$fcIdx = view_pdo($query_1, $value_1)['m_idx'];
		
		# 영업자
		$fcCode = str_replace("FC", "", $fcCode);
		$value = array(':idx'=>$fcCode,':tm_code'=>$tmCode);
		$query = "SELECT idx FROM mt_member WHERE use_yn = 'Y' AND idx = :idx AND tm_code = :tm_code";
		$fcCode = view_pdo($query, $value)['idx'];
		$fcCode = ($fcCode) ? $fcCode : $fcIdx;
		
		# 이름
		if(!$cs_name){
			$failed['reason'] = "{$customLabel["cs_name"]}없음";
			array_push($failedData, $failed);
			$fail++;
			continue;
		}
		
		# 연락처
		if(!$cs_tel){
			$failed['reason'] = "{$customLabel["cs_tel"]}없음";
			array_push($failedData, $failed);
			$fail++;
			continue;
		}

		# 고객등급
		if($gradeCode) {
			$v_value = array(':grade_code'=>$gradeCode);
			$v_query = "SELECT ex_memo, grade_name FROM mc_db_grade_info WHERE use_yn = 'Y' AND grade_code = :grade_code";
			$grade = view_pdo($v_query, $v_value);

			if(!$grade['grade_name'] || $gradeCode == "000") {
				$gradeCode = "";
				// $failed['reason'] = "없는 고객등급코드";
				// array_push($failedData, $failed);
				// $fail++;
				// continue;
			}
		}
		
		# 중복검사
		$overlap_yn = "N";
		if($site['overlap_yn'] == "Y"){
			if($site['overlap_days'] > 0){
				$checkAndQuery = ($site['overlap_days']) ? " AND made_date > DATE_ADD(date_format('{$made_date}', '%Y-%m-%d'), INTERVAL - {$site['overlap_days']} day)" : "";
			}
			$checkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);
			$checkTel2 = preg_replace('/-(\d{4})-/', '-$1', $cs_tel);
			$checkTel3 = preg_replace('/-/', '', $cs_tel, 1);
			$checkTel4 = $cs_tel;

		    // (A) 휴대폰: 010-1234-1234
		    if (strlen($checkTel) === 11 && substr($checkTel, 0, 3) === '010') {
		        $checkTel4 = substr($checkTel, 0, 3) . '-' . substr($checkTel, 3, 4) . '-' . substr($checkTel, 7, 4);
		    }
		    // (B) 서울 번호 (02)
		    elseif (substr($checkTel, 0, 2) === '02') {
		        if (strlen($checkTel) === 9) { // 02-123-4567
		            $checkTel4 = substr($checkTel, 0, 2) . '-' . substr($checkTel, 2, 3) . '-' . substr($checkTel, 5);
		        } elseif (strlen($checkTel) === 10) { // 02-1234-5678
		            $checkTel4 = substr($checkTel, 0, 2) . '-' . substr($checkTel, 2, 4) . '-' . substr($checkTel, 6);
		        }
		    }
		    // (C) 나머지 지역번호 (3자리: 031, 051, 062 등)
		    elseif (preg_match('/^0\d{2}/', $checkTel)) {
		        $areaCode = substr($checkTel, 0, 3);
		        if (strlen($checkTel) === 10) { // 031-123-4567
		            $checkTel4 = $areaCode . '-' . substr($checkTel, 3, 3) . '-' . substr($checkTel, 6);
		        } elseif (strlen($checkTel) === 11) { // 031-1234-5678
		            $checkTel4 = $areaCode . '-' . substr($checkTel, 3, 4) . '-' . substr($checkTel, 7);
		        }
		    }

			$value = array(':tm_code'=>$tmCode, ':m_idx'=>$fcCode);
			$query = "SELECT * FROM mt_db WHERE use_yn = 'Y' AND cs_tel in ('{$cs_tel}', '{$checkTel}', '{$checkTel2}', '{$checkTel3}', '{$checkTel4}') AND tm_code = :tm_code AND m_idx = :m_idx {$checkAndQuery}";
			$overDB = view_pdo($query, $value);
			if($overDB){
				$overlap_yn = "Y";
			}

			// $overDB = view_sql("SELECT * FROM mt_db WHERE use_yn = 'Y' AND cs_tel = '{$cs_tel}' AND tm_code = '{$tmCode}' AND m_idx = '{$fcCode}' {$checkAndQuery}");
			// if($overDB){
			// 	$failed['reason'] = "중복{$customLabel["cs_tel"]}";
			// 	array_push($failedData, $failed);
			// 	$fail++;
			// 	continue;
			// }
		}

		$chkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);
		$value = array(''=>'');
		$query = "SELECT * FROM mt_block_tel";
		$blackData = list_pdo($query, $value);
		while($row = $blackData->fetch(PDO::FETCH_ASSOC)){
			if($row['block_tel'] == $chkTel){
				$failed['reason'] = "블랙리스트";
				array_push($failedData, $failed);
				$fail++;
				continue;
			}
		}

		if($csStatusCode){
			$andColumns .= ", cs_status_code, cs_status_date";
			$andValues .= ", '{$csStatusCode}', now()";
		}

		if($gradeCode){
			$andColumns .= ", grade_code, grade_date";
			$andValues .= ", '{$gradeCode}', now()";
		}

		# 생산업체
		if($pmCode){
			$pmCode = str_replace("PM", "", $pmCode);
			$pmCode = str_replace("pm", "", $pmCode);
			
			$value = array(':idx'=>$pmCode);
			$query = "SELECT idx FROM mt_member_cmpy WHERE use_yn = 'Y' AND auth_code = '003' AND idx = :idx";
			$pmCode = view_pdo($query, $value)["idx"];
		}
		$pmCode = ($pmCode) ? $pmCode : 0000;

		$value = array(''=>'');
		$sql = "
			INSERT INTO mt_db
				( save_type, pm_code, dist_code, dist_date, made_date, tm_code, m_idx, cs_name, cs_tel, reg_idx, reg_ip {$andColumns}, overlap_yn )
			VALUES
				( 'excel', '{$pmCode}', '002', now(), '{$made_date}', '{$tmCode}', '{$fcCode}', '{$cs_name}', '{$cs_tel}', '{$proc_id}', '{$proc_ip}' {$andValues}, '{$overlap_yn}' )
		";
		$exec = execute_pdo($sql, $value);
		if($exec['data']->rowCount() > 0){
			$idx = $exec['insertIdx'];
			$db_idx = $exec['insertIdx'];

			if( $gradeCode ) {
				excute("
					INSERT INTO mt_db_grade_log
						( db_idx, grade_code, grade_name, ex_memo, reg_idx, reg_ip )
					VALUES
						( '{$db_idx}', '{$gradeCode}', '{$grade['grade_name']}', '{$grade['ex_memo']}', '{$proc_id}', '{$proc_ip}' )
				");
			}

			if ( $csMemo ){
				excute("
					INSERT INTO mt_db_cs_log 
						( db_idx, status_code, memo, reg_idx, reg_ip )
					VALUES 
						( '{$db_idx}', '{$csStatusCode}', '{$csMemo}', '{$proc_id}', '{$proc_ip}'  )
				");
			}

			# 로그등록
			excute("
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'tm', '{$pmCode}', 'upload', '{$idx}', '{$proc_id}', '{$proc_ip}' )
			");
			
			# 생산업체 잔여DB
			$nowDate = date("Y-m-d");
			$value = array(':pm_code'=>$pmCode);
			$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pm_code";
			$pmCodeStock = view_pdo($query, $value)['cnt'];
			$value = array(':code_value'=>$pmCode);
			$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
			$pmCodeStockIDX = view_pdo($query, $value)['idx'];
			if(!$pmCodeStockIDX){
				$value = array(''=>'');
				$sql ="
					INSERT INTO mt_db_pm_log
						( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
					VALUES
						( 'pm', '{$user['pm_code']}', 'stock', 0, '{$proc_id}', '{$proc_ip}' )
				";
				$exec = execute_pdo($sql, $value);
				$pmCodeStockIDX = $exec['insertIdx'];
			}

			excute("
				UPDATE mt_db_chart_log SET
					  db_cnt = {$pmCodeStock}
					, edit_idx = '{$proc_id}'
					, edit_ip = '{$proc_ip}'
					, edit_date = now()
				WHERE idx = '{$pmCodeStockIDX}'
			");


			
			$success++;
		} else {
			$failed['reason'] = "DB오류";
			array_push($failedData, $failed);
			$fail++;
		}
	}

	$result['data'] = $failedData;
	$result['success'] = number_format($success);
	$result['fail'] = number_format($fail);
	$result['total'] = number_format($success + $fail);

	header('Content-Type: application/json');
	echo json_encode($result);

?>