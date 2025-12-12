<?php

	include_once $_SERVER["DOCUMENT_ROOT"]."/plugin/excelDown/PHPExcel.php";
	$objPHPExcel = new PHPExcel();

	function etExcelColumnString($col){
		$char = 'A';
		for(;$col>0;$col--){
			$char++;
		}
		return $char;
	}

	function cellBg($cells="",$color=""){
		global $objPHPExcel;

		$objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'startcolor' => array(
				'rgb' => $color
			)
		));
	}

	function cellColor($cells="",$datas=""){
		global $objPHPExcel;

		$styleArray = array(
			'font'  => $datas
		);      
		$objPHPExcel->getActiveSheet()->getStyle($cells)->applyFromArray($styleArray);
	}

?>