<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$nowDate = date("Y-m-d");

	$value = array(':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
	$query = "
		UPDATE mt_db SET
			  use_yn = 'Y'
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
		WHERE idx IN ( {$_COOKIE['listCheckData']} )
	";
	$exec = execute_pdo($query, $value);

	if($exec['data']->rowCount() > 0){
		
		# 로그등록
		$data = explode(",", $_COOKIE['listCheckData']);
		for($i = 0; $i < count($data); $i++){
			$value = array(':db_idx'=>$data[$i]);
			$query = "SELECT idx FROM mt_db_chart_log WHERE db_idx = :db_idx AND type_name = 'delete' AND reg_date LIKE '{$nowDate}%'";
			$check = view_pdo($query, $value)['idx'];
			if($check){
				$value1 = array(':checks'=>$check);
				$query1 = "DELETE FROM mt_db_chart_log WHERE idx = :checks";
				execute_pdo($query1, $value1);
			}


			
			$value = array(':idx'=>$data[$i]);
			$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
			$pmCode = view_pdo($query, $value)['pm_code'];
			if($pmCode){
				# 생산업체 잔여DB
				$nowDate = date("Y-m-d");
				$value = array(':pm_code'=>$pmCode);
				$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = :pm_code";
				$pmCodeStock = view_pdo($query, $value)['cnt'];
				$value = array(':code_value'=>$pmCode);
				$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
				$pmCodeStockIDX = view_pdo($query, $value)['idx'];
				if(!$pmCodeStockIDX){
					$value2 = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
					$query2 = "
						INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
						VALUES
							( 'pm', :pmCode, 'stock', 0, :proc_id, :proc_ip )
					";
					$exec2 = execute_pdo($query2, $value2);
					$pmCodeStockIDX = $exec2['insertIdx'];

				}

				$value3 = array(':pmCodeStock'=>$pmCodeStock, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':pmCodeStockIDX'=>$pmCodeStockIDX);
				$query3 = "
					UPDATE mt_db_chart_log SET
						  db_cnt = :pmCodeStock
						, edit_idx = :proc_id
						, edit_ip = :proc_ip
						, edit_date = now()
					WHERE idx = :pmCodeStockIDX
				";
				execute_pdo($query3, $value3);
				
			}
		}
		
		echo "success";
	}  else {
		echo "fail";
	}

?>