<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

    if(!$permit_ip) {
        echo "IP를 입력해주세요";
        return false;
    }

    $value = array(':permit_ip'=> $permit_ip, ':ipName'=> $ipName, ':proc_id'=> $proc_id, ':proc_ip'=> $proc_ip, ':idx'=> $idx);

    $sql = "
        UPDATE mt_permit_ip SET
            permit_ip = :permit_ip
            , ip_name = :ipName
            , edit_idx = :proc_id
            , edit_ip = :proc_ip
            , edit_date = now()
        WHERE idx = :idx
    ";

    $exec = execute_pdo($sql, $value);

    if($exec['data']->rowCount() > 0){
        echo "success";
    } else {
        // echo $sql;
        echo "fail";
    }
?>