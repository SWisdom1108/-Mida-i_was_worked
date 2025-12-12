<?php 
    ini_set('display_errors', 1); 
    ini_set('display_startup_errors', 1); 
    error_reporting(E_ALL); 
    $host = "175.126.0.109:1436"; // ← 여기 덴트웹에서 준 IP 
    $db = "DentWeb"; $user = "dwpublic"; $pass = "dwpublic2!"; 
    try { // 중요: dblib 사용해야 접속됨! 
        $conn = new PDO("dblib:host=$host;dbname=$db;charset=UTF8", $user, $pass); 
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); echo "MSSQL Connected!"; 
    } catch (PDOException $e) { 
        echo "Connection failed: " . $e->getMessage(); 
    }

    $date = date("Ymd", strtotime("-1 day"));

    $conn->query("
    IF OBJECT_ID('tempdb..#TMP_접수') IS NOT NULL DROP TABLE #TMP_접수;
");


// 디비매니저 클라우드 디비연결
    $dbHost = "121.78.125.113";
	$dbUser = "root";
	$dbName = "dbsom_reve";
	$dbPasswd = "1m2i3d4a21@";
    $mysql = new PDO("mysql:host={$dbHost};dbname={$dbName}",$dbUser,$dbPasswd);
	$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$mysql->exec("set names utf8");

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

	function list_pdo($query, $value){
		global $mysql;
		$data = $mysql->prepare($query);
		$data->execute($value); 

		return $data;
	}

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
    $proc_id = 1;
    $proc_ip = $_SERVER['REMOTE_ADDR'];

$conn->query("
    CREATE TABLE #TMP_접수 (
        n상태 tinyint,
        sz차트번호 varchar(30),
        sz이름 varchar(40),
        b신환여부 bit,
        sz접수시각 char(14),
        sz예약시각 char(4),
        n담당의사 smallint,
        sz전화번호 varchar(16),
        sz휴대폰번호 varchar(16),
        접수내용 varchar(400) NULL,
        n환자ID int,
        담당직원 smallint,
        체어 smallint,
        b성별 bit,
        sz생년월일 varchar(8)
    );
");
    $conn->query("INSERT INTO #TMP_접수 EXEC DentWeb.dbo.PUB_P접수목록 '$date'");
    $stmt = $conn->query("
        SELECT 
            T.sz차트번호,
            T.sz이름 AS 고객명,
            T.sz휴대폰번호 AS 휴대폰번호,
            T.담당직원,
            S1.sz이름 AS 담당직원명,
            T.n담당의사,
            S2.sz이름 AS 담당의사명,
            T.sz접수시각,
            T.sz예약시각,
            T.b성별,
            T.sz생년월일 
        FROM #TMP_접수 AS T
        LEFT JOIN PUB_V직원정보 AS S1 ON T.담당직원 = S1.nID
        LEFT JOIN PUB_V직원정보 AS S2 ON T.n담당의사 = S2.nID
        ORDER BY T.sz차트번호 DESC;
    ");
    // $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo "<pre>";
    // print_r($row);
    // echo "</pre>";
    // die();

    // $stmt = $conn->query("exec DentWeb.dbo.PUB_P접수목록 '20251124'");
    // $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo "<pre>";
    // print_r($rows);
    // echo "</pre>";

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $md_idx = ($row['담당직원명']) ? view_pdo("SELECT idx FROM mt_member WHERE use_yn = 'Y' AND m_name = :m_name", array(':m_name' => $row['담당직원명'])): null;
        $dr_idx = ($row['담당의사명']) ? view_pdo("SELECT idx FROM mt_member WHERE use_yn = 'Y' AND m_name = :m_name", array(':m_name' => $row['담당의사명'])) : null;

        $md_idx = (!empty($md_idx['idx'])) ? $md_idx['idx'] : null;
        $dr_idx = (!empty($dr_idx['idx'])) ? $dr_idx['idx'] : null;

        

        $cs_tel = str_replace('-', '', $row['휴대폰번호']);
        $cs_tel2 = preg_replace("/(\d{3})(\d{3,4})(\d{4})/", "$1-$2-$3", $cs_tel);

        $query = "
            UPDATE mt_db SET
                chart_num = :chart_num
            WHERE (cs_tel = :cs_tel OR cs_tel = :cs_tel2)
            AND cs_name = :cs_name
        ";
        $value = array(':chart_num' => $row['sz차트번호'], ':cs_tel' => $cs_tel, ':cs_tel2' => $cs_tel2, ':cs_name' => $row['고객명']);
        $result = execute_pdo($query,$value);

        $query = "
            SELECT idx
            FROM mt_db_dent
            WHERE chart_num = :chart_num
        ";
        $value = array(':chart_num' => $row['sz차트번호']);
        $exist = view_pdo($query, $value);

        $rcpt_date = DateTime::createFromFormat('YmdHis', $row['sz접수시각']);
        $rcpt_date = $rcpt_date ? $rcpt_date->format('Y-m-d H:i:s') : null;


        $resv_date = !empty($row['sz예약시각']) ? DateTime::createFromFormat('Hi', str_pad($row['sz예약시각'], 4, "0", STR_PAD_LEFT)) : null;
        $resv_date = $resv_date ? $resv_date->format('H:i') : null;

        if(isset($row['b성별'])){
            $gender = ($row['b성별'] == '1') ? 'F' : 'M';
        }else{
            $gender = null;
        }

        if ($exist && !empty($exist['idx'])) {
            $value = array(':chart_num' => $row['sz차트번호'], ':rcpt_date' => $rcpt_date, ':edit_idx' => $proc_id, ':edit_ip' => $proc_ip);
            $query = "
                UPDATE mt_db_dent SET
                     rcpt_date = :rcpt_date
                    , edit_idx = :edit_idx
                    , edit_ip = :edit_ip
                    , edit_date = NOW()
                WHERE chart_num = :chart_num
            ";
            execute_pdo($query, $value);
            continue;
        }



        $query = "
            INSERT INTO mt_db_dent (chart_num, dr_idx, md_idx, cs_name, cs_tel, rcpt_date, resv_date, gender, reg_idx, reg_ip, reg_date, use_yn)
            VALUES (:chart_num, :dr_idx, :md_idx, :cs_name, :cs_tel, :rcpt_date, :resv_date, :gender, :reg_idx, :reg_ip, NOW(), 'Y')
        ";
        $value = array( ':chart_num' => $row['sz차트번호'], ':dr_idx' => $dr_idx, ':md_idx' => $md_idx, ':cs_name' => $row['고객명'], ':cs_tel' => $cs_tel, ':rcpt_date'=>$rcpt_date, ':resv_date'=>$resv_date, ':gender'=>$gender, ':reg_idx'=>$proc_id, ':reg_ip' => $proc_ip);
        $insert = execute_pdo($query, $value);
        // $sql_debug = $query;
        // foreach ($value as $k => $v) {
        //     $sql_debug = str_replace($k, "'$v'", $sql_debug);
        // }
        // echo $sql_debug;
        // die();
    }



    // echo "<pre>";
    // print_r($rows);
    // echo "</pre>";
?>