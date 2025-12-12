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

	if(!$file['type']){
		echo "return upload";
		return false;
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
	foreach ($sheetData as $row) {
		if($i++ == 1){
			continue; // 헤더 행 건너뛰기
		}
		
		$failed = [];

		// 엑셀 컬럼 읽기
		$pay_date = trim($row['B']);           
		$treat_date = trim($row['C']);         
		$chart_num = trim($row['D']);
		$cs_name = trim($row['E']);           
		$insurance_type = trim($row['F']);     
		$treat_name = trim($row['G']);         
		$dr_name = trim($row['H']);            
		$pay = trim($row['K']);               
		$first_date = trim($row['S']);         
		$visit_path = trim($row['T']);         
		$md_name = trim($row['W']);            
		
		// 실패데이터 미리 저장
		$failed['pay_date'] = $pay_date;
		$failed['treat_date'] = $treat_date;
		$failed['chart_num'] = $chart_num;
		$failed['cs_name'] = $cs_name;
		$failed['insurance_type'] = $insurance_type;
		$failed['treat_name'] = $treat_name;
		$failed['dr_name'] = $dr_name;
		$failed['pay'] = $pay;
		$failed['first_date'] = $first_date;
		$failed['visit_path'] = $visit_path;
		$failed['md_name'] = $md_name;
		
		// 빈 행 체크 (주요 필드 중 하나라도 있으면 처리)
		if(!$chart_num && !$cs_name && !$pay){
			continue;
		}
		
		// 필수값 체크: 차트번호
		if(!$chart_num){
			$failed['reason'] = "차트번호 없음";
			array_push($failedData, $failed);
			$fail++;
			continue;
		}
		
		// 필수값 체크: 이름
		if(!$cs_name){
			$failed['reason'] = "이름 없음";
			array_push($failedData, $failed);
			$fail++;
			continue;
		}
		
		// 금액 숫자 변환 (콤마 제거)
		$pay = preg_replace("/[^0-9]/", "", $pay);
		
		// 의사 idx 찾기
		$dr_idx = 0;
		if($dr_name){
			$value = array(':dr_name' => $dr_name);
			$query = "
				SELECT idx FROM mt_member
				WHERE use_yn = 'Y'
				AND m_name = :dr_name
				AND auth_code = '007'
			";
			$dr_info = view_pdo($query, $value);
			$dr_idx = ($dr_info) ? $dr_info['idx'] : null;
		}
		
		// 실장명 처리 (실장 텍스트 제거)
		$md_name = preg_replace('/\s*실장\s*/', '', $md_name);
		$md_name = trim($md_name);
		
		// 실장 idx 찾기
		$md_idx = 0;
		if($md_name){
			$value = array(':md_name' => $md_name);
			$query = "
				SELECT idx FROM mt_member
				WHERE use_yn = 'Y'
				AND m_name = :md_name
				AND auth_code = '006'
			";
			$md_info = view_pdo($query, $value);
			$md_idx = ($md_info) ? $md_info['idx'] : null;
		}

        if(strpos($treat_name, "임플") !== false){
            $treat_code = 1;
        }else if(strpos($treat_name, "교정") !== false){
            $treat_code = 5;
        }else if(strpos($treat_name, "미용") !== false){
            $treat_code = 3;
        }else if(strpos($treat_name, "보험") !== false){
            $treat_code = 6;
        }else if(strpos($treat_name, "부가") !== false){
            $treat_code = 7;
        }else if(
             strpos($treat_name, "보철") !== false
            || strpos($treat_name, "틀니") !== false
            || strpos($treat_name, "소아") !== false
            || strpos($treat_name, "기타") !== false
            || strpos($treat_name, "진료외") !== false
            || strpos($treat_name, "진단서") !== false
            || strpos($treat_name, "서류") !== false
        ){
            $treat_code = 2;
        }

		// DB 삽입
		$value = array(
			':pay_date' => $pay_date,
			':treat_date' => $treat_date,
			':chart_num' => $chart_num,
			':cs_name' => $cs_name,
			':insurance_type' => $insurance_type,
			':treat_code' => $treat_code,
			':dr_idx' => $dr_idx,
			':pay' => $pay,
			':first_date' => $first_date,
			':visit_path' => $visit_path,
			':md_idx' => $md_idx,
			':reg_idx' => $user['idx'],
			':reg_ip' => $proc_ip
		);
		
		$sql = "
			INSERT INTO mt_db_pay
				(pay_date, treat_date, chart_num, cs_name, insurance_type, treat_code, 
				dr_idx, pay, first_date, visit_path, md_idx, reg_idx, reg_ip, reg_date)
			VALUES
				(:pay_date, :treat_date, :chart_num, :cs_name, :insurance_type, :treat_code, 
				:dr_idx, :pay, :first_date, :visit_path, :md_idx, :reg_idx, :reg_ip, NOW())
		";
		$exec = execute_pdo($sql, $value);
		
		if($exec['data']->rowCount() > 0){
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