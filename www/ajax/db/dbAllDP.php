<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$idxs = $_COOKIE['listCheckData'];

	$value = array(':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip);
	$query = "UPDATE mt_db SET
			  use_yn = 'N'
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx IN ($idxs)";

	$exec = execute_pdo($query, $value);

	if($exec['data']->rowCount() > 0){
		
		# 로그등록
		$data = explode(",", $_COOKIE['listCheckData']);
		for($i = 0; $i < count($data); $i++){
			$value = array(':idx'=> $data[$i]);
			$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
			$pmCode = view_pdo($query, $value)['pm_code'];
			if($pmCode){
				$value = array(':idx'=> $data[$i], ':pmCode'=> $pmCode, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip);
				$value2 = array(':pmCode'=>$pmCode);
				$query = "INSERT INTO mt_db_chart_log
						( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
					VALUES
						( 'pm', :pmCode, 'delete', :idx, :proc_id, :proc_ip )";

				execute_pdo($query, $value);
				
				# 생산업체 잔여DB
				$nowDate = date("Y-m-d");
				$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pmCode";
				$pmCodeStock = view_pdo($query, $value2)['cnt'];

				$value2 = array(':pmCode'=>$pmCode, ':nowDate'=>$nowDate);
				$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :pmCode AND type_name = 'stock' AND reg_date LIKE :nowDate";
				$pmCodeStockIDX = view_pdo($query, $value2)['idx'];
				if(!$pmCodeStockIDX){
					$value = array(':pmCode'=>$pmCode, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip);
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
						VALUES
							( 'pm', :pmCode, 'stock', 0, :proc_id', :proc_ip )";
					$exec2 = execute_pdo($query, $value);

					$pmCodeStockIDX = $exec2['insertIdx'];
				}

				$value = array(':pmCodeStock'=>$pmCodeStock, ':pmCodeStockIDX'=>$pmCodeStockIDX, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip);
				$query = "UPDATE mt_db_chart_log SET
						  db_cnt = :pmCodeStock
						, edit_idx = :proc_id
						, edit_ip = :proc_ip
						, edit_date = now()
					WHERE idx = :pmCodeStockIDX";

				execute_pdo($query, $value);
			}
			

			# 팀별 분배기록
			$value = array(':idx'=> $data[$i]);
			$query = "SELECT tm_code FROM mt_db WHERE idx = :idx";
			$tmCode = view_pdo($query, $value)['tm_code'];

			$query = "SELECT m_idx FROM mt_db WHERE idx = :idx";
			$fcCode = view_pdo($query, $value)['m_idx'];

			$value = array(":tmCode"=>$tmCode, ':idx'=>$data[$i], ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip);

			$query = "INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'tm', :tmCode, 'delete', :idx, :proc_id, :proc_ip )";

			execute_pdo($query, $value);

			# 팀원별 분배기록

			$value = array(":fcCode"=>$fcCode, ':idx'=>$data[$i], ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip);
			$query = "INSERT INTO mt_db_chart_log
					( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
				VALUES
					( 'fc', :fcCode, 'delete', :idx, :proc_id, :proc_ip )";

			execute_pdo($query, $value);

			# 분배기록 수정
			excute("
				UPDATE mt_db_dist_log SET
						use_yn = 'N'
					, edit_idx = '{$proc_id}'
					, edit_ip = '{$proc_ip}'
					, edit_date = now()
				WHERE db_idx ='{$data[$i]}'
			");
		}
		
		echo "success";
	}  else {
		echo "fail";
	}

?>