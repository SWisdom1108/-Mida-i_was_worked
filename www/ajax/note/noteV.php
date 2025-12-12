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

	# 값 불러오기
	$value1 = array(':idx'=>$idx);

	$query1 = "
		SELECT MT.*
			, ( SELECT m_name FROM mt_member WHERE idx = MT.receive_idx ) AS receiveName
			, ( SELECT m_id FROM mt_member WHERE idx = MT.receive_idx ) AS receiveID
			, ( SELECT m_name FROM mt_member WHERE idx = MT.reg_idx ) AS regName
			, ( SELECT m_id FROM mt_member WHERE idx = MT.reg_idx ) AS regID
		FROM mt_note MT
		WHERE use_yn = 'Y' 
		AND idx = :idx
	";
	$view = view_pdo($query1, $value1);
	
	# 없는 쪽지결과
	if(!$view){
		$result['msg'] = "fail";
	} else {
		if($view['reg_idx'] != $user['idx'] && $view['receive_idx'] != $user['idx']){
			# 받는 사람도 보낸 사람도 아니면 실패
			$result['msg'] = "fail";
		} else {
			if($view['receive_idx'] == $user['idx'] && !$view['view_date']){
				# 받는 사람이 본인인데 안읽은 상태이면 변경
				$value = array(':idx'=>$idx);
				$query = "UPDATE mt_note SET view_date = now() WHERE idx = :idx";
				execute_pdo($query, $value);

				$result['date'] = date("Y-m-d H:i:s");
			}
			
			if($view['receive_idx'] == $user['idx']){
				# 본인이 받은 쪽지일 경우
				$result['user'] = "FROM. {$view['regName']}({$view['regID']})";
			} else {
				# 본인이 보낸 쪽지일 경우
				$result['user'] = "TO. {$view['receiveName']}({$view['receiveID']})";
			}
			
			$result['memo'] = nl2br($view['contents']);
		}
	}

	# 결과보내기
	header("Content-type: application/json");
	echo json_encode($result);

?>