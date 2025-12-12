<?php

	function saveLog($sql=""){
		global $procIDX, $procIP;
		
		$sql2 = preg_replace('/\r\n|\r|\n/','',$sql);
		$sql = strtoupper(preg_replace('/\r\n|\r|\n/','',$sql));
		
		// INSERT문
		if(strpos($sql, "INSERT INTO")){
			// 변수 설정
			$lastIDX = mysqli_insert_id($conn);
			$action = "등록";
			$name = explode("INSERT INTO", $sql)[1];
			$name = strtolower(trim(explode("(", $name)[0]));
			$name_ko = view_sql("
				SELECT TABLE_COMMENT FROM information_schema.tables 
				WHERE table_name = '{$name}'
			")['TABLE_COMMENT'];
			$AI = view_sql("SHOW INDEX FROM {$name}")['Column_name'];
			$con = "";
			$i = 0;
			
			// 로그 내용 설정
			$view = view_sql("SELECT * FROM {$name} WHERE {$AI} = '{$lastIDX}'");
			foreach($view as $key => $val){
				$i++;
				$key_ko = view_sql("SHOW FULL COLUMNS FROM {$name} WHERE Field = '{$key}'")['Comment'];
				if($i == 1){
					$con .= "{$key}[{$key_ko}] = {$val}";
				} else {
					$con .= "<br>{$key}[{$key_ko}] = {$val}";
				}
			}
		}
		
		// UPDATE문
		if(strpos($sql, "UPDATE ") && strpos($sql, " SET")){
			// 변수 설정
			$lastIDX = mysqli_insert_id($conn);
			$action = "수정";
			$name = explode("UPDATE", $sql)[1];
			$name = explode("SET", $sql)[0];
			$name = strtolower(trim(explode("(", $name)[0]));
			$name_ko = view_sql("
				SELECT TABLE_COMMENT FROM information_schema.tables 
				WHERE table_name = '{$name}'
			")['TABLE_COMMENT'];
			$AI = view_sql("SHOW INDEX FROM {$name}")['Column_name'];
			$con = explode("SET", $sql2)[1];
			$con = ehtml($con);
		}
		
		// DELETE문
		if(strpos($sql, "DELETE FROM")){
			// 변수 설정
			$action = "삭제";
			$name = explode("DELETE FROM", $sql)[1];
			$name = strtolower(trim(explode("WHERE", $name)[0]));
			$name_ko = view_sql("
				SELECT TABLE_COMMENT FROM information_schema.tables 
				WHERE table_name = '{$name}'
			")['TABLE_COMMENT'];
			$con = explode("WHERE ", $sql)[1];
		}
		
		// 로그인
		if($sql == "LOGIN"){
			// 변수 설정
			$action = "로그인";
			$name = "totop";
			$name_ko = "투탑 로그인";
		}
		
		// 로그아웃
		if($sql == "LOGOUT"){
			// 변수 설정
			$action = "로그아웃";
			$name = "totop";
			$name_ko = "투탑 로그아웃";
		}
		
		// 로그 저장
		excute("
			INSERT INTO mt_log
				( table_name, action, contents, reg_idx, reg_ip, reg_date )
			VALUES
				( '{$name}@{$name_ko}', '{$action}', '{$con}', '{$procIDX}', '{$procIP}', now() )
		");
		
	}

?>