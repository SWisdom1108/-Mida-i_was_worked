<?php

	include_once $_SERVER["DOCUMENT_ROOT"]."/mida/db/config.php";

	$date = date("YmdHis");
	$excelName = "{$codeInfo['name']} DB통합목록 {$date}";
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
		->setCellValue(etExcelColumnString(1)."1", "중복여부")
		->setCellValue(etExcelColumnString(2)."1", "DB고유번호")
		->setCellValue(etExcelColumnString(3)."1", "생산일자")
		->setCellValue(etExcelColumnString(4)."1", "분배여부")
		->setCellValue(etExcelColumnString(5)."1", "상담상태")
		->setCellValue(etExcelColumnString(6)."1", "{$customLabel["cs_name"]}")
		->setCellValue(etExcelColumnString(7)."1", "{$customLabel["cs_tel"]}");

	$addExcel = ($_COOKIE['excelYnColumn']) ? "AND idx IN ( {$_COOKIE['excelYnColumn']} )" : "";

	# 컬럼 정리
	$lastColum = 7;
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
			->setCellValue(etExcelColumnString(8 + $index)."1", $val['name']);
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

	$value = array(':idx'=>$user['pm_code']);
	$query = "SELECT * FROM mt_member_cmpy WHERE idx = :idx";
	$cmpyView = view_pdo($query, $value)['hidden_yn'];

	$value = array(':use_yn'=>'Y',':pm_code' => $user['pm_code']);
	$query = "
		SELECT COUNT(*) as cnt
		FROM mt_db MT
		WHERE use_yn = :use_yn
		AND pm_code = :pm_code
		{$andQuery}
		{$orderQuery}
	";
	$cnt = view_pdo($query, $value)['cnt'];

	$rows = 2;
	$value = array(':use_yn'=>'Y',':pm_code' => $user['pm_code']);
	$query = "
		SELECT MT.*
			, ( SELECT status_name FROM mc_db_cs_status WHERE status_code = MT.cs_status_code ) AS cs_status_name
		FROM mt_db MT
		WHERE use_yn = :use_yn
		AND pm_code = :pm_code
		{$andQuery}
		{$orderQuery}
	";
	$result = list_pdo($query, $value);
	while($row = $result->fetch(PDO::FETCH_ASSOC)){
		$overlapTxt = ($row['overlap_yn'] == "Y") ? "중복" : "미중복";
		$distTxt = ($row['dist_code'] == "002") ? "분배완료" : "미분배";
		if($cmpyView == "Y" && $row['dist_code'] == "002"){
			$cs_name = (mb_strlen($row['cs_name']) > 1) ? mb_substr($row['cs_name'],0,1,'utf-8')."**" : $row['cs_name'];
			$cs_tel = (mb_strlen($row['cs_tel']) > 1) ? mb_substr($row['cs_tel'],0,1,'utf-8')."*******" : $row['cs_tel'];
		}else{
			$cs_name = $row['cs_name'];
			$cs_tel = $row['cs_tel'];
		}
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue(etExcelColumnString(0).$rows, $cnt--)
			->setCellValue(etExcelColumnString(1).$rows, $overlapTxt)
			->setCellValue(etExcelColumnString(2).$rows, "D-{$row['idx']}")
			->setCellValue(etExcelColumnString(3).$rows, date("Y-m-d", strtotime($row['made_date'])))
			->setCellValue(etExcelColumnString(4).$rows, $distTxt)
			->setCellValue(etExcelColumnString(5).$rows, $row['cs_status_name'])
			->setCellValue(etExcelColumnString(6).$rows, $cs_name)
			->setCellValue(etExcelColumnString(7).$rows, $cs_tel);
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
					->setCellValue(etExcelColumnString(8 + $index).$rows, $cs);
			}
		
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