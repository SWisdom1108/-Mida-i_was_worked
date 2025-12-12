<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$list = $_POST['idx'];
	
	# 항목설정
	foreach($list as $idx){
		$name = ehtml($_POST["name_{$idx}"]);
		$ex = ehtml($_POST["ex_{$idx}"]);
		$column_type = $_POST["column_type_{$idx}"];
		$use_yn = ($_POST["use_yn_{$idx}"]) ? "Y" : "N";
		$list_yn = ($_POST["list_yn_{$idx}"]) ? "Y" : "N";

		if(!$name && $use_yn == "Y"){
			echo "사용하시려면 항목명을 입력해주세요.";
			return false;
		}
		

		$value = array(':name'=> $name, ':ex'=> $ex, ':column_type'=>$column_type, ':list_yn'=> $list_yn, ':use_yn'=> $use_yn, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':idx'=> $idx);
		$query = "
			UPDATE mt_db_cs_info SET
				  column_name = :name
				, column_ex = :ex
				, column_type = :column_type
				, list_yn = :list_yn
				, use_yn = :use_yn
				, edit_idx = :proc_id
				, edit_ip = :proc_ip
				, edit_date = now()
			WHERE idx = :idx
		";
		execute_pdo($query, $value);

	}

	# 중복검사설정
	$overlap_yn = ($_POST['overlay']) ? "Y" : "N";
	$value = array(':overlap_yn'=> $overlap_yn, ':overlap_days'=> $overlap_days, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip);
	$query = "
			UPDATE mt_site_info SET
			  overlap_yn = :overlap_yn
			, overlap_days = :overlap_days
			, edit_idx = :proc_id
			, edit_ip = :proc_ip
			, edit_date = now()
	";
	execute_pdo($query, $value);


	echo "success";

?>