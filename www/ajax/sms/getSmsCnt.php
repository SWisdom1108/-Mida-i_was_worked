<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	# 데이터 정리
	$data = [];
	$data['tel'] = view_sql("SELECT sent_tel FROM mt_sms_tel")["sent_tel"];

	# 연결
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_URL, "https://api.mdworks.kr/mida/sms/cnt");
	$res = curl_exec($ch);
	$smsCntInfo = json_decode($res, true);

	# 결과 가공
	$smsCntInfo["finishCnt"] = number_format($smsCntInfo["totalCnt"] - $smsCntInfo["useCnt"]);
	$smsCntInfo["totalCnt"] = number_format($smsCntInfo["totalCnt"]);
	$smsCntInfo["useCnt"] = number_format($smsCntInfo["useCnt"]);

	# 결과추출
	header('Content-Type: application/json');
	echo json_encode($smsCntInfo);

?>