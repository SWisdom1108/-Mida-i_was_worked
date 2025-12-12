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

	# 슬롯 검사
	if($use_yn == "Y"){
		if($site["slot"] < 9999){
			if(($site["slot"] - $site["slot_r"]) <= 0){
				echo "사용가능한 사용자수량이 존재하지 않습니다.";
				return false;
			}
		}
	}

	$value = array(':use_yn' => $use_yn, ':idx' => $idx);
	$query = "
		UPDATE mt_member_cmpy SET
			use_yn = :use_yn
		WHERE idx = :idx
	";
	$exec = execute_pdo($query, $value);
	if ( $exec['data']->rowCount() > 0 ){

		$value = array(':use_yn' => $use_yn, ':idx' => $idx);
		$query = "
		UPDATE mt_member SET
			use_yn = '{$use_yn}'
		WHERE idx = '{$m_idx}'
		";
		$exec = execute_pdo($query, $value);

		$result['status_code'] = "success";
		$result['msg'] = "상태 변경에 성공하였습니다.";
	}else{
		$result['status_code'] = "fail";
		$result['msg'] = "상태 변경에 실패하였습니다.";
	}

	# 결과보내기
	header("Content-type: application/json");
	echo json_encode($result, true);

?>