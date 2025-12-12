<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	# 변수설정
	$result = [];
	$idxs = implode(",", $_POST["idx"]);
	$Msg_Id = "";

	# Msg_Id 추출
	$sql = list_sql("SELECT result_id FROM mt_sms_log WHERE idx IN ( {$idxs} )");
	foreach ( $sql as $row ){
		$Msg_Id .= ($Msg_Id) ? "," : "";
		$Msg_Id .= $row["result_id"];
	}

	# 연결
	$data = [];
	$data["Msg_Id"] = $Msg_Id;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_URL, "https://api.mdworks.kr/oneshot/result");
	$res = curl_exec($ch);
	$res = json_decode($res, true);
	$resCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close();

	# 데이터 수정
	$datas = $res["data"];
	foreach($datas as $Msg_Id => $row){
		if($row["msg"] == "success"){
			$result[$Msg_Id]["code"] = $row["data"]["Result"];
			$result[$Msg_Id]["msg"] = $row["data"]["Result_Msg"];
			$result[$Msg_Id]["date"] = $row["data"]["Send_Time"];
			
			$value = array(':result_code'=>$row["data"]["Result"],':result_msg'=>$row["data"]["Result_Msg"],':send_date'=>$row["data"]["Send_Time"],':result_id'=>$Msg_Id);
			$query = "
				UPDATE mt_sms_log SET
					  result_code = :result_code
					, result_msg = :result_msg
					, send_date = :send_date
				WHERE result_id = :result_id";
			execute_pdo($query, $value);
		}
	}

	# 결과추출
	header('Content-Type: application/json');
	echo json_encode($result);

?>