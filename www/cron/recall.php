
<?php
function execute_pdo($query, $value){
	global $mysql;
	$data = $mysql->prepare($query);
	try {
		$data->execute($value);
	} catch(PDOException $e) {
		// return $e;
		return array('errorCode'=>$e->getCode());
	}
	
	
	$insertIdx = $mysql->lastInsertId();
	$errorCode = $mysql->errorCode();
    $result = "";
    return array("insertIdx"=>$insertIdx, "data"=>$data);
}

function list_pdo($query, $value){
	global $mysql;
	$data = $mysql->prepare($query);
	$data->execute($value);

	return $data;
}

function view_pdo($query, $value){
	global $mysql;
	$data = $mysql->prepare($query);
	$data->execute($value); 
	$view = "";
	while($row = $data->fetch(PDO::FETCH_ASSOC)){
		$view = $row;
	}
	
	return $view;
}
function list_sql($sql){
	global $conn;
	$result = mysqli_query($conn, $sql);
    return $result;
}
function execute($sql){
    global $conn;
	$result = mysqli_query($conn, $sql);
    return mysqli_affected_rows($conn);
}
function excute($sql){
    global $conn;
	$result = mysqli_query($conn, $sql);
    return mysqli_affected_rows($conn);
}
function view($sql){
    global $conn;
	$result = mysqli_query($conn, $sql);
	return mysqli_fetch_array($result, MYSQLI_ASSOC);
}
function view_sql($sql){
    global $conn;
	$result = mysqli_query($conn, $sql);
	return mysqli_fetch_array($result, MYSQLI_ASSOC);
}
function rows($sql){
	$result = mysqli_query($sql);
	return $result;
}

    $updateLastIDX = "";
	$conn = mysqli_connect("121.78.125.113", "root", "1m2i3d4a21@","dbsom_reve");
	$db = mysqli_select_db("dbsom_hnjrent", $conn);
    $dbHost = "121.78.125.113";
	$dbUser = "root";
	$dbName = "dbsom_reve";
	$dbPasswd = "1m2i3d4a21@";

	mysqli_query("set session character_set_connection=utf8;");
	mysqli_query("set session character_set_results=utf8;");
	mysqli_query("set session character_set_client=utf8;");

    $mysql = new PDO("mysql:host={$dbHost};dbname={$dbName}",$dbUser,$dbPasswd);
	$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$mysql->exec("set names utf8");

    $proc_id = 0;
	$proc_ip = $_SERVER['REMOTE_ADDR'];

    $cs_status_codes = ['004','005','006','007','008','009','010','011','020'];
    $cs_status_list = "'" . implode("','", $cs_status_codes) . "'";

    $recall_date = date('Y-m-d', strtotime('-14 days'));

    $value = array(''=>'');
    $query = "
        SELECT idx 
        FROM mt_db
        WHERE use_yn = 'Y'
        AND cs_status_code IN ({$cs_status_list})
        AND dist_code = '002'
        AND order_by_date <= '{$recall_date}'
    ";
    $result = list_pdo($query,$value);

    $idx = [];
    while($row = $result->fetch(PDO::FETCH_ASSOC)){
        $idx[] = $row['idx'];
    }

    if(!empty($idx)){
        $value = array(''=>'');
        $idx = implode(',', $idx);
        $query = "
            UPDATE mt_db
            SET dist_code = '003'
            , m_idx = NULL
            , tm_code = NULL
            WHERE idx IN ({$idx})
        ";
        echo $query;
        die();
        $result = execute_pdo($query, $value);
    }

?>