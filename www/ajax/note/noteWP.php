<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$content = ehtml($content);

	# 받는 사람 정리
	$receive_id = explode(",", $receive_id);

	# 결과 카운팅
	$successed = 0;
	$failed = 0;
	$totalCnt = count($receive_id);

	# 쪽지보내기
	for($i = 0; $i < count($receive_id); $i++){
		
		# 공백제거
		$mID = trim($receive_id[$i]);
		
		# 자신에게 보내면 리턴
		if($mID == $user['m_id']){
			$failed++;
			continue;
		}
		
		# 회원이 존재하는지 검사
		$value = array(':mID'=>$mID);
		$query = "SELECT idx FROM mt_member WHERE use_yn = 'Y' AND m_id = :mID";
		$view = view_pdo($query, $value);
		
		if(!$view['idx']){
			$failed++;
			continue;
		}
		
		# 쪽지보내기
		$value1 = array(':receive_idx'=>$view['idx'], ':content'=>$content, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
		$query1 = "
			INSERT INTO mt_note
				( receive_idx, contents, reg_idx, reg_ip )
			VALUES
				( :receive_idx, :content, :proc_id, :proc_ip )
		";
		execute_pdo($query1, $value1);

		$successed++;
		
	}

	# 결과
	echo "받는 사람 총 {$totalCnt}명 중 {$successed}명에게 전송이 완료되었습니다.";

?>