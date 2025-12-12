<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
    $category_name = ehtml($_POST['category_name']);

	$value = array(':category_name' => $category_name, ':category_depth' => $category_depth);
	$query = "SELECT * FROM mc_member_cmpy_category WHERE category_name = :category_name AND category_depth = :category_depth";
	$view = view_pdo($query, $value);
	if($view['category_name']){
		echo "중복된 카테고리 명 입니다.";
		return false;
	}

	$value2 = array(':category_name' => $category_name, ':category_depth' => $category_depth, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip);
	$query2 = "
		INSERT INTO mc_member_cmpy_category
			( category_name, category_depth, reg_idx, reg_ip )
		VALUES
			( :category_name, :category_depth, :proc_id, :proc_ip )
	";


	$exec2 = execute_pdo($query2, $value2);

	if($exec2['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>