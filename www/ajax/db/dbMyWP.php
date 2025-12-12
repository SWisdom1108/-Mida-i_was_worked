<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$pm_code = ($pm_code) ? $pm_code : 0;

	$value = array(''=>'');
	$query = "SELECT * FROM mt_site_info WHERE idx = 1";
	$site_info = view_pdo($query, $value);

	$andColumns = "";
	$andValues = "";

	# 컬럼 정리

	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = 'Y'
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);

	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		if ( gettype($_POST[$row['column_code']]) == "array" ){
			$data = implode(",", $_POST[$row['column_code']]);

			if ( $row['column_code'] == "cs_etc03" ){
				$data = implode("@", $_POST[$row['column_code']]);
			}

		}else{
			$data = $_POST[$row['column_code']];
		}
		$data = ehtml($data);
		$andColumns .= ", {$row['column_code']}";
		$andValues .= ", '{$data}'";
	}

	$chkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);

	$query = "SELECT * FROM mt_block_tel";
	$blackData = list_pdo($query, $value);
	while($row = $blackData->fetch(PDO::FETCH_ASSOC)){
		if($row['block_tel'] == $chkTel){
			echo "블랙리스트에 추가된 연락처입니다.";
			return false;
		}
	}

	# 중복검사
	$overlap_yn = "N";
	if($site['overlap_yn'] == "Y"){
		if($site['overlap_days'] > 0){
			$checkAndQuery = ($site['overlap_days']) ? " AND made_date > DATE_ADD(date_format('{$made_date}', '%Y-%m-%d'), INTERVAL - {$site['overlap_days']} day)" : "";
		}

		$checkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);
		$value = array(':checkTel'=>$checkTel);
		$query = "SELECT * FROM mt_db WHERE use_yn = 'Y' AND replace(cs_tel, '-', '') = :checkTel {$checkAndQuery}";
		$overDB = view_pdo($query, $value);
		if($overDB){
			echo "중복 연락처입니다.";
			return false;
		}

	}

	

	$value = array(':made_date'=>$made_date, ':pm_code'=>$pm_code, ':cs_name'=>$cs_name, ':cs_tel'=>$cs_tel, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':overlap_yn'=>$overlap_yn, ':m_idx'=>$user['idx'], ':tm_code'=>$user['tm_code'] );

	$sql = "
		INSERT INTO mt_db
			( made_date, pm_code, cs_name, cs_tel, reg_idx, reg_ip {$andColumns}, overlap_yn, dist_code, dist_date, m_idx, tm_code )
		VALUES
			( :made_date, :pm_code, :cs_name, :cs_tel, :proc_id, :proc_ip {$andValues}, :overlap_yn, '002', now(), :m_idx, :tm_code )
	";

	$exec = execute_pdo($sql, $value);

	if( $exec['data']->rowCount() > 0 ){
		$idx= $exec['insertIdx'];

		$value = array(':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':m_idx'=>$user['idx'], ':tm_code'=>$user['tm_code'] );
		# 분배기록 등록
		$query = "INSERT INTO mt_db_dist_log
				( tm_code, m_idx, db_idx, reg_idx, reg_ip )
			VALUES
				( :tm_code, :m_idx, :idx, :proc_id, :proc_ip )";

		execute_pdo($query, $value);

		# 팀별 분배기록
		$value = array(':code_value'=>$user['tm_code'], ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
		$query = "INSERT INTO mt_db_chart_log
				( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
			VALUES
				( 'tm', :code_value, 'dist', :idx, :proc_id, :proc_ip )";
		execute_pdo($query, $value);

		
		# 팀원별 분배기록
		$value = array(':code_value'=>$user['idx'], ':idx'=>$idx, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip );
		$query = "INSERT INTO mt_db_chart_log
				( code_type, code_value, type_name, db_idx, reg_idx, reg_ip )
			VALUES
				( 'fc', :code_value, 'dist', :idx, :proc_id, :proc_ip )";
		execute_pdo($query, $value);

		$value = array(':team_cnt'=>$team_cnt, ':idx'=>$user['tm_code']);
		$query = "UPDATE mt_member_team SET dist_cnt_now = :team_cnt WHERE idx = :idx";
		execute_pdo($query, $value);

		$value = array(''=>'');
		$query = "select count(*) as cnt from mt_member_team where use_yn = 'Y' AND dist_cnt != dist_cnt_now";
		$reset_cnt = view_pdo($query, $value)['cnt'];
		if($reset_cnt == 0){
			$query = "UPDATE mt_member_team SET dist_cnt_now = 0 WHERE use_yn = 'Y'";
			execute_pdo($query, $value);
		}

		echo "success";
	}  else {
		echo "fail";
	}

?>