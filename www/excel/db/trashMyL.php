<?php

	include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/config.php";

	$date = date("YmdHis");
	$excelName = "휴지통DB {$date}";
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
		->setCellValue(etExcelColumnString(2)."1", "{$customLabel["cs_name"]}")
		->setCellValue(etExcelColumnString(3)."1", "{$customLabel["cs_tel"]}");

	$addExcel = ($_COOKIE['excelYnColumn']) ? "AND idx IN ( {$_COOKIE['excelYnColumn']} )" : "";

	# 컬럼 정리
	$lastColum = 3;
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
			->setCellValue(etExcelColumnString(4 + $index)."1", $val['name']);
	}

	for($i = 0; $i <= $lastColum; $i++){
		# border지정
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->applyFromArray($headBorder);
		$objPHPExcel->getActiveSheet()->getColumnDimension(etExcelColumnString($i))->setWidth(20);
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	}

	# 데이터 추출
	$andQuery = ($_COOKIE['listCheckData']) ? "AND idx IN ( {$_COOKIE['listCheckData']} )" : "";
	$orderQuery = ($_COOKIE['listCheckData']) ? "ORDER BY FIELD(idx, {$_COOKIE['listCheckData']}) ASC" : "ORDER BY reg_date DESC";
	if($user['auth_code'] == "005"){
		$andQuery .= " AND m_idx = '{$user['idx']}'";
	}

	$value = array(':use_yn'=>'N',':dist_code'=>'002',':tm_code'=>$user['tm_code']);
	$query = "
		SELECT COUNT(*) as cnt
		FROM mt_db MT
		WHERE use_yn = :use_yn
		AND dist_code = :dist_code
		AND tm_code = :tm_code
		{$andQuery}
		{$orderQuery}
	";

	$cnt = view_pdo($query, $value)['cnt'];
	$rows = 2;

	$value = array(':use_yn'=>'N',':dist_code'=>'002',':tm_code'=>$user['tm_code']);
	$query = "
		SELECT MT.*
		FROM mt_db MT
		WHERE use_yn = :use_yn
		AND dist_code = :dist_code
		AND tm_code = :tm_code
		{$andQuery}
		{$orderQuery}
	";
	$result = list_pdo($query, $value);
	while($row = $result->fetch(PDO::FETCH_ASSOC)){
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString(0).$rows, $cnt--)
			->setCellValue(etExcelColumnString(1).$rows, "D-{$row['idx']}")
			->setCellValue(etExcelColumnString(2).$rows, $row['cs_name'])
			->setCellValue(etExcelColumnString(3).$rows, $row['cs_tel']);
		
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
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue(etExcelColumnString(4 + $index).$rows, $cs);
			}
		
		$rows++;
	}

	$http_host = $_SERVER['HTTP_HOST'];
	$request_uri = $_SERVER['REQUEST_URI'];
	$url = $http_host.$request_uri;

	excute("
			INSERT INTO mt_excel_log
				( m_idx, m_id, m_name, reg_idx, reg_ip, down_url )
			VALUES
				( '{$proc_id}', '{$user["m_id"]}', '{$user["m_name"]}', '{$proc_id}', '{$proc_ip}', '{$url}' )
			");

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