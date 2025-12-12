<?php

	// count query
	function cnt_sql($sql){
		global $dbConn;
		global $conn;
		switch($dbConn){
			case "dbconn_a" :
				$MySQL = new MySQL();
				$MySQL->db_connect();
				$result = $MySQL->query($sql);
				return mysql_num_rows($result);
			case "dbconn_b" :
				$result = mysqli_query($conn, $sql);
				return mysqli_num_rows($result);
		}
	}

	// list
	function list_sql($sql){
		global $dbConn;
		global $conn;
		switch($dbConn){
			case "dbconn_a" :
				$MySQL = new MySQL();
				$MySQL->db_connect();
				return $MySQL->query($sql);
			case "dbconn_b" :
				$result = mysqli_query($conn, $sql);
				return $result;
		}
	}

	// view
	function view_sql($sql){
		global $dbConn;
		global $conn;
		switch($dbConn){
			case "dbconn_a" :
				$MySQL = new MySQL();
				$MySQL->db_connect();
				$rs = list_sql($sql);
				return mysql_fetch_array($rs,MYSQL_ASSOC);
			case "dbconn_b" :
				$result = mysqli_query($conn, $sql);
				return mysqli_fetch_array($result, MYSQLI_ASSOC);
		}
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

	// insert, delete, update #return affect_row
	function excute($sql){
		global $dbConn;
		global $conn;
		switch($dbConn){
			case "dbconn_a" :
				$MySQL = new MySQL();
				$MySQL->db_connect();
				$MySQL->query($sql);
				return mysql_affected_rows();
			case "dbconn_b" :
				$result = mysqli_query($conn, $sql);
				return mysqli_affected_rows($conn);
		}
	}

?>