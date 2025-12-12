<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
		header("location: /");
		return false;
	}


	$idxs = $_POST['idxs'];

	$all_permit = "SELECT * FROM mt_permit WHERE idx = '{$idxs}'";
	$value = array(''=>'');
	$query = $all_permit;
	$allPermitYN = view_pdo($query, $value)['all_permit_yn'];
	$permit_yn = ($allPermitYN  == "Y") ? "N" : "Y";


	$value = array(':permit_yn'=> $permit_yn, ':idxs'=> $idxs );
	$sql = "
        UPDATE mt_permit SET all_permit_yn = :permit_yn 
        WHERE idx = :idxs
	";
	
	$exec = execute_pdo($sql, $value);
	if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>