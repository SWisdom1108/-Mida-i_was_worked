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

	$searchQuery = "";
	$searchQuery .= " AND {$label} LIKE '%{$value}%'";

	$value = array(':use_yn' => 'Y');
	$query = "
		SELECT MT.*
			, ( SELECT COUNT(*) FROM mt_member WHERE tm_code = MT.idx ) AS m_cnt
		FROM mt_member_team MT
		WHERE use_yn = :use_yn
		{$searchQuery}
		ORDER BY idx DESC
	";
	$sql = list_pdo($query, $value);
	while($row = $sql -> fetch(PDO::FETCH_ASSOC)){
		$cnt++;
		
		$data = [];
		$data['idx'] = $row['idx'];
		$data['team_name'] = $row['team_name'];
		$data['m_cnt'] = number_format($row['m_cnt'])."명";
		
		array_push($result, $data);
	}

	# 결과보내기
	header("Content-type: application/json");
	echo json_encode($result);

?>