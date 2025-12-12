<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$apiKey = "";

	# API 발급함수
	function setAPIKey($length){
		global $apiKey;
		if(!$length){
			return;
		}

		$char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$char .= '0123456789';

		$result = '';

		for($i = 0; $i < $length; $i++){
			$result .= $char[mt_rand(0, strlen($char))];
		}

		$value = array(':api_key'=>$result);
		$query = "SELECT idx FROM mt_api WHERE api_key = :api_key";
		$sql = view_pdo($query, $value);
		if($sql['idx']){
			setAPIKey($length);
		} else {
			if(!$result){
				setAPIKey($length);
			} else {
				if(strlen($result) != $length){
					setAPIKey($length);
				} else {
					$apiKey = $result;
				}
			}
		}
	}
	setAPIKey(20);

	$sql = "
		INSERT INTO mt_api
			( auth_code, code_idx, api_key, reg_idx, reg_ip )
		VALUES
			( 'pm', '{$code}', '{$apiKey}', '{$proc_id}', '{$proc_ip}' )
	";

	if(excute($sql) > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>