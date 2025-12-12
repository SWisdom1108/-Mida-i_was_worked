<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

	$first = "";
	foreach ( $sorts as $key => $row ){
		$data = explode("||", $row);
		$value = array(':sort'=> $data[1], ':idx'=> $data[0] );
		$query = "
			UPDATE mc_db_cs_status SET
				sort = :sort
			WHERE status_code = :idx
		";
		execute_pdo($query, $value);

		if ( $key == 0 ){
			$first = $data[0];
		}
	}

	// alter
	// if ( $first ){
	// 	$query = "ALTER TABLE mt_db ALTER COLUMN cs_status_code SET DEFAULT :first";
	// 	$value = array(':first'=> $first );
	// 	execute_pdo($query, $value);

	// 	$query = "ALTER TABLE mt_db_cs_log ALTER COLUMN status_code SET DEFAULT :first";
	// 	$value = array(':first'=> $first );
	// 	execute_pdo($query, $value);
	// }
	
	echo "순서를 변경하였습니다.";

?>