<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$memo = ehtml($memo);
	$file = $_FILES['file'];

	# 201116 숫자전용
	// $numberStatus = view_sql("SELECT number_yn FROM mc_db_cs_status WHERE status_code = '{$status_code}'")["number_yn"];
	// if($numberStatus == "Y"){
	// 	$memo = preg_replace("/[^0-9]/s", "", $memo);
	// 	$memo = ($memo) ? $memo : 0;
	// }

	$value = array(':status_code'=>$status_code);
	$query = " SELECT number_yn FROM mc_db_cs_status WHERE status_code = :status_code";

	$view = view_pdo($query, $value);


	if($view['number_yn'] == "Y"){
		$memo = preg_replace("/[^0-9]/s", "", $memo);
		$memo = ($memo) ? $memo : 0;
	}

	$value = array(':db_idx'=> $db_idx, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip,  ':memo'=> $memo, ':status_code'=> $status_code);

	$query ="
		INSERT INTO mt_db_cs_log
			( db_idx, status_code, memo, reg_idx, reg_ip )
		VALUES
			( :db_idx, :status_code, :memo, :proc_id, :proc_ip )

	";

	$exec = execute_pdo($query, $value);

	if( $exec['data']->rowCount() > 0 ){
		$idx = $exec['insertIdx'];

		$v_value = array(':grade_code'=>$grade_code);
		$v_query = "SELECT ex_memo, grade_name FROM mc_db_grade_info WHERE use_yn = 'Y' AND grade_code = :grade_code";
		$grade = view_pdo($v_query, $v_value);
		
		# 200831 상담상태변경
		if($grade_code){
			$value1 = array(':status_code'=> $status_code, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':db_idx'=> $db_idx);
			$query1 = "
				UPDATE mt_db SET
					  cs_status_code = :status_code
					, cs_status_date = now()
					, grade_code = {$grade_code}
					, edit_idx = :proc_id
					, edit_ip = :proc_ip
					, edit_date = now()
				WHERE idx = :db_idx
			";

			execute_pdo($query1, $value1);
		}else{
			$value1 = array(':status_code'=> $status_code, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':db_idx'=> $db_idx);
			$query1 = "
				UPDATE mt_db SET
					  cs_status_code = :status_code
					, cs_status_date = now()
					, edit_idx = :proc_id
					, edit_ip = :proc_ip
					, edit_date = now()
				WHERE idx = :db_idx
			";

			execute_pdo($query1, $value1);
		}


		$value2 = array(':db_idx'=>$db_idx, ':grade_code'=>$grade_code, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
		$query2 = "
			INSERT INTO mt_db_grade_log
				( db_idx, grade_code, grade_name, ex_memo, reg_idx, reg_ip, reg_date )
			VALUES
				( :db_idx, :grade_code, '{$grade['grade_name']}', '{$memo}', :proc_id, :proc_ip, now() )	
		";
		execute_pdo($query2, $value2);

		
		# 첨부파일
		if($file){
			$date = date("Y/m/d");

			$directoryName = "db/cs/{$date}";
			$uploadResult = fileUpload($file, $directoryName);
			if($uploadResult['result']) {
				$fileQuery = "
					UPDATE mt_db_cs_log SET
						filename = :fileName
						, filename_r = :originalFileName
						, file_ext = :fileExt
					WHERE idx = :idx
				";
				$fileValue = array(':fileName'=>$directoryName.'/'.$uploadResult['fileName'], ':originalFileName'=>$uploadResult['originalFileName'], ':fileExt'=>$uploadResult['fileExt'], ':idx'=>$idx);
				execute_pdo($fileQuery, $fileValue);
			}
		}
		
		# 분배기록 수정
		$nowYM = date("Y-m"); # 월별일자
		$value2 = array(':status_code'=> $status_code, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':db_idx'=> $db_idx);

		$query2 = "
			UPDATE mt_db_dist_log SET
				  status_code = :status_code					
				, edit_idx = :proc_id
				, edit_ip = :proc_ip
				, edit_date = now()
			WHERE db_idx = :db_idx 
			AND reg_date LIKE '{$nowYM}%' 
			AND m_idx = '{$user['idx']}'
		";
		execute_pdo($query2, $value2);

		# 일정등록
		if($scheduleChk == 'on') {
			$idx = $exec['insertIdx'];
			$date = $_POST['date'];

			// 20251208 차현우 일정등록 종료시간

			// $s_date = "{$date} {$s_time}:00";
			// $e_time = date("H:i", strtotime("{$s_time} + 1 hour"));
			// $e_date = "{$date} {$e_time}:00";

			$s_date = "{$date} {$s_time}";
			$e_date = "{$date} {$e_time}";
			
			// 20251208 차현우 일정등록 종료시간 끝.
			$memo = ehtml($memo);
			$andColumn = "";
			$andValue = "";


			$tm_code = ($user["tm_code"]) ? $user["tm_code"] : 0;

			switch($user["auth_code"]){
				case "001" :
				case "002" :
					$share_all_yn = ($share_all_yn) ? "Y" : "N";
					
					$andColumn .= ", share_all_yn";
					$andValue .= ", '{$share_all_yn}'";
					break;
				case "004" :
					$share_tm_yn = ($share_tm_yn) ? "Y" : "N";
					
					$andColumn .= ", share_tm_yn";
					$andValue .= ", '{$share_tm_yn}'";
					break;
			}

			if(isset($cs_name)) {
				$andColumn .= ", cs_name";
				$andValue .= ", '{$cs_name}'";
			}

			if(isset($cs_tel)) {
				$andColumn .= ", cs_tel";
				$andValue .= ", '{$cs_tel}'";
			}

			if(!empty($db_idx)) {
				$andColumn .= ", db_idx";
				$andValue .= ", '{$db_idx}'";
			}

			if($noti_yn == 'on') {
				$noti_yn = 'Y';
				$noti_send_yn = 'N';
				$call_yn = 'N';
			} else {
				$noti_yn = 'N';
				$call_yn = 'Y';
			}

			$value = array(':type_code'=>$type_code, ':tm_code'=>$tm_code, ':s_date'=>$s_date, ':e_date'=>$e_date, ':memo'=>$memo,':noti_yn'=>$noti_yn,':noti_time'=>$noti_time,':noti_send_yn'=>$noti_send_yn, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':call_yn' => $call_yn, ':idx'=>$idx);
			$sql = "
				INSERT INTO mt_schedule
					( schedule_type, type_code, tm_code, s_date, e_date, memo, noti_yn, noti_time, noti_send_yn, reg_idx, reg_ip, call_yn, log_idx {$andColumn})
				VALUES
					( 'basic', :type_code, :tm_code, :s_date, :e_date, :memo, :noti_yn, :noti_time, :noti_send_yn, :proc_id, :proc_ip, :call_yn, :idx {$andValue})
			";
			execute_pdo($sql, $value);
		}

		// $alarm_sql = "
		// 	SELECT * FROM mt_schedule_alarm
		// 	WHERE db_idx = '{$db_idx}'
		// ";
		// $value = array(':db_idx'=>$db_idx);
		// $alarm_view = view_pdo($alarm_sql, $value);
		// if($alarm_view['idx']){
		// 	$alarm_sql ="
		// 		DELETE FROM mt_schedule_alarm
		// 		WHERE db_idx = :db_idx
		// 	";
		// 	execute_pdo($alarm_sql, $value);

		// 	$value_schedule = array(':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':schedule_idx'=>$alarm_view['schedule_idx']);
		// 	$schedule_sql ="
		// 		UPDATE mt_schedule SET
		// 			  noti_send_yn = 'Y'
		// 			, edit_idx = :proc_id
		// 			, edit_ip = :proc_ip
		// 			, edit_date = now()
		// 		WHERE idx = :schedule_idx
		// 		";
		// 	execute_pdo($schedule_sql, $value_schedule);
		// }
		
		echo "success";
	}  else {
		echo "fail";
	}

?>