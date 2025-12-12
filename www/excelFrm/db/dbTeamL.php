<?php

	include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/config.php";

	$date = date("YmdHis");
	$excelName = "DB 대량업로드 양식";
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

	# 컬럼 정리
	$lastColum = 3;
	$columnArr = [];
	$value = array(':use_yn'=>'Y');
	$query = "
	SELECT *
	FROM mt_db_cs_info
	WHERE use_yn = :use_yn
	ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);
	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
	
		$lastColum++;
		
		$thisdatas = [];
		$thisdatas['name'] = $row['column_name'];
		$thisdatas['ex'] = $row['column_ex'];
		
		array_push($columnArr, $thisdatas);
	}

	$objPHPExcel->getProperties()->setCreator("DBSOM")
								 ->setLastModifiedBy("DBSOM")
								 ->setTitle($excelName)
								 ->setSubject($excelName)
								 ->setDescription()
								 ->setKeywords($excelName)
								 ->setCategory("License");

	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString(0)."1", "생산업체코드")
		->setCellValue(etExcelColumnString(1)."1", "생산일자")
		->setCellValue(etExcelColumnString(2)."1",$customLabel["cs_name"])
		->setCellValue(etExcelColumnString(3)."1",$customLabel["cs_tel"]);

	foreach($columnArr as $index => $val){
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString(4 + $index)."1", $val['name']);
	}

	$lastColum++;
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString($lastColum)."1", "고객등급코드");

	$lastColum++;
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString($lastColum)."1", "상담구분코드");

	$lastColum++;
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString($lastColum)."1", "상담내용");

	$lastColum++;
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString($lastColum)."1", "담당자(FC)");




	/* 200602 양식 예시 */
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString(0)."2", "PM0001")
		->setCellValue(etExcelColumnString(1)."2", date("Y-m-d 00:00:00"))
		->setCellValue(etExcelColumnString(2)."2", "홍길동")
		->setCellValue(etExcelColumnString(3)."2", "010-1234-5678");

	foreach($columnArr as $index => $val){
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString(4 + $index)."2", $val['ex']);
	}

	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString($lastColum-3)."2", "001");

	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString($lastColum-2)."2", "001");

	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString($lastColum-1)."2", "상담내용");

	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(etExcelColumnString($lastColum)."2", "FC0000");

	$data = array(
		'color' => array('rgb' => "999999")
	);

	cellColor("A2:W2", $data);

	for($i = 0; $i <= $lastColum; $i++){
		# border지정
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->applyFromArray($headBorder);
		$objPHPExcel->getActiveSheet()->getColumnDimension(etExcelColumnString($i))->setWidth(20);
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	}

	# 텍스트 형식으로 변환
	$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString(0).'1:'.etExcelColumnString($lastColum)."2000")->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT);

	$objPHPExcel->getActiveSheet()->setTitle($excelName);
	$objPHPExcel->setActiveSheetIndex(0);

	
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');

	exit;
		
?>