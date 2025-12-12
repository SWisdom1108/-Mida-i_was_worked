<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	# 팀코드설정

	$value = array(':cs_status_code'=>$cs_status_code, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
	$query = "UPDATE mt_db SET
			  cs_status_code = :cs_status_code
			, cs_status_date = now()
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx IN ( ".implode(",", $idx)." )";

	$exec = execute_pdo($query, $value);

	if( $exec['data']->rowCount() > 0 ){		

		foreach($idx as $val){
			$nowYM = date("Y-m"); # 월별일자

			$value = array(':val'=>$val);

			$query = "select * from mt_db where idx = :val";
			$dbInfo = view_pdo($query, $value);
			$tmCode = $dbInfo['tm_code'];
			$fcCode = $dbInfo['m_idx'];

			$query = "DELETE FROM mt_db_dist_log WHERE db_idx = :val AND reg_date LIKE '{$nowYM}%'";
			execute_pdo($query, $value);

			$query = "DELETE FROM mt_db_chart_log WHERE db_idx = :val AND reg_date LIKE '{$nowYM}%'";
			execute_pdo($query, $value);

			$query = "DELETE FROM mt_db_chart_log WHERE db_idx = :val AND reg_date LIKE '{$nowYM}%'";
			execute_pdo($query, $value);
			
			if($fcCode > 0){

				$value = array(':tmCode'=>$tmCode, ':fcCode'=>$fcCode, ':val'=>$val, ':cs_status_code'=>$cs_status_code, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
				$query = "INSERT INTO mt_db_dist_log
						( tm_code, m_idx, db_idx, status_code, reg_idx, reg_ip )
					VALUES
						( :tmCode, :fcCode, :val, :cs_status_code, :proc_id, :proc_ip )";
				execute_pdo($query, $value);

			}
			
			# 팀별 분배기록
			$value = array(':tmCode'=>$tmCode, ':val'=>$val, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
			$query = "INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'tm', :tmCode, 'dist', :val, :proc_id, :proc_ip )";
			execute_pdo($query, $value);

			# 팀원별 분배기록

			$value = array( ':fcCode'=>$fcCode, ':val'=>$val, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
			$query = "INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'fc', :fcCode, 'dist', :val, :proc_id, :proc_ip )";
			execute_pdo($query, $value);
		}

		echo "success";
	}  else {
		echo "fail";
	}

?>