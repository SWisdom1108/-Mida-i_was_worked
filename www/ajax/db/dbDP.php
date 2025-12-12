<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$value = array(':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':idx'=>$idx);
	$query = "
		UPDATE mt_db SET
			  use_yn = 'N'
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx = :idx
	";
	$exec = execute_pdo($query, $value);

	if($exec['data']->rowCount() > 0){

		# 로그등록
		$value = array(':idx'=>$idx);
		$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
		$pmCode = view_pdo($query, $value)['pm_code'];
		if($pmCode){

			$value2 = array(':pmCode'=>$pmCode, ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query2 = "
				INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'pm', :pmCode, 'delete', :idx, :proc_id, :proc_ip )
			";
			execute_pdo($query2, $value2);

			
			# 생산업체 잔여DB
			$nowDate = date("Y-m-d");
			$value = array(':pm_code'=>$pmCode);
			$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pm_code";
			$pmCodeStock = view_pdo($query, $value)['cnt'];
			$value = array(':code_value'=>$pmCode);
			$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
			$pmCodeStockIDX = view_pdo($query, $value)['idx'];
			if(!$pmCodeStockIDX){

				$value3 = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
				$query3 = "
					INSERT INTO mt_db_chart_log
						( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
					VALUES
						( 'pm', :pmCode, 'stock', 0, :proc_id, :proc_ip )
				";
				$exec2 = execute_pdo($query3, $value3);
				$pmCodeStockIDX = $exec2['insertIdx'];
			}

			$value4 = array(':pmCodeStock'=>$pmCodeStock, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':pmCodeStockIDX'=>$pmCodeStockIDX);
			$query4 = "
				UPDATE mt_db_chart_log SET
					  db_cnt = :pmCodeStock
					, edit_idx = :proc_id
					, edit_ip = :proc_ip
					, edit_date = now()
				WHERE idx = :pmCodeStockIDX
			";
			execute_pdo($query4, $value4);

		}
		
			
		# 팀별 분배기록
		$value = array(':idx'=>$idx);
		$query = "SELECT tm_code FROM mt_db WHERE idx = :idx";
		$tmCode = view_pdo($query, $value)['tm_code'];
		$value = array(':idx'=>$idx);
		$query = "SELECT m_idx FROM mt_db WHERE idx = :idx";
		$fcCode = view_pdo($query, $value)['m_idx'];


		$value5 = array(':tmCode'=>$tmCode, ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
		$query5 = "
			INSERT INTO mt_db_chart_log
				( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
			VALUES
				( 'tm', :tmCode, 'delete', :idx, :proc_id, :proc_ip )
		";
		execute_pdo($query5, $value5);


		# 팀원별 분배기록
		$value6 = array(':fcCode'=>$fcCode, ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
		$query6 = "
			INSERT INTO mt_db_chart_log
				( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
			VALUES
				( 'fc', :fcCode, 'delete', :idx, :proc_id, :proc_ip )
		";

		execute_pdo($query6, $value6);
		
		echo "success";
	}  else {
		echo "fail";
	}

?>