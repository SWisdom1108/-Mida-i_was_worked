<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$userMemo = ehtml($userMemo);
	$result = [];

	# 로그인된 계정과 비교
	if($userID == $user['m_id']){
		$result['msg'] = "본인은 추가하실 수 없습니다.";
	} else {
		# 회원이 존재하는지 검사
		$value = array(':m_id'=>$userID);
		$query = "SELECT idx, m_name FROM mt_member WHERE use_yn = 'Y' AND m_id = :m_id";
		$userInfo = view_pdo($query, $value);

		if(!$userInfo){
			$result['msg'] = "존재하지 않는 회원입니다.";
		} else {
			# 등록된 주소록인지 검사
			$value = array(':m_idx'=>$userInfo['idx'], ':reg_idx'=>$user['idx']);
			$query = "SELECT * FROM mt_note_book WHERE use_yn = 'Y' AND m_idx = :m_idx AND reg_idx = :reg_idx";
			$view = view_pdo($query, $value);

			if($view){
				$result['msg'] = "이미 등록된 아이디입니다.";
			} else {
				$userMemo = ($userMemo) ? $userMemo : $userInfo['m_name'];

				$value = array(':m_idx'=>$userInfo['idx'], ':userMemo'=>$userMemo, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
				$query = "
					INSERT INTO mt_note_book
						( m_idx, memo, reg_idx, reg_ip )
					VALUES
						( :m_idx, :userMemo, :proc_id, :proc_ip )
				";

				$exec = execute_pdo($query, $value);

				if($exec['data']->rowCount() > 0){
					$idx = mysqli_insert_id($conn);
					$result['msg'] = "success";
					$result['memo'] = dhtml($userMemo);
					$result['memo2'] = dhtml($userMemo);
					$userInfo['m_name'] = dhtml($userInfo['m_name']);
					$result['user'] = "{$userInfo['m_name']}({$userID})";
					$result['id'] = $userID;
					$result['idx'] = $idx;
				} else {
					$result['msg'] = "알 수 없는 오류입니다.";
				}
			}
		}
	}

	# 결과보내기
	header("Content-type: application/json");
	echo json_encode($result);

?>