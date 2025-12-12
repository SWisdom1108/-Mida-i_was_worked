<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$value = array(':data'=> $data);
	$query = "SELECT * FROM mt_block_tel WHERE block_tel = :data";
	$view = view_pdo($query, $value);

	if($view){
		echo "이미 차단된 번호입니다.";
		return false;
	}

	$value = array(':data'=> $data, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip );

	$sql = "INSERT INTO mt_block_tel
	 			( block_tel, reg_id, reg_ip, reg_date, use_yn )
	 		VALUES
	 			( :data, :proc_id, :proc_ip, now(), 'Y' )";

	$exec = execute_pdo($sql, $value);
	if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}


	# 기존소스
	// print_r($_POST);
	// return false;
	// $i = 0;
	// foreach($blockTel as $index => $val){

	// 	$overTel = view_sql("SELECT * FROM mt_block_tel WHERE block_tel = '{$blockTel}'");
	// 	if($overTel){
	// 		$i++;
	// 		continue;
	// 	}

	// 	excute("
	// 		INSERT INTO mt_block_tel
	// 			( block_tel, reg_id, reg_ip, reg_date, use_yn )
	// 		VALUES
	// 			( '{$val}', '{$proc_id}', '{$proc_ip}', now(), 'Y' )
	// 	");


	// }
?>