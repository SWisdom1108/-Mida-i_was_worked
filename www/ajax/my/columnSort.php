<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	foreach ( $sorts as $row ){
		$data = explode("||", $row);

		$value = array(':sort'=> $data[1], ':idx'=> $data[0] );
		$query = "UPDATE mt_db_cs_info SET
				sort = :sort
			WHERE idx = :idx";


		execute_pdo($query, $value);
	}
	// echo $data;
	
	echo "순서를 변경하였습니다.";

?>