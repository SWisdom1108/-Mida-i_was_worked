<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
    $md_idx = (!empty($md_idx)) ? $md_idx : null;
	$dr_idx = (!empty($dr_idx)) ? $dr_idx : null; 
    $value = array( ':treat_code' => $treat_code, ':pay' => $pay, ':md_idx' => $md_idx, ':dr_idx' => $dr_idx, ':edit_idx' => $user['idx'], ':edit_ip' => $proc_ip, ':idx' => $idx);
    $query ="UPDATE mt_pay_log SET
        treat_code = :treat_code,
        pay = :pay,
        md_idx = :md_idx,
        dr_idx = :dr_idx,
        edit_idx = :edit_idx,
        edit_ip = :edit_ip,
        edit_date = NOW()
    WHERE idx = :idx
    ";
    $exec = execute_pdo($query, $value);


	if($exec['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>