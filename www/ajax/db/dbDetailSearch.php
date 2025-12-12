<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();

  $values = [];
  $params = [];
  $query = "SELECT * FROM mt_db WHERE 1=1 ";

  if (!empty($searchName)) {

    $params[] = 'cs_name=$value=' . $searchName;
  }
  if (!empty($searchTel)) {
    $query .= " AND cs_tel LIKE :cs_tel ";
    $values[':cs_tel'] = $searchTel . "%";
    
    $params[] = 'cs_tel=' . $searchTel;
  }
  if (!empty($dist_date)) {
    $query .= " AND dist_date >= :dist_date_start AND dist_date < :dist_date_end ";
    $values[':dist_date_start'] = $dist_date . " 00:00:00";
    $values[':dist_date_end']   = date("Y-m-d H:i:s", strtotime($dist_date . " +1 day"));
    
    $params[] = 's_date=' . $dist_date . " 00:00:00";
    $params[] = 'e_date=' . date("Y-m-d H:i:s", strtotime($dist_date . " +1 day"));
  }
  if (!empty($reg_date)) {
    $query .= " AND reg_date >= :reg_date_start AND reg_date < :reg_date_end ";
    $values[':reg_date_start'] = $reg_date . " 00:00:00";
    $values[':reg_date_end']   = date("Y-m-d H:i:s", strtotime($reg_date . " +1 day"));
    
    $params[] = 'reg_date_start=' . $reg_date . " 00:00:00";
    $params[] = 'reg_date_end=' . date("Y-m-d H:i:s", strtotime($reg_date . " +1 day"));
  }
  // "선택" 값은 빈 값으로 처리
  if (!empty($cs_status_code) && $cs_status_code != "선택") {
    $query .= " AND cs_status_code = :cs_status_code ";
    $values[':cs_status_code'] = $cs_status_code;
    
    $params[] = 'cs_status_code=' . $cs_status_code;
  }
  // "선택" 값은 빈 값으로 처리
  if (!empty($grade_code) && $grade_code != "선택") {
    $query .= " AND grade_code = :grade_code ";
    $values[':grade_code'] = $grade_code;
    
    $params[] = 'grade_code=' . $grade_code;
  }

  if (!empty($cs_etc01)) {
    $query .= " AND cs_etc01 = :cs_etc01 ";
    $values[':cs_etc01'] = $cs_etc01;
    
    $params[] = 'cs_etc01=' . $cs_etc01;
  }
  if (!empty($cs_etc02)) {
    $query .= " AND cs_etc02 = :cs_etc02 ";
    $values[':cs_etc02'] = $cs_etc02;
    
    $params[] = 'cs_etc02=' . $cs_etc02;
  }
  if (!empty($cs_etc03)) {
    $query .= " AND cs_etc03 = :cs_etc03 ";
    $values[':cs_etc03'] = $cs_etc03;
    
    $params[] = 'cs_etc03=' . $cs_etc03;
  }
  if (!empty($cs_etc04)) {
    $query .= " AND cs_etc04 = :cs_etc04 ";
    $values[':cs_etc04'] = $cs_etc04;
    
    $params[] = 'cs_etc04=' . $cs_etc04;
  }
  if (!empty($cs_etc05)) {
    $query .= " AND cs_etc05 = :cs_etc05 ";
    $values[':cs_etc05'] = $cs_etc05;
    
    $params[] = 'cs_etc05=' . $cs_etc05;
  }
  if (!empty($cs_etc06)) {
    $query .= " AND cs_etc06 = :cs_etc06 ";
    $values[':cs_etc06'] = $cs_etc06;
    
    $params[] = 'cs_etc06=' . $cs_etc06;
  }
  if (!empty($cs_etc07)) {
    $query .= " AND cs_etc07 = :cs_etc07 ";
    $values[':cs_etc07'] = $cs_etc07;
    
    $params[] = 'cs_etc07=' . $cs_etc07;
  }
  if (!empty($cs_etc08)) {
    $query .= " AND cs_etc08 = :cs_etc08 ";
    $values[':cs_etc08'] = $cs_etc08;
    
    $params[] = 'cs_etc08=' . $cs_etc08;
  }
  if (!empty($cs_etc09)) {
    $query .= " AND cs_etc09 = :cs_etc09 ";
    $values[':cs_etc09'] = $cs_etc09;
    
    $params[] = 'cs_etc09=' . $cs_etc09;
  }
  if (!empty($cs_etc10)) {
    $query .= " AND cs_etc10 = :cs_etc10 ";
    $values[':cs_etc10'] = $cs_etc10;
    
    $params[] = 'cs_etc10=' . $cs_etc10;
  }
  
	$list = list_pdo($query, $values);

  $queryString = count($params) > 0 ? '?' . implode('&', $params) : '';

	$response = [
    "status" => "success",
    "queryString" => $queryString
	];

  // print_r($queryString);

  echo json_encode($response);
  exit;
?>