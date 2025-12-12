<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$result = [];
	$andQuery = ($user['auth_code'] == "001" || $user['auth_code'] == "002") ? "" : " AND tm_code = '{$user['tm_code']}'";

	$sql = list_sql("
		SELECT MT.*
		FROM mt_member MT
		WHERE use_yn = 'Y'
		AND auth_code IN ( 004, 005 )
		AND idx != '{$user['idx']}'
		{$andQuery}
	");
	foreach ( $sql as $row ){
		# 등록된 주소록인지 검사
		$view = view_sql("SELECT * FROM mt_note_book WHERE use_yn = 'Y' AND m_idx = '{$row['idx']}' AND reg_idx = '{$user['idx']}'");
		if(!$view){
			$userMemo = "{$row['m_name']}";

			$value = array(':m_idx'=>$row['idx'], ':userMemo'=>$userMemo, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );

			$query = "
				INSERT INTO mt_note_book
					( m_idx, memo, reg_idx, reg_ip )
				VALUES
					( :m_idx, :userMemo, :proc_id, :proc_ip )
			";

			execute_pdo($query, $value);

			$userData = [];
			$userData['user'] = "{$row['m_name']}({$row['m_id']})";
			$userData['id'] = $row['m_id'];
			$userData['memo'] = $userMemo;
			$userData['memo2'] = $userMemo;
			$userData['idx'] = mysqli_insert_id($conn);

			array_push($result, $userData);
		}
	}

	# 결과보내기
	header("Content-type: application/json");
	echo json_encode($result);

?>