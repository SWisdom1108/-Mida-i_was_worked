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
	$db_idxList = $_POST["idx"];
	$result = "success";

	# 201116 숫자전용
	// $numberStatus = view_sql("SELECT number_yn FROM mc_db_cs_status WHERE status_code = '{$status_code}'")["number_yn"];

	$value = array(':status_code'=>$status_code);
	$query = " SELECT number_yn FROM mc_db_cs_status WHERE status_code = :status_code";

	$view = view_pdo($query, $value);


	if($view['number_yn'] == "Y"){
		$memo = preg_replace("/[^0-9]/s", "", $memo);
		$memo = ($memo) ? $memo : 0;


	}

	foreach($db_idxList as $db_idx){

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

			# 200831 상담상태변경
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
		}  else {
			$result = "fail";
		}
	}

	echo $result;

?>