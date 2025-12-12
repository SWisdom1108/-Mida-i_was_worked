<?php

	include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/config.php";

	$date = date("YmdHis");
	$excelName = "{$codeInfo['name']} 덴트웹DB관리 {$date}";
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
		->setCellValue(etExcelColumnString(1)."1", "등록일시")
		->setCellValue(etExcelColumnString(2)."1", "생산업체")
		->setCellValue(etExcelColumnString(3)."1", "차트번호")
		->setCellValue(etExcelColumnString(4)."1", "이름")
		->setCellValue(etExcelColumnString(5)."1", "연락처")
		->setCellValue(etExcelColumnString(6)."1", "성별")
        ->setCellValue(etExcelColumnString(7)."1", "최근진료일")
        ->setCellValue(etExcelColumnString(8)."1", "누적금액");

	$addExcel = ($_COOKIE['excelYnColumn']) ? "AND MT.idx IN ( {$_COOKIE['excelYnColumn']} )" : "";


	# 컬럼 정리
	$lastColum = 8;

	for($i = 0; $i <= $lastColum; $i++){
		# border지정
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->applyFromArray($headBorder);
		$objPHPExcel->getActiveSheet()->getColumnDimension(etExcelColumnString($i))->setWidth(20);
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	}

	# 데이터 추출
	$andQuery = ($_SESSION["excelAndQuery"]) ? $_SESSION["excelAndQuery"] : "WHERE MT.use_yn = 'Y'";
	$andQuery .= ($_COOKIE['listCheckData']) ? "AND MT.idx IN ( {$_COOKIE['listCheckData']} )" : "";
	$orderQuery = ($_COOKIE['listCheckData']) ? "ORDER BY FIELD(MT.idx, {$_COOKIE['listCheckData']}) ASC" : "ORDER BY MT.reg_date DESC";


	$value = array();
	$query = "
		SELECT COUNT(*) as cnt
		FROM mt_db_dent AS MT
		{$andQuery}
		{$orderQuery}
	";
	$cnt = view_pdo($query, $value)['cnt'];

//  여기부터 작업 개시
	$value = array(':use_yn'=>'Y');
	$query = "
		SELECT MT.*
			, SUM(mp.pay) AS total_pay
			, cmpy.company_name AS pm_name
		FROM mt_db_dent AS MT
		LEFT JOIN mt_pay_log AS mp
		ON mp.chart_num = MT.chart_num AND mp.use_yn = 'Y'
		{$andQuery}
		GROUP BY MT.idx
		{$orderQuery}
	";
	$result = list_pdo($query, $value);
	$rows = 2;
	while($row = $result->fetch(PDO::FETCH_ASSOC)){
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString(0).$rows, $cnt--)
			->setCellValue(etExcelColumnString(1).$rows, date("Y-m-d", strtotime($row['reg_date'])))
			->setCellValue(etExcelColumnString(2).$rows, ($row['pm_name'])?$row['pm_name']:'-')
			->setCellValue(etExcelColumnString(3).$rows, $row['chart_num'])
			->setCellValue(etExcelColumnString(4).$rows, $row['cs_name'])
			->setCellValue(etExcelColumnString(5).$rows, $row['cs_tel'])
			->setCellValue(etExcelColumnString(6).$rows, ($row['gender']) ? $row['gender'] : '-')
			->setCellValue(etExcelColumnString(7).$rows, date("Y-m-d", strtotime($row['rcpt_date'])))
			->setCellValue(etExcelColumnString(8).$rows, ($row['total_pay'] ? $row['total_pay'] : '0'));
		
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
		
?><!-- 미구현 -->