<?php

	include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/config.php";

	$code = $_GET['code'];
	$value = array(':status_code'=>$_GET['code']);
	$query = "SELECT * FROM mc_db_cs_status WHERE use_yn = 'Y' AND status_code = :status_code AND number_yn = 'Y'";
	$codeInfo = view_pdo($query, $value);
	if(!$codeInfo){
		www("/");
		return false;
	}

	$date = date("YmdHis");
	$excelName = "DB{$codeInfo["status_name"]} 정산통계현황 {$date}";
	$filename = iconv("UTF-8", "EUC-KR", $excelName);

	# 생산업체추출
	$pmList = [];
	$value = array(''=>'');
	$query = "SELECT idx, company_name FROM mt_member_cmpy WHERE use_yn = 'Y' AND auth_code = '003' ORDER BY idx DESC";
	$pmSQL = list_pdo($query, $value);
	while($row = $pmSQL->fetch(PDO::FETCH_ASSOC)){
		$pmList[$row["idx"]] = $row["company_name"];
	}

	# 팀추출
	$fcList = [];
	$teamList = [];
	$value = array(''=>'');
	$query = "SELECT idx, team_name FROM mt_member_team WHERE use_yn = 'Y' ORDER BY idx DESC";
	$teamSQL = list_pdo($query, $value);
	while($row = $teamSQL->fetch(PDO::FETCH_ASSOC)){
		$fcDatas = [];
		$value = array(':tm_code'=>$row['idx']);
		$query = "SELECT idx, m_name FROM mt_member WHERE use_yn = 'Y' AND tm_code = :tm_code";
		$fcSQL = list_pdo($query, $value);
		while($subRow = $fcSQL->fetch(PDO::FETCH_ASSOC)){
			$fcDatas[$subRow["idx"]] = $subRow["m_name"];
		}
		
		$teamList[$row["idx"]] = $row["team_name"];
		$fcList[$row["idx"]] = $fcDatas;
	}

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
		->setCellValue(etExcelColumnString(1)."1", "일시")
		->setCellValue(etExcelColumnString(2)."1", "생산업체")
		->setCellValue(etExcelColumnString(3)."1", "이름")
		->setCellValue(etExcelColumnString(4)."1", "연락처")
		->setCellValue(etExcelColumnString(5)."1", "접수일시")
		->setCellValue(etExcelColumnString(6)."1", "팀")
		->setCellValue(etExcelColumnString(7)."1", "담당자")
		->setCellValue(etExcelColumnString(8)."1", "정산내역");

	# 컬럼 정리
	$lastColum = 8;

	for($i = 0; $i <= $lastColum; $i++){
		# border지정
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->applyFromArray($headBorder);
		$objPHPExcel->getActiveSheet()->getColumnDimension(etExcelColumnString($i))->setWidth(20);
		$objPHPExcel->getActiveSheet()->getStyle(etExcelColumnString($i).'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	}

	# 데이터 추출
	$value = array(''=>'');
	$query = "
		SELECT MT.*
			, ( SELECT tm_code FROM mt_member WHERE idx = MT.reg_idx ) AS tm_code
			, ( SELECT m_name FROM mt_member WHERE idx = MT.reg_idx ) AS m_name
			, ( SELECT pm_code FROM mt_db WHERE idx = MT.db_idx ) AS pm_code
			, ( SELECT cs_name FROM mt_db WHERE idx = MT.db_idx ) AS cs_name
			, ( SELECT cs_tel FROM mt_db WHERE idx = MT.db_idx ) AS cs_tel
			, ( SELECT reg_date FROM mt_db WHERE idx = MT.db_idx ) AS cs_date
		FROM mt_db_cs_log MT
		{$_SESSION["chartExcelAndQuery"]}
		{$_SESSION["chartExcelOrderQuery"]}
	";
	$dataSQL = list_pdo($query, $value);
	// $result = list_sql($dataSQL);
	$query = "
		SELECT count(*) as cnt
		FROM mt_db_cs_log MT
		{$_SESSION["chartExcelAndQuery"]}
		{$_SESSION["chartExcelOrderQuery"]}
	";
	$cnt = view_pdo($query, $value)['cnt'];
	// $cnt = cnt_sql($dataSQL);
	$rows = 2;
	while($row = $dataSQL->fetch(PDO::FETCH_ASSOC)){
		$row["memo"] = preg_replace("/[^0-9]/s", "", $row["memo"]);
		
		if($row["pm_code"] < 10){
			$row["pm_code"] = "000{$row["pm_code"]}";
		} else if($row["pm_code"] < 100){
			$row["pm_code"] = "00{$row["pm_code"]}";
		} else if($row["pm_code"] < 1000){
			$row["pm_code"] = "0{$row["pm_code"]}";
		}
		
		if($row["tm_code"] < 10){
			$row["tm_code"] = "000{$row["tm_code"]}";
		} else if($row["tm_code"] < 100){
			$row["tm_code"] = "00{$row["tm_code"]}";
		} else if($row["tm_code"] < 1000){
			$row["tm_code"] = "0{$row["tm_code"]}";
		}
		
		if($row["reg_idx"] < 10){
			$row["reg_idx"] = "000{$row["reg_idx"]}";
		} else if($row["reg_idx"] < 100){
			$row["reg_idx"] = "00{$row["reg_idx"]}";
		} else if($row["reg_idx"] < 1000){
			$row["reg_idx"] = "0{$row["reg_idx"]}";
		}
		
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString(0).$rows, $cnt--)
			->setCellValue(etExcelColumnString(1).$rows, ($row["reg_date"]) ? $row["reg_date"] : "-")
			->setCellValue(etExcelColumnString(2).$rows, ($pmList[$row["pm_code"]]) ? $pmList[$row["pm_code"]] : "-")
			->setCellValue(etExcelColumnString(3).$rows, ($row["cs_name"]) ? $row["cs_name"] : "-")
			->setCellValue(etExcelColumnString(4).$rows, ($row["cs_tel"]) ? $row["cs_tel"] : "-")
			->setCellValue(etExcelColumnString(5).$rows, ($row["cs_date"]) ? $row["cs_date"] : "-")
			->setCellValue(etExcelColumnString(6).$rows, ($teamList[$data["tm_code"]]) ? $teamList[$data["tm_code"]] : "-")
			->setCellValue(etExcelColumnString(7).$rows, ($row["m_name"]) ? $row["m_name"] : "-")
			->setCellValue(etExcelColumnString(8).$rows, number_format($row["memo"]).$codeInfo["number_label"]);
		
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