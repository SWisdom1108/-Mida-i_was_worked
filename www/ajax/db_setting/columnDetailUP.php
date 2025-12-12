<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	// 등록
	foreach( $_POST['w_idxs'] as $row ){
		$name = ehtml($_POST['w_name'.$row]);
		$sort = $_POST['w_sort'.$row];
		if ( $name ){

			$value = array(':name'=>$name, ':sort'=>$sort, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
			$query = "
				INSERT INTO mt_db_cs_info_detail ( info_idx, info_val, sort, reg_idx, reg_ip )
				VALUES ( '{$_POST['info_idx']}', :name, :sort, :proc_id, :proc_ip )
			";
			execute_pdo($query, $value);
		}
	}
	
	foreach( $_POST['u_idxs'] as $row ){
		$name = ehtml($_POST['u_name'.$row]);
		$sort = $_POST['u_sort'.$row];
		$delYn = $_POST['delYn'.$row];
		$andQuery = "";

		if ( $delYn == "Y" ){
			$andQuery = ", use_yn = 'N'";
		}

		if ( $name ){

			$value = array(':name'=>$name, ':sort'=>$sort, ':row'=>$row);
			$query = "
				UPDATE mt_db_cs_info_detail SET
					  info_val = :name
					, sort = :sort
					{$andQuery}
				WHERE idx = :row
			";
			execute_pdo($query, $value);
		}
	}

	$value = array(''=>'');
	$query = "
		SELECT *
		FROM mt_db_cs_info_detail
		WHERE info_idx = '{$_POST['info_idx']}'
		AND use_yn = 'Y'
		ORDER BY sort ASC
	";
	$sql = list_pdo($query, $value);
	// $result = list_sql($sql);
	$sort = 0;
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		$sort++;
		$value = array(':sort'=>$sort);
		$query = "
			UPDATE mt_db_cs_info_detail SET
				sort = '{$sort}'
			WHERE idx = '{$row['idx']}'
		";
		execute_pdo($query, $value);

	}

	echo "success";


?>