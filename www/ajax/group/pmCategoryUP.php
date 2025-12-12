<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
    $use_yn = ($use_yn) ? "Y" : "N";
    $category_name = ehtml($_POST['category_name']);

	$value = array(':category_name' => $category_name, ':category_depth' => $category_depth, ':category_code' => $category_code);
	$query = "SELECT * FROM mc_member_cmpy_category WHERE category_name = :category_name AND category_depth = :category_depth AND category_code != :category_code";
	$view = view_pdo($query, $value);
	if($view['category_name']){
		echo "중복된 카테고리 명 입니다.";
		return false;
	}

	$value2 = array(':category_name' => $category_name, ':category_depth' => $category_depth, ':proc_id' => $proc_id, ':proc_ip' => $proc_ip, ':use_yn' => $use_yn, ':category_code' => $category_code);
    $query2 = "
        UPDATE mc_member_cmpy_category
        SET category_name = :category_name,
            category_depth = :category_depth,
            edit_idx = :proc_id,
            edit_ip = :proc_ip,
            edit_date = NOW(),
            use_yn = :use_yn
        WHERE category_code = :category_code
    ";

	$exec2 = execute_pdo($query2, $value2);

	if($exec2['data']->rowCount() > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>