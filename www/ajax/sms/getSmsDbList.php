<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

    header('Content-Type: application/json');
    post2val();

    $searchQuery = "";
    $query_data = [];

    if(isset($label) && $label && isset($value) && $value) {
        $searchQuery .= " AND " . $label . " LIKE CONCAT('%', :searchValue, '%')";
        $query_data[":searchValue"] = $value;
    }

    if(isset($setDate) && $setDate) {
        if(isset($s_date) && $s_date) {
            $searchQuery .= " AND DATE_FORMAT(" . $setDate . "_date, '%Y-%m-%d') >= :s_date";
            $query_data[":s_date"] = $s_date;
        }
        if(isset($e_date) && $e_date) {
            $searchQuery .= " AND DATE_FORMAT(" . $setDate . "_date, '%Y-%m-%d') <= :e_date";
            $query_data[":e_date"] = $e_date;
        }
    }

    $query = "
        SELECT
            idx
            , m_idx
            , cs_name
            , cs_tel
            , made_date
            , reg_date
        FROM mt_db
        WHERE use_yn = 'Y'
        {$searchQuery}
        ORDER BY reg_date DESC
    ";
    
    $result = list_pdo($query, $query_data);
    $response = []; // 결과를 담을 배열

    // 결과를 배열에 담기
    while($row = $result->fetch(PDO::FETCH_ASSOC)){
        $response["data"][$row["idx"]]['m_idx'] = $row['m_idx'];
        $response["data"][$row["idx"]]['cs_name'] = dhtml($row['cs_name']);
        $response["data"][$row["idx"]]['cs_tel'] = $row['cs_tel'] ? $row['cs_tel'] : "-";
        $response["data"][$row["idx"]]['made_date'] = $row["made_date"] ? date("Y-m-d", strtotime($row["made_date"])) : "-";
        $response["data"][$row["idx"]]['reg_date'] = $row["reg_date"] ? date("Y-m-d", strtotime($row["reg_date"])) : "-";
    }

    (count($response["data"]) > 0) ? $response["status"] = "success" : $response["status"] = "noData";

	# 결과추출
	echo json_encode($response);

?>