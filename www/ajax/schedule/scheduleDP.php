<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$value = array(':idx'=>$idx);
	$query = "SELECT schedule_type FROM mt_schedule WHERE use_yn = 'Y' AND idx = :idx";
	$type = view_pdo($query, $value)["schedule_type"];
	$value = array(':idx'=>$idx);
	$query = "SELECT msg_id FROM mt_schedule WHERE use_yn = 'Y' AND idx = :idx";
	$msg_id = view_pdo($query, $value)["msg_id"];
	$value = array(':idx'=>$idx);
	$query = "SELECT s_date FROM mt_schedule WHERE use_yn = 'Y' AND idx = :idx";
	$date = view_pdo($query, $value)["s_date"];
	$date = date("Y-m-d", strtotime($date));
	$day = date("d", strtotime($date));

	$query2 = "SELECT log_idx FROM mt_schedule WHERE use_yn = 'Y' AND idx = :idx";
	$log_idx = view_pdo($query2, $value);
	$value2 = array(':log_idx'=>$log_idx['log_idx']);
	$sql2 = "
		DELETE FROM mt_db_cs_log
		WHERE idx = :log_idx
	";
	
	$exec2 = execute_pdo($sql2, $value2);

	$value = array(':idx'=>$idx);
	$sql = "
		DELETE FROM mt_schedule
		WHERE idx = :idx
	";
	$exec = execute_pdo($sql, $value);

	if($exec['data']->rowCount() > 0){
		$result["date"] = $day;
		$value = array(''=>'');
		$query = "SELECT COUNT(*) AS cnt FROM mt_schedule WHERE use_yn = 'Y' AND s_date LIKE '{$date}%'";
		$result["totalCnt"] = view_pdo($query, $value)["cnt"];
		$result["totalCnt"] = number_format($result["totalCnt"]);
		
		if($type == "sms"){
			# 데이터 정리
			$data = [];
			$data['Msg_Id'] = $msg_id; # 고유번호

			# 연결
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_URL, "https://api.mdworks.kr/oneshot/delete");
			$res = curl_exec($ch);
			$res = json_decode($res, true);

			if($res["msg"] == "success"){
				$value = array(':msg_id'=>$msg_id);
				$query = "DELETE FROM mt_sms_log WHERE result_id = :msg_id";
				execute_pdo($query, $value);
			}
			
			$value = array(''=>'');
			$query = "SELECT COUNT(*) AS cnt FROM mt_schedule WHERE use_yn = 'Y' AND s_date LIKE '{$date}%' AND schedule_type = 'sms'";
			$result["smsCnt"] = view_pdo($query, $value)["cnt"];
			$result["smsCnt"] = number_format($result["smsCnt"]);
		}
		$result["msg"] = "success";
	} else {
		$result["msg"] = "fail";
	}

	# 결과추출
	header('Content-Type: application/json');
	echo json_encode($result);

?>