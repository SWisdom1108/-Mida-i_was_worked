<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$andQuery = "";

	$value1 = array(':idx'=>$idx);
	$query1 = "SELECT COUNT(*) AS cnt FROM mt_db where m_idx = :idx";
	$cnt = view_pdo($query1, $value1);
	// $cnt = view_sql("SELECT COUNT(*) AS cnt FROM mt_db where m_idx = '{$idx}'")['cnt'];

	if ( $cnt['cnt'] > 0 ){
		echo "fail_cnt";
	}else{
		$value2 = array(':idx'=>$idx);
		$query2 = "DELETE FROM mt_member WHERE idx = :idx";
		$result = execute_pdo($query2, $value2);


		// $sql = "
		// 	DELETE FROM mt_member WHERE idx = '{$idx}';
		// ";
		if ( $result > 0 ){
			$value3 = array(':idx'=>$idx);

			$query3 = "DELETE FROM mt_db_dist_log WHERE m_idx = :idx";
			$query4 = "DELETE FROM mt_login_log WHERE reg_idx = :idx";
			$query5	= "DELETE FROM mt_notification WHERE m_idx = :idx";

			execute_pdo($query3, $value3);
			execute_pdo($query4, $value3);
			execute_pdo($query5, $value3);

			// excute("DELETE FROM mt_db_dist_log WHERE m_idx = '{$idx}';");
			// excute("DELETE FROM mt_login_log WHERE reg_idx = '{$idx}';");
			// excute("DELETE FROM mt_notification WHERE m_idx = '{$idx}';");

			echo "success";
		}else{
			echo "fail";
		}

	}

?>