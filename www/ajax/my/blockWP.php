<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$use_yn = 'Y';

	$value = array(':block_ip'=>$data, ':use_yn'=>$use_yn);
	$query = "SELECT * FROM mt_block_ip WHERE block_ip = :block_ip AND use_yn = :use_yn";
	$view = view_pdo($query, $value);
	if($view){
		echo "이미 차단된 IP입니다.";
		return false;
	}

	$value = array(':data'=> $data, ':ipName'=> $ipName, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip);

	$sql = "INSERT INTO mt_block_ip
	 			( block_ip, ip_name, reg_idx, reg_ip, reg_date, use_yn )
	 		VALUES
	 			( :data, :ipName, :proc_id, :proc_ip, now(), 'Y' )";

	$exec = execute_pdo($sql, $value);

    if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>