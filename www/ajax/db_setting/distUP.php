<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$idx = $_POST['idx'];


	$value = array(''=>'');
	$query = "SELECT count(*) as cnt FROM mt_member_team WHERE use_yn = 'Y' AND (m_idx = 0 || m_idx is null)";
	$view2 = view_pdo($query, $value);
	

	if($auto_dist_pm || $auto_dist_fc){
		foreach ($_POST['pm_code'] as $key => $value) {
	    	if(!($_POST['select_pm_TM'][$key])){
	    		$_POST['select_pm_TM'][$key] = '0000';
	    	}

			$value2 = array(':idx'=>$_POST['select_pm_TM'][$key]);
	    	$query2 = "SELECT m_idx FROM mt_member_team WHERE idx = :idx";
			$midx_yn = view_pdo($query2, $value2)['m_idx'];

	   //  	if(!($midx_yn) && $_POST['select_pm_TM'][$key] != '0000'){
	   //  		echo "[생산업체별 분배설정]\n매칭된 팀 중 팀장이 정해지지 않은 팀이 존재합니다. \n팀장을 정한 후 설정해주세요.";
				// return false;
	   //  	}

	    	$value = array(':value'=>$value);
	    	$query = "UPDATE mt_member_cmpy SET auto_dist_team = '{$_POST['select_pm_TM'][$key]}' WHERE idx = :value";
	    	execute_pdo($query, $value);
		} 
	}else{
		foreach ($_POST['pm_code'] as $key => $value) {
			if(!($_POST['select_pm_TM'][$key])){
				$_POST['select_pm_TM'][$key] = '0000';
			}
	
			$value2 = array(':idx'=>$_POST['select_pm_TM'][$key]);
			$query2 = "SELECT m_idx FROM mt_member_team WHERE idx = :idx";
			$midx_yn = view_pdo($query2, $value2)['m_idx'];
	
			if(!($midx_yn) && $_POST['select_pm_TM'][$key] != '0000'){
				echo "[생산업체별 분배설정]\n매칭된 팀 중 팀장이 정해지지 않은 팀이 존재합니다. \n팀장을 정한 후 설정해주세요.";
				return false;
			}
	
			$value = array(':value'=>$value);
			$query = "UPDATE mt_member_cmpy SET auto_dist_team = '{$_POST['select_pm_TM'][$key]}' WHERE idx = :value";
			execute_pdo($query, $value);
		} 
	}

	if($auto_dist_team){
		$auto_dist = "T";
		if($view2['cnt'] > 0){
			echo "팀장이 정해지지 않은 팀이 존재합니다. \n팀장을 정한 후 자동분배를 설정해주세요.";
			return false;
		}
	}else if($auto_dist_fc){
		$auto_dist = "F";
	}else if($auto_dist_pm){
		$auto_dist = "P";
	}
	else{
		$auto_dist = "N";
	}
	
	$value2 = array(':selectTM'=>$selectTM, ':auto_dist'=>$auto_dist);
	$query2 = "UPDATE mt_site_info SET auto_dist_team = :selectTM, auto_dist_yn = :auto_dist  WHERE idx = 1";
	execute_pdo($query2, $value2);

	foreach($idx as $val){
		$cnt = ${"cnt_{$val}"};
		$sort = ${"sort_{$val}"};


		$value = array(':idx'=>$val);
		$query = "SELECT dist_cnt FROM mt_member WHERE idx = :idx";
		$view3 = view_pdo($query, $value);
		$query = "";
		if($view3['dist_cnt'] != $cnt){
			$query = ", dist_cnt_now = '0'";
		}

		$value3 = array(':sort'=>$sort, ':cnt'=>$cnt, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':val'=>$val);
		$query3 = "
			UPDATE mt_member SET
				  dist_sort = :sort
				, dist_cnt = :cnt
				, edit_idx = :proc_id
				, edit_ip = :proc_ip
				, edit_date = now()
				{$query}
			WHERE idx = :val
		";
		execute_pdo($query3, $value3);
	}

	$idx = $_POST['tmCode'];

	foreach($idx as $val){
		$cnt = ${"tm_cnt_{$val}"};
		$sort = ${"tm_sort_{$val}"};

		$value = array(':idx'=>$val);
		$query = "SELECT dist_cnt FROM mt_member_team WHERE idx = :idx";
		$view3 = view_pdo($query, $value);
		$query = "";
		if($view3['dist_cnt'] != $cnt){
			$query = ", dist_cnt_now = '0'";
		}

		$value4 = array(':sort'=>$sort, ':cnt'=>$cnt, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':val'=>$val);
		$query4 = "
			UPDATE mt_member_team SET
				  dist_sort = :sort
				, dist_cnt = :cnt
				, edit_idx = :proc_id
				, edit_ip = :proc_ip
				, edit_date = now()
				{$query}
			WHERE idx = :val
		";
		execute_pdo($query4, $value4);
	}



	foreach($pm_idx as $val){
		$cnt = ${"pm_cnt_{$val}"};
		$sort = ${"pm_sort_{$val}"};


		// echo $val. ":" .$cnt."\n";

		$value = array(':idx'=>$val);
		$query = "SELECT dist_cnt FROM mt_member_pmDist WHERE idx = :idx";
		$view3 = view_pdo($query, $value);
		$query = "";
		if($view3['dist_cnt'] != $cnt){
			$query = ", dist_cnt_now = '0'";
		}

		$value3 = array(':sort'=>$sort, ':cnt'=>$cnt, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip, ':val'=>$val);
		$query3 = "
			UPDATE mt_member_pmDist SET
				  dist_sort = :sort
				, dist_cnt = :cnt
				, edit_idx = :proc_id
				, edit_ip = :proc_ip
				, edit_date = now()
				{$query}
			WHERE idx = :val
		";

		execute_pdo($query3, $value3);
	}

	echo "success";

?>