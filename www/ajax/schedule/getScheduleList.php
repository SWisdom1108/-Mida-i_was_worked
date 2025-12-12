<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$result = [];
	$userList = explode(",", $_COOKIE["scheduleCheckUserData"]);
	$typeList = explode(",", $_COOKIE["scheduleCheckTypeData"]);

	# 200928 일정카운팅
	$todayCnt = date("Y-m-d");
	$scheduleLogList = [];
	$value = array(':reg_idx'=>$user['idx']);
	$query = "SELECT schedule_idx FROM mt_schedule_log WHERE use_yn = 'Y' AND reg_idx = :reg_idx AND reg_date LIKE '{$todayCnt}%'";
	$sql = list_pdo($query, $value);
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		array_push($scheduleLogList, $row["schedule_idx"]);
	}

	# andQuery
	$andQuery = "";
	
	switch($user["auth_code"]){
		case "004" :
			$andQuery .= " AND ( tm_code = '{$user["tm_code"]}' OR share_all_yn = 'Y' )";
			break;
		case "005" :
			$andQuery .= " AND ( ( reg_idx = '{$user["idx"]}' OR share_all_yn = 'Y' ) OR ( share_tm_yn = 'Y' AND tm_code ='{$user["tm_code"]}') )";
			break;
	}

	$value = array(''=>'');
	$query = "
		SELECT MT.*
			, ( SELECT m_name FROM mt_member WHERE idx = MT.reg_idx ) AS reg_name
			, ( SELECT type_name FROM mc_schedule_type WHERE type_code = MT.type_code ) AS type_name
		FROM mt_schedule MT
		WHERE use_yn = 'Y'
		AND s_date LIKE '{$date}%'
		{$andQuery}
		ORDER BY s_date ASC
	";
	$sql = list_pdo($query, $value);

	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		$display = "block";
		// 251208 차현우 30분 단위 분기

		// $timeClass = date("H", strtotime($row["s_date"]));

		$hour = date("H", strtotime($row["s_date"]));
		$minute = date("i", strtotime($row["s_date"]));
		$minute = floor($minute / 30) * 30;
		$timeClass = sprintf("%02d:%02d", $hour, $minute);

		$sTime = date("H:i", strtotime($row["s_date"]));
		$eTime = date("H:i", strtotime($row["e_date"]));

		$db_idx = $row["db_idx"];
		$value = array(':db_idx' => $db_idx );
		$query2 = "SELECT * FROM mt_db WHERE idx = :db_idx";
		$scheduleInfo = view_pdo($query2, $value);

		$value = array(':idx' => $scheduleInfo['m_idx'] );
		$query2 = "SELECT * FROM mt_member WHERE idx = :idx";
		$memberInfo = view_pdo($query2, $value);
		// 251208 차현우 30분 단위 분기 끝.

		$sTime = date("H:i", strtotime($row["s_date"]));
		$eTime = date("H:i", strtotime($row["e_date"]));
		
		$data = [];

		if($row["call_yn"] == "Y"){
			$data["call"] = true;
		}

		if($row["call_yn"] == "N"){
			$data["call"] = false;
		}
		
		if($row["share_all_yn"] == "Y"){
			$data["notice"] = "공지";
		}
		
		if($row["share_tm_yn"] == "Y"){
			$data["notice"] = "팀내공지";
		}
		
		if(in_array($row["reg_idx"], $userList)){
			$display = "none";	
		}
		
		if(in_array($row["type_code"], $typeList)){
			$display = "none";	
		}
		
		$data["new"] = false;
		$data["btn"] = false;
		$data["idx"] = $row["idx"];
		$data["type"] = $row["schedule_type"];
		$data["typeName"] = dhtml($row["type_name"]);
		$data["regName"] = dhtml($row["reg_name"]);
		$data["time"] = ($row["schedule_type"] == "basic") ? "{$sTime} ~ {$eTime}" : $sTime;
		$data["timeClass"] = $timeClass;
		$data["memo"] = dhtml($row["memo"]);
		$data["noti_yn"] = $row["noti_yn"]; // 알림 작업_2024.10.17 문지호
		$data["display"] = $display;
		$data["class"] = "userItem{$row["reg_idx"]} typeItem{$row["type_code"]}";
		$data["cs_name"] = dhtml($row["cs_name"]);
		$data["cs_tel"] = $row["cs_tel"];

		// 251208 차현우
		$data["treat"] = ($scheduleInfo['cs_etc02']) ? $scheduleInfo['cs_etc02'] : "-";
		$data["member"] = ($memberInfo['m_name']) ? $memberInfo['m_name'] : "-";
		// 251208 차현우 끝.
		
		if(date("Y-m-d", strtotime($row["s_date"])) == date("Y-m-d")){
			if(!in_array($row["idx"], $scheduleLogList)){
				$value1 = array(':schedule_idx'=>$row["idx"], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
				$query1 = "
					INSERT INTO mt_schedule_log
						( schedule_idx, reg_idx, reg_ip )
					VALUES
						( :schedule_idx, :proc_id, :proc_ip )
				";
				execute_pdo($query1, $value1);
				
				$data["new"] = true;
			}
		}
		
		switch($user["auth_code"]){
			case "001" :
			case "002" :
				$data["btn"] = true;
				break;
			case "004" :
			case "005" :
				$data["btn"] = ($row["reg_idx"] == $user["idx"]) ? true : false;
				break;
		}
		
		array_push($result, $data);
	}

	# 결과추출
	header('Content-Type: application/json');
	echo json_encode($result);

?>