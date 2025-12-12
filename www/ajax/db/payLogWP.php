
<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
    $value = array(''=>'');
    $query ="SELECT *
        FROM mt_db_pay
        WHERE idx IN ($idx)
    ";
    $result = list_pdo($query, $value);

    $success = true;

    while($row = $result->fetch(PDO::FETCH_ASSOC)){
        $md_idx = $row['md_idx'] ? $row['md_idx'] : null;
	    $dr_idx = $row['dr_idx'] ? $row['dr_idx'] : null;
        $value = array(':pay_date'=>$row['pay_date'], ':treat_date'=>$row['treat_date'], ':chart_num' => $row['chart_num'], ':insurance_type'=>$row['insurance_type'], ':treat_code' => $row['treat_code'], ':pay_money' => $row['pay'], ':first_date'=>$row['first_date'], ':visit_path'=>$row['visit_path'], ':md_idx' => $md_idx, ':dr_idx' => $dr_idx, ':pay_date' => $row['pay_date'], ':reg_idx'=>$user['idx'], ':reg_ip'=>$proc_ip);
        $query = "
            INSERT INTO mt_pay_log
                (pay_date, treat_date, chart_num, insurance_type, treat_code, pay, first_date, visit_path, md_idx, dr_idx, reg_idx, reg_ip, reg_date)
            VALUES
                (:pay_date, :treat_date, :chart_num, :insurance_type, :treat_code, :pay_money, :first_date, :visit_path, :md_idx, :dr_idx, :reg_idx, :reg_ip, NOW())
        ";

        $exec = execute_pdo($query, $value);

        if($exec['data']->rowCount() > 0){
            $value_del = array(':idx' => $row['idx']);
            $query_del = "
                DELETE FROM mt_db_pay
                WHERE idx = :idx
            ";

            $exec_del = execute_pdo($query_del,$value_del);

            if($exec_del['data']->rowCount() < 1){
                $success = false;
            }
        }else{
            $success = false;
        }
    }

	if($success){
		echo "success";
	}  else {
		echo "fail";
	}

?>