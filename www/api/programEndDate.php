<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php"; ?>
<?php

	# 헤더설정
	header('Content-Type: application/json');

	# 변수검사
	if(!$_POST){
		$result["msg"] = "fail";
		echo json_encode($result);
		return false;
	}

	# 변수설정
	$result = [];
	$endDate = $_POST["endDate"];

	# 저장
	$value = array(':endDate'=>$endDate);
	$sql = "
		UPDATE mt_site_info SET
			e_date = :endDate
	";
	
	$exec = execute_pdo($sql, $value);
	if($exec['data']->rowCount() > 0){
		$result["msg"] = "success";
	} else {
		$result["msg"] = "fail";
	}

	# 결과추출
	echo json_encode($result);

?>