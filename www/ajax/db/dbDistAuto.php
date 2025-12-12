<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	# DB 가져오기
	$andQuery = ($code) ? " AND pm_code = '{$code}'" : "";

	if ( $_COOKIE['listCheckData'] ){
		$andQuery .= " AND idx IN ( {$_COOKIE['listCheckData']} ) ";
	}

	if($overlap_yn_check == "Y"){
		$andQuery .= " AND overlap_yn = 'N'";
	}

	$data = [];
	$value = array(''=>'');
	$query = "SELECT idx FROM mt_db WHERE use_yn = 'Y' AND (dist_code = '001' OR dist_code = '003') {$andQuery} ORDER BY made_date DESC";
	$list_sql = list_pdo($query, $value);
	while($row = $list_sql->fetch(PDO::FETCH_ASSOC)){
		array_push($data, $row['idx']);
	}
	$dataCnt = 0;

	# 몰아서분배
	if($distMainType == "typeB"){
		
		# 팀별 DB자동분배
		if($type == "distTM"){
			$idx = $_POST['tmCode'];

			foreach($idx as $val){
				$cnt = ${"tmCnt_{$val}"};
				$value = array(':idx'=>$val);
				$query = "SELECT m_idx FROM mt_member_team WHERE idx = :idx";
				$fcCode = view_pdo($query, $value)['m_idx'];
				for($i = 0; $i < $cnt; $i++){
					if(!$data){
						return false;
					}

					$value = array(':val'=>$val, ':fcCode'=>$fcCode, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
					$query = "UPDATE mt_db SET
							  dist_code = '002'
							, dist_date = now()
							, tm_code = :val
							, m_idx = :fcCode
							, edit_idx = :proc_id
							, edit_ip = :proc_ip
							, edit_date = now()
							, order_by_date = now()
						WHERE idx = :idx";

					execute_pdo($query, $value);


					# 분배기록 등록
					$query = "INSERT INTO mt_db_dist_log
							( tm_code, m_idx, db_idx, reg_idx, reg_ip )
						VALUES
							( :val, :fcCode, :idx, :proc_id, :proc_ip )";
					execute_pdo($query, $value);


					# 로그등록
					$value = array(':idx'=>$data[$dataCnt]);
					$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
					$pmCode = view_pdo($query, $value)['pm_code'];
					if($pmCode){

						$value = array(':pmCode'=>$pmCode, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
						$query = "INSERT INTO mt_db_chart_log
								( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
							VALUES
								( 'pm', '{$pmCode}', 'dist', '{$data[$dataCnt]}', '{$proc_id}', '{$proc_ip}' )";
						execute_pdo($query, $value);

						# 생산업체 잔여DB
						$nowDate = date("Y-m-d");
						$value = array(':pm_code'=>$pmCode);
						$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND (dist_code = '001' OR dist_code = '003') AND pm_code = :pm_code";
						$pmCodeStock = view_pdo($query, $value)['cnt'];
						$value = array(':code_value'=>$pmCode);
						$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
						$pmCodeStockIDX = view_pdo($query, $value)['idx'];
						if(!$pmCodeStockIDX){

							$value = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
							$query = "INSERT INTO mt_db_chart_log
									( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
								VALUES
									( 'pm', :pmCode, 'stock', 0, :proc_id, :proc_ip )";
							$exec = execute_pdo($query, $value);

							$pmCodeStockIDX = $exec['insertIdx'];
						}

						$value = array(':pmCodeStock'=>$pmCodeStock, ':pmCodeStockIDX'=>$pmCodeStockIDX, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
						$query = "UPDATE mt_db_chart_log SET
								  db_cnt = :pmCodeStock
								, edit_idx = :proc_id
								, edit_ip = :proc_ip
								, edit_date = now()
							WHERE idx = :pmCodeStockIDX";
						execute_pdo($query, $value);
					}
					
					# 팀별 분배기록
					$value = array(':val'=>$val, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'tm', :val, 'dist', :idx, :proc_id, :proc_ip )";
					execute_pdo($query, $value);
					
					
					# 팀원별 분배기록
					$value = array(':fcCode'=>$fcCode, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'fc', :fcCode, 'dist', :idx, :proc_id, :proc_ip )";
					execute_pdo($query, $value);

					unset($data[$dataCnt]);
					$dataCnt++;
				}
			}
		}

		# 팀원별 DB자동분배
		if($type == "distFC"){
			$idx = $_POST['idx'];

			foreach($idx as $val){
				$cnt = ${"cnt_{$val}"};
				$value = array(':idx'=>$val);
				$query = "SELECT tm_code FROM mt_member WHERE idx = :idx";
				$tmCode = view_pdo($query, $value)['tm_code'];
				for($i = 0; $i < $cnt; $i++){
					if(!$data){
						return false;
					}

					$value = array(':tmCode'=>$tmCode, ':val'=>$val, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
					$query = "UPDATE mt_db SET
							  dist_code = '002'
							, dist_date = now()
							, tm_code = :tmCode
							, m_idx = :val
							, edit_idx = :proc_id
							, edit_ip = :proc_ip
							, edit_date = now()
							, order_by_date = now()
						WHERE idx = :idx";					
					execute_pdo($query, $value);

					# 분배기록 등록
					$query = "INSERT INTO mt_db_dist_log
							( tm_code, m_idx, db_idx, reg_idx, reg_ip )
						VALUES
							( :tmCode, :val, :idx, :proc_id, :proc_ip )";
					execute_pdo($query, $value);


					# 로그등록
					$value = array(':idx'=>$data[$dataCnt]);
					$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
					$pmCode = view_pdo($query, $value)['pm_code'];
					if($pmCode){
						$value = array(':pmCode'=>$pmCode, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
						$query = "INSERT INTO mt_db_chart_log
								( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
							VALUES
								( 'pm', :pmCode, 'dist', :idx, :proc_id, :proc_ip )";

						execute_pdo($query, $value);

						# 생산업체 잔여DB
						$nowDate = date("Y-m-d");
						$value = array(':pm_code'=>$pmCode);
						$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND (dist_code = '001' OR dist_code = '003') AND pm_code = :pm_code";
						$pmCodeStock = view_pdo($query, $value)['cnt'];
						$value = array(':code_value'=>$pmCode);
						$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
						$pmCodeStockIDX = view_pdo($query, $value)['idx'];
						if(!$pmCodeStockIDX){
							$value = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
							$query = "INSERT INTO mt_db_chart_log
									( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
								VALUES
									( 'pm', :pmCode, 'stock', 0, :proc_id, :proc_ip )";
							$exec = execute_pdo($query, $value);

							$pmCodeStockIDX = $exec['insertIdx'];
						}

						$value = array(':pmCodeStock'=>$pmCodeStock, ':pmCodeStockIDX'=>$pmCodeStockIDX, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
						$query = "UPDATE mt_db_chart_log SET
								  db_cnt = :pmCodeStock
								, edit_idx = :proc_id
								, edit_ip = :proc_ip
								, edit_date = now()
							WHERE idx = :pmCodeStockIDX";
						execute_pdo($query, $value);
					}
					
					# 팀별 분배기록
					$value = array(':tmCode'=>$tmCode, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'tm', :tmCode, 'dist', :idx, :proc_id, :proc_ip )";
					execute_pdo($query, $value);
					
					# 팀원별 분배기록
					$value = array(':val'=>$val, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'fc', :val, 'dist', :idx, :proc_id, :proc_ip )";
					execute_pdo($query, $value);

					unset($data[$dataCnt]);
					$dataCnt++;
				}
				
				# 200924 SMS전송
				$value = array(''=>'');
				$query = "SELECT * FROM mt_sms_template WHERE idx = '2'";
				$templateInfo = view_pdo($query, $value);
				if($templateInfo["use_yn"] == "Y" && ${"cnt_{$val}"}){
					$value = array(':idx'=>$val);
					$query = "SELECT * FROM mt_member WHERE idx = :idx";
					$templateUserInfo = view_pdo($query, $value);
					$templateInfo["contents"] = str_replace("#{COMPANY_NAME}", $mainCmpy["company_name"], $templateInfo["contents"]);
					$templateInfo["contents"] = str_replace("#{NAME}", $templateUserInfo["m_name"], $templateInfo["contents"]);
					$templateInfo["contents"] = str_replace("#{DATE}", date("Y-m-d H:i:s"), $templateInfo["contents"]);

					if($templateUserInfo["m_tel"]){
						$smsResult = smsSend($templateUserInfo["m_tel"], $templateInfo["contents"]);
						if($smsResult["msg"] == "success"){
							$value = array(':send_name'=>$smsResult['send_name'], ':send_tel'=>$smsResult['send_tel'], ':contents'=>$templateInfo["contents"], ':receive_name'=>$templateUserInfo["m_name"], ':receive_tel'=>$templateUserInfo["m_tel"], ':result_id'=>$smsResult["Msg_Id"], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );

							$query = "INSERT INTO mt_sms_log 
									( send_name, send_tel, contents, receive_name, receive_tel, result_code, result_msg, result_id, reg_idx, reg_ip )
								VALUES 
									( :send_name, :send_tel, :contents, :receive_name, :receive_tel, '-', '-', :result_id, :proc_id, :proc_ip  )";
							execute_pdo($query, $value);
						}
					}
				}
			}
		}
		
	}

	# 한개씩 사이좋게 분배
	if($distMainType == "typeA"){
		
		# 팀별 DB자동분배
		if($type == "distTM"){
			$idx = $_POST['tmCode'];
			
			$codeResult = [];
			foreach($idx as $val){
				${"tmCnt_{$val}_r"} = 1;
				$codeResult[$val] = "false";
			}

			while(true){
				if(array_count_values($codeResult)["true"] == count($codeResult)){
					break;
				}
				
				foreach($idx as $val){
					if(${"tmCnt_{$val}_r"} > ${"tmCnt_{$val}"}){
						$codeResult[$val] = "true";
						continue;
					}
				
					if(!$data){
						return false;
					}

					$value = array(':idx'=>$val);
					$query = "SELECT m_idx FROM mt_member_team WHERE idx = :idx";
					$fcCode = view_pdo($query, $value)['m_idx'];

					$value = array(':val'=>$val, ':fcCode'=>$fcCode, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
					$query = "UPDATE mt_db SET
							  dist_code = '002'
							, dist_date = now()
							, tm_code = :val
							, m_idx = :fcCode
							, edit_idx = :proc_id
							, edit_ip = :proc_ip
							, edit_date = now()
							, order_by_date = now()
						WHERE idx = :idx";
					execute_pdo($query, $value);

					# 분배기록 등록
					$query = "INSERT INTO mt_db_dist_log
							( tm_code, m_idx, db_idx, reg_idx, reg_ip )
						VALUES
							( :val, :fcCode, :idx, :proc_id, :proc_ip )";
					execute_pdo($query, $value);

					# 로그등록
					$value = array(':idx'=>$data[$dataCnt]);
					$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
					$pmCode = view_pdo($query, $value)['pm_code'];
					if($pmCode){

						$value = array(':pmCode'=>$pmCode, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
						$query = "INSERT INTO mt_db_chart_log
								( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
							VALUES
								( 'pm', :pmCode, 'dist', :idx, :proc_id, :proc_ip )";
						execute_pdo($query, $value);

						# 생산업체 잔여DB
						$nowDate = date("Y-m-d");
						$value = array(':pm_code'=>$pmCode);
						$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND (dist_code = '001' OR dist_code = '003') AND pm_code = :pm_code";
						$pmCodeStock = view_pdo($query, $value)['cnt'];
						$value = array(':code_value'=>$pmCode);
						$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
						$pmCodeStockIDX = view_pdo($query, $value)['idx'];
						if(!$pmCodeStockIDX){

							$value = array(':pmCode'=>$pmCode, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
							$query = "INSERT INTO mt_db_chart_log
									( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
								VALUES
									( 'pm', :pmCode, 'stock', 0, :proc_id, :proc_ip )";
							$exec = execute_pdo($query, $value);

							$pmCodeStockIDX = $exec['insertIdx'];
						}

						$value = array(':pmCodeStock'=>$pmCodeStock, ':pmCodeStockIDX'=>$pmCodeStockIDX, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
						$query = "UPDATE mt_db_chart_log SET
								  db_cnt = :pmCodeStock
								, edit_idx = :proc_id
								, edit_ip = :proc_ip
								, edit_date = now()
							WHERE idx = :pmCodeStockIDX";

						execute_pdo($query, $value);
					}
					
					# 팀별 분배기록
					$value = array(':val'=>$val, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'tm', :val, 'dist', :idx, :proc_id, :proc_ip )";
					execute_pdo($query, $value);
					
					# 팀원별 분배기록
					$value = array(':fcCode'=>$fcCode, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
					$query = "INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'fc', :fcCode, 'dist', :idx, :proc_id, :proc_ip )";

					execute_pdo($query, $value);

					unset($data[$dataCnt]);
					$dataCnt++;
					${"tmCnt_{$val}_r"}++;
				}
			}
		}

		# 팀원별 DB자동분배
		if($type == "distFC"){
			$idx = $_POST['idx'];
			
			$codeResult = [];
			foreach($idx as $val){
				${"cnt_{$val}_r"} = 1;
				$codeResult[$val] = "false";
				
				# 200924 SMS전송
				$value = array(''=>'');
				$query = "SELECT * FROM mt_sms_template WHERE idx = '2'";
				$templateInfo = view_pdo($query, $value);
				if($templateInfo["use_yn"] == "Y" && ${"cnt_{$val}"}){
					$value = array(':idx'=>$val);
					$query = "SELECT * FROM mt_member WHERE idx = :idx";
					$templateUserInfo = view_pdo($query, $value);
					$templateInfo["contents"] = str_replace("#{COMPANY_NAME}", $mainCmpy["company_name"], $templateInfo["contents"]);
					$templateInfo["contents"] = str_replace("#{NAME}", $templateUserInfo["m_name"], $templateInfo["contents"]);
					$templateInfo["contents"] = str_replace("#{DATE}", date("Y-m-d H:i:s"), $templateInfo["contents"]);

					if($templateUserInfo["m_tel"]){
						$smsResult = smsSend($templateUserInfo["m_tel"], $templateInfo["contents"]);
						if($smsResult["msg"] == "success"){
							excute("
								INSERT INTO mt_sms_log 
									( send_name, send_tel, contents, receive_name, receive_tel, result_code, result_msg, result_id, reg_idx, reg_ip )
								VALUES 
									( '{$smsResult['send_name']}', '{$smsResult['send_tel']}', '{$templateInfo["contents"]}', '{$templateUserInfo["m_name"]}', '{$templateUserInfo["m_tel"]}', '-', '-', '{$smsResult["Msg_Id"]}', '{$proc_id}', '{$proc_ip}'  )
							");
						}
					}
				}
			}

			while(true){
				if(array_count_values($codeResult)["true"] == count($codeResult)){
					break;
				}
				
				foreach($idx as $val){
					if(${"cnt_{$val}_r"} > ${"cnt_{$val}"}){
						$codeResult[$val] = "true";
						continue;
					}
				
					if(!$data){
						return false;
					}

					$value = array(':idx'=>$val);
					$query = "SELECT tm_code FROM mt_member WHERE idx = :idx";
					$tmCode = view_pdo($query, $value)['tm_code'];

					$value = array(':val'=>$val, ':tmCode'=>$tmCode, ':idx'=>$data[$dataCnt], ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
					$query = "UPDATE mt_db SET
							  dist_code = '002'
							, dist_date = now()
							, tm_code = :tmCode
							, m_idx = :val
							, edit_idx = :proc_id
							, edit_ip = :proc_ip
							, edit_date = now()
							, order_by_date = now()
						WHERE idx = :idx";
					execute_pdo($query, $value);

					# 분배기록 등록
					excute("
						INSERT INTO mt_db_dist_log
							( tm_code, m_idx, db_idx, reg_idx, reg_ip )
						VALUES
							( '{$tmCode}', '{$val}', '{$data[$dataCnt]}', '{$proc_id}', '{$proc_ip}' )
					");

					# 로그등록
					$value = array(':idx'=>$data[$dataCnt]);
					$query = "SELECT pm_code FROM mt_db WHERE idx = :idx";
					$pmCode = view_pdo($query, $value)['pm_code'];
					if($pmCode){
						excute("
							INSERT INTO mt_db_chart_log
								( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
							VALUES
								( 'pm', '{$pmCode}', 'dist', '{$data[$dataCnt]}', '{$proc_id}', '{$proc_ip}' )
						");

						# 생산업체 잔여DB
						$nowDate = date("Y-m-d");
						$value = array(':pm_code'=>$pmCode);
						$query = "SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND (dist_code = '001' OR dist_code = '003') AND pm_code = :pm_code";
						$pmCodeStock = view_pdo($query, $value)['cnt'];
						$value = array(':code_value'=>$pmCode);
						$query = "SELECT idx FROM mt_db_chart_log WHERE code_type = 'pm' AND code_value = :code_value AND type_name = 'stock' AND reg_date LIKE '{$nowDate}%'";
						$pmCodeStockIDX = view_pdo($query, $value)['idx'];
						if(!$pmCodeStockIDX){
							excute("
								INSERT INTO mt_db_chart_log
									( code_type, code_value, type_name, db_cnt, reg_idx, reg_ip )
								VALUES
									( 'pm', '{$pmCode}', 'stock', 0, '{$proc_id}', '{$proc_ip}' )
							");

							$pmCodeStockIDX = mysqli_insert_id($conn);
						}

						excute("
							UPDATE mt_db_chart_log SET
								  db_cnt = {$pmCodeStock}
								, edit_idx = '{$proc_id}'
								, edit_ip = '{$proc_ip}'
								, edit_date = now()
							WHERE idx = '{$pmCodeStockIDX}'
						");
					}
					
					# 팀별 분배기록
					excute("
						INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'tm', '{$tmCode}', 'dist', '{$data[$dataCnt]}', '{$proc_id}', '{$proc_ip}' )
					");
					
					# 팀원별 분배기록
					excute("
						INSERT INTO mt_db_chart_log
							( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
						VALUES
							( 'fc', '{$val}', 'dist', '{$data[$dataCnt]}', '{$proc_id}', '{$proc_ip}' )
					");

					unset($data[$dataCnt]);
					$dataCnt++;
					${"cnt_{$val}_r"}++;
				}
			}
		}
		
	}

?>