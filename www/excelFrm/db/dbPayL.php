<?php

	include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/config.php";

	$date = date("YmdHis");
	$excelName = "수납 DB 대량업로드 양식";
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

	// 헤더 설정 (1행)
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue("A1", "순번")
		->setCellValue("B1", "수납일")
		->setCellValue("C1", "진료일")
		->setCellValue("D1", "차트번호")
		->setCellValue("E1", "이름")
		->setCellValue("F1", "보험구분")
		->setCellValue("G1", "수납구분")
		->setCellValue("H1", "의사명")
		->setCellValue("I1", "수납자")
		->setCellValue("J1", "진료금액")
		->setCellValue("K1", "금액")
		->setCellValue("L1", "카드수납")
		->setCellValue("M1", "현금수납")
		->setCellValue("N1", "기타(온라인)")
		->setCellValue("O1", "현영발행액")
		->setCellValue("P1", "카드사/페이")
		->setCellValue("Q1", "공제제외")
		->setCellValue("R1", "메모")
		->setCellValue("S1", "최초내원")
		->setCellValue("T1", "내원경로")
		->setCellValue("U1", "고객성향")
		->setCellValue("V1", "고객구분")
		->setCellValue("W1", "실장명");

	// 예시 데이터 (2행)
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue("A2", "")
		->setCellValue("B2", "2025-01-15")
		->setCellValue("C2", "2025-01-15")
		->setCellValue("D2", "12345")
		->setCellValue("E2", "홍길동")
		->setCellValue("F2", "건강보험")
		->setCellValue("G2", "임플란트")
		->setCellValue("H2", "김철수")
		->setCellValue("I2", "")
		->setCellValue("J2", "")
		->setCellValue("K2", "1000000")
		->setCellValue("L2", "")
		->setCellValue("M2", "")
		->setCellValue("N2", "")
		->setCellValue("O2", "")
		->setCellValue("P2", "")
		->setCellValue("Q2", "")
		->setCellValue("R2", "")
		->setCellValue("S2", "2025-01-01")
		->setCellValue("T2", "인터넷")
		->setCellValue("U2", "")
		->setCellValue("V2", "")
		->setCellValue("W2", "홍길동실장");

	// 예시 행 색상
	$data = array(
		'color' => array('rgb' => "999999")
	);
	cellColor("A2:W2", $data);

	// 스타일 적용
	for($i = 0; $i <= 22; $i++){ // A~W (0~22)
		$col = etExcelColumnString($i);
		
		// 테두리
		$objPHPExcel->getActiveSheet()->getStyle($col.'1')->applyFromArray($headBorder);
		
		// 컬럼 너비
		$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth(15);
		
		// 가운데 정렬
		$objPHPExcel->getActiveSheet()->getStyle($col.'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	}

	// 텍스트 형식으로 변환 (날짜/숫자가 자동 변환 방지)
	$objPHPExcel->getActiveSheet()->getStyle("A1:W2000")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

	$objPHPExcel->getActiveSheet()->setTitle($excelName);
	$objPHPExcel->setActiveSheetIndex(0);

	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');

	exit;
		
?>
