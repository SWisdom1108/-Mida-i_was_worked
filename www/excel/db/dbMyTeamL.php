<?php

	include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/config.php";
	if($user['excel_yn'] !="Y" && $user['auth_code'] > 002){
		die();
	}
	$date = date("YmdHis");
	$excelName = "{$codeInfo['name']} DB분배목록 {$date}";
	$filename = iconv("UTF-8", "EUC-KR", $excelName);

	$defaultBorder = array(
		'style' => PHPExcel_Style_Border::BORDER_THIN,
		'color' => array('rgb'=>'000000')
	);
	$headBorder = array(
		'borders' => array(
			'bottom' => $defaultBorder,
			'left'   => $defaultBorder,
			'top'    => $defaultBorder,
			'right'  => $defaultBorder
		)
	);

	$objPHPExcel->getProperties()->setCreator("DBSOM")
								 ->setLastModifiedBy("DBSOM")
								 ->setTitle($excelName)
								 ->setSubject($excelName)
								 ->setDescription()
								 ->setKeywords($excelName)
								 ->setCategory("License");

	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString(0)."1", "NO")
		->setCellValue(etExcelColumnString(1)."1", "DB고유번호")
		->setCellValue(etExcelColumnString(2)."1", "차트번호")
		->setCellValue(etExcelColumnString(3)."1", "고객등급")
		->setCellValue(etExcelColumnString(4)."1", "{$customLabel["cs_name"]}")
		->setCellValue(etExcelColumnString(5)."1", "{$customLabel["cs_tel"]}");

	$addExcel = ($_COOKIE['excelYnColumn']) ? "AND idx IN ( {$_COOKIE['excelYnColumn']} )" : "";

	# 컬럼 정리
	$lastColum = 5;
	$columnArr = [];
	$value = array(':use_yn'=>'Y');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = :use_yn
		{$addExcel}
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);
	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
	
		$lastColum++;
		
		$thisdatas = [];
		$thisdatas['name'] = $row['column_name'];
		$thisdatas['code'] = $row['column_code'];
		$thisdatas['type'] = $row['column_type'];
		
		array_push($columnArr, $thisdatas);
	}

	foreach($columnArr as $index => $val){
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString(6 + $index)."1", $val['name']);
	}

	$lastColum++;
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString($lastColum)."1", "분배일시");

	$lastColum++;
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString($lastColum)."1", "담당자(FC)");

	$lastColum++;
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString($lastColum)."1", "상담상태");

	for($i = 0; $i <= $lastColum; $i++){
		# border지정
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->applyFromArray($headBorder);
		$objPHPExcel->getActiveSheet()->getColumnDimension(etExcelColumnString($i))->setWidth(20);
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	}

	# 데이터 추출
	$andQuery = ($_COOKIE['listCheckData']) ? " WHERE idx IN ( {$_COOKIE['listCheckData']} )" : $_SESSION["excelAndQuery"];
	$orderQuery = ($_COOKIE['listCheckData']) ? "ORDER BY FIELD(idx, {$_COOKIE['listCheckData']}) ASC" : $_SESSION["excelOrderQuery"];
	$andQuery .= ($_GET['code']) ? " AND m_idx = '{$_GET['code']}'" : "";

	$value = array(''=>'');
	$query = "
			SELECT COUNT(*) as cnt
			FROM mt_db MT
			{$andQuery}
			AND tm_code = {$user['tm_code']}
			{$orderQuery}
	";
	$cnt = view_pdo($query, $value)['cnt'];

	$rows = 2;
	$value = array(''=>'');
	$query = "
		SELECT MT.*
			,  ( SELECT status_name FROM mc_db_cs_status WHERE status_code = MT.cs_status_code ) AS cs_status_name
			, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS m_name
			, ( SELECT grade_name FROM mc_db_grade_info WHERE grade_code = MT.grade_code) AS grade_name
		FROM mt_db MT
		{$andQuery}
		AND tm_code = {$user['tm_code']}
		{$orderQuery}
	";
	$result = list_pdo($query, $value);
	while($row = $result->fetch(PDO::FETCH_ASSOC)){
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString(0).$rows, $cnt--)
			->setCellValue(etExcelColumnString(1).$rows, "D-{$row['idx']}")
			->setCellValue(etExcelColumnString(2).$rows, ($row['chart_num']? $row['chart_num'] : "-"))
			->setCellValue(etExcelColumnString(3).$rows, ($row['grade_code'] == "000")? "없음": $row['grade_name'])
			->setCellValue(etExcelColumnString(4).$rows, $row['cs_name'])
			->setCellValue(etExcelColumnString(5).$rows, $row['cs_tel']);
		
		$thisColumnRow = 5;
		foreach($columnArr as $index => $val){


						
			if($val['type'] == "file"){
				if($row["{$val['code']}"]){
					$value = explode( '@#@#', $row["{$val['code']}"] );
					$cs = $value[1];	
				}else{
					$cs = "-";
				}
			} else{
				$cs = ($row["{$val['code']}"]) ? $row["{$val['code']}"] : "-";
			}
			$thisColumnRow++;
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue(etExcelColumnString(6 + $index).$rows, $cs);
		}
		
		$thisColumnRow++;
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString($thisColumnRow).$rows, date("Y-m-d H:i", strtotime($row['order_by_date'])));
		
		$thisColumnRow++;
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString($thisColumnRow).$rows, ($row['m_name']) ? "{$row['m_name']}(FC{$row['m_idx']})" : "-");
		
		$thisColumnRow++;
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString($thisColumnRow).$rows, ($row['cs_status_name']) ? $row["cs_status_name"] : "-");
		
		$rows++;
	}

	$http_host = $_SERVER['HTTP_HOST'];
	$request_uri = $_SERVER['REQUEST_URI'];
	$url = $http_host.$request_uri;

	$value = array(':m_idx'=>$proc_id, ':m_id'=>$user["m_id"], ':m_name'=> $user["m_name"], ':reg_idx'=>$proc_id, ':reg_ip'=>$proc_ip, ':down_url'=>$url);
	$query = "
		INSERT INTO mt_excel_log
			( m_idx, m_id, m_name, reg_idx, reg_ip, down_url )
		VALUES
			(  :m_idx, :m_id, :m_name, :reg_idx, :reg_ip, :down_url )
	";
	execute_pdo($query, $value);


	if($_COOKIE['excelYnColumn']){
		unset($_COOKIE['excelYnColumn']);
    	setcookie('excelYnColumn', '', time() - 3600, '/');
	}

	# 텍스트 형식으로 변환
	$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString(0).'1:'.etExcelColumnString($lastColum).$rows)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString(0).'1:'.etExcelColumnString($lastColum).$rows)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$objPHPExcel->getActiveSheet()->setTitle($excelName);
	$objPHPExcel->setActiveSheetIndex(0);

	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');

	exit;
		
?>