<?php

	include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/config.php";

	$date = date("YmdHis");
	$excelName = "PM{$_GET["code"]} DB통합통계 {$date}";
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
		->setCellValue(etExcelColumnString(1)."1", "생산업체")
		->setCellValue(etExcelColumnString(2)."1", "등록일시")
		->setCellValue(etExcelColumnString(3)."1", "이름")
		->setCellValue(etExcelColumnString(4)."1", "연락처")
		->setCellValue(etExcelColumnString(5)."1", "분배일시")
		->setCellValue(etExcelColumnString(6)."1", "담당팀")
		->setCellValue(etExcelColumnString(7)."1", "담당자")
		->setCellValue(etExcelColumnString(8)."1", "삭제여부");

	# 컬럼 정리
	$lastColum = 8;

	for($i = 0; $i <= $lastColum; $i++){
		# border지정
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->applyFromArray($headBorder);
		$objPHPExcel->getActiveSheet()->getColumnDimension(etExcelColumnString($i))->setWidth(20);
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	}

	# 데이터 추출
	$startDate = ($_GET['s_date']) ? $_GET['s_date'] : date("Y-m-d", strtotime("- 7 days"));
	$endDate = ($_GET['e_date']) ? $_GET['e_date'] : date("Y-m-d");
	$dbList = [];
	
	$andQuery = " AND code_type = 'pm' AND code_value = '{$_GET["code"]}' AND date_format(reg_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d') AND date_format(reg_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";
	$value = array(''=>'');
	$query = "SELECT db_idx FROM mt_db_chart_log WHERE 1=1 {$andQuery}";
	$sql = list_pdo($query, $value);
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		if($row["db_idx"]){
			array_push($dbList, $row["db_idx"]);
		}
	}

	$value = array(':pm_code'=>$_GET['code']);
	$query = "SELECT idx FROM mt_db WHERE pm_code = :pm_code AND date_format(reg_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d') AND date_format(reg_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";
	$sql = list_pdo($query, $value);
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		array_push($dbList, $row["idx"]);
	}

	$dbList = implode(",", $dbList);

	$value = array(''=>'');
	$query = "
		SELECT MT.*
			, ( SELECT status_name FROM mc_db_cs_status WHERE status_code = MT.cs_status_code ) AS cs_status_name
			, ( SELECT company_name FROM mt_member_cmpy WHERE idx = MT.pm_code ) AS pm_name
			, ( SELECT team_name FROM mt_member_team WHERE idx = MT.tm_code ) AS tm_name
			, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS m_name
		FROM mt_db MT
		WHERE idx IN ( {$dbList} )
		ORDER BY idx DESC
	";
	$sql = list_pdo($query, $value);
	// $result = list_sql($sql);
	// $cnt = cnt_sql($sql);
	$query = "
		SELECT count(*) as cnt
		FROM mt_db MT
		WHERE idx IN ( {$dbList} )
		ORDER BY idx DESC
	";
	$cnt = view_pdo($query, $value)['cnt'];
	$rows = 2;
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString(0).$rows, $cnt--)
			->setCellValue(etExcelColumnString(1).$rows, ($row["pm_name"]) ? $row["pm_name"] : "-")
			->setCellValue(etExcelColumnString(2).$rows, ($row["reg_date"]) ? $row["reg_date"] : "-")
			->setCellValue(etExcelColumnString(3).$rows, ($row["cs_name"]) ? $row["cs_name"] : "-")
			->setCellValue(etExcelColumnString(4).$rows, ($row["cs_tel"]) ? $row["cs_tel"] : "-")
			->setCellValue(etExcelColumnString(5).$rows, ($row["dist_code"] == "002") ? $row["order_by_date"] : "-")
			->setCellValue(etExcelColumnString(6).$rows, ($row["tm_name"]) ? $row["tm_name"] : "-")
			->setCellValue(etExcelColumnString(7).$rows, ($row["m_name"]) ? $row["m_name"] : "-")
			->setCellValue(etExcelColumnString(8).$rows, ($row["use_yn"] == "Y") ? "미삭제" : "삭제");
		
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