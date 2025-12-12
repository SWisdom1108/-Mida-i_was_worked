<?
/**
 * PROGRAM NAME	: db_conn.inc
 * COMAPNY		: nemosoft
 * AUTHOR		:
 * DATE			: 2009.09.09
 * DESCRIPTION	: MySQL 연결 및 I/D/U/S 관련 클래스
 */

class MySQL
{
	var $conn		=	null;
	var $connected	=	0	;
	var $query		=	''	;
	var $stmt				;
	var $pstmt				;
	var $result_set			;
	var $reference	=	0	;
	var $host		=	null;
	var $port		=	null;
	var $name		=	null;
	var $id			=	null;
	var $pw			=	null;

	function db_connect()
	{
		if(0==$this->reference)
		{
			$this->db_server = DB_SERVER;
//			$this->db->port = '3306';
			$this->db_name = DB_NAME;
			$this->db_user = DB_USER;
			$this->db_passwd = DB_PASSWD;
			$this->host = $this->db->port?$this->db_server.":".$this->db->port : $this->db_server;
			$this->conn = $this->connect($this->host, $this->db_user, $this->db_passwd);
			$this->select_db($this->db_name);
			$this->reference++;
		}
	}

	function db_disconnect()
	{
		$this->reference--;
		if(0==$this->reference)
		{
			if(is_resource($this->result_set)) $this->free_result();
			$this->close();
		}
	}

	function connect($host_name, $user, $password)
	{
		$this->conn = @mysql_connect($host_name, $user, $password) or $this->error('현재 사용자의 연결이 많아 서버가 혼잡하고 있습니다.',$this->_error());
		return $this->conn;
	}

	function pconnect($host_name, $user, $password)
	{
		$this->conn = @mysql_pconnect($host_name, $user, $password) or $this->error('현재 사용자의 연결이 많아 서버가 혼잡하고 있습니다.',$this->_error());
		return $this->conn;
	}

	function close()
	{
		return @mysql_close($this->conn);
	}

	function select_db($db_name)
	{
		$r = @mysql_select_db($db_name) or $this->error('データベースを選択できません。',$this->_error());
		return $r;
	}

	function change_db($db_name)
	{
		$r = @mysql_select_db($db_name,$this->conn) or $this->error('데이터베이스를 선택할 수 없습니다.',$this->_error());
		return $r;
	}

	function create_db($db_name)
	{
		$r = @mysql_create_db($db_name) or $this->error('데이터베이스를 생성 할 수 없습니다.',$this->_error());
		return $r;
	}

	function drop_db($db_name)
	{
		$r = @mysql_drop_db($db_name) or $this->error('데이터베이스를 생성 할 수 없습니다.',$this->_error());
		return $r;
	}

	function ping($conn)
	{
		return @mysql_ping($conn);
	}

	function articles($query)
	{
		$this->result_set = $this->query($query);
		return $this->num_rows();
	}

	function query($query)
	{
		//$this->result_set = @mysql_query($query) or $this->error('잘못된 질의문을 전송できません。',$this->_error().$query);
		@mysql_query("SET NAMES utf8");
		$this->result_set = @mysql_query($query);
		return $this->result_set;
	}

	function unbuffered_query($query,$result_mode=1)
	{
		$result_mode = $result_mode ? "MYSQL_USE_RESULT" : "MYSQL_STORE_RESULT";
		$this->result_set = @mysql_unbuffered_query($query,$result_mode) or $this->error('쿼리를 전달할 수 없습니다.',$this->_error());
		return $this->result_set;
	}

	function last_id()
	{
		return @mysqli_insert_id($conn); //이전의 INSERT 작업으로부터 生成된 ID를 반환
	}

	function num_max($table_name, $fields)
	{
		$query = "select if(isnull(max($fields)),'1',max($fields)+1) as max_result from $table_name";
		$r = $this->fetch_array($query);
		return $r[max_result];
	}

	function db_query($db_name, $query)
	{
		$this->result_set = mysql_db_query($db_name, $query);
		return $this->result_set;
	}

	function lock($table_name, $mode="read")
	{
		$query = "lock tables ". $table_name ." ". $mode;
		$this->query($query);
	}

	function unlock()
	{
		$query = "unlock tables";
		$this->query($query);
	}

	function result($row, $fields)
	{
		if($this->num_rows()) $r = @mysql_result($this->result_set, $row, $fields);
		else $r = 0;
		return $r;
	}
	
	function free_result()
	{
		return @mysql_free_result($this->result_set);
	}

	function fetch_array($query)
	{
		$this->result_set = $this->query($query);
		$r =  @mysql_fetch_array($this->result_set);
		return $r;
	}

	function fetch_array2($query)
	{
		$r = array();
		$this->result_set = $this->query($query);
		for($m=0; $m < $this->num_rows(); $m++)
		{
			for($k=0; $k < $this->num_fields(); $k++)
			{
				$r[$m][$this->field_name($k)] = $this->result($m,$this->field_name($k));
			}
		}
		return $r;
	}

	function fetch_array3($query)
	{
		$r = array();
		$this->result_set = $this->query($query);

		for($k=0; $k < $this->num_fields(); $k++)
		{
			$col = array();
			for($m=0; $m < $this->num_rows(); $m++)
			{
				array_push($col ,$this->result($m,$this->field_name($k)) );
			}
			$r[$this->field_name($k)] = $col;
		}
		return $r;
	}

	// Key => Value 형식배열
	function fetch_array4($query)
	{
		$r = array();
		$this->result_set = $this->query($query);

		for($m=0; $m < $this->num_rows($rs); $m++)
		{
			$r[$this->result($m,$this->field_name(2))] = $this->result($m,$this->field_name(1));
		}
		return $r;
	}

	function fetch_row($query)
	{
		$this->result_set = $this->query($query);
		$r = @mysql_fetch_row($this->result_set);
		return $r;
	}

	function fetch_row2($query)
	{
		$r = array();
		$this->result_set = $this->query($query);
		for($m=0; $m < $this->num_rows($rs); $m++)
		{
			for($k=0; $k < $this->num_fields($rs); $k++)
			{
				$r[$m] = $this->result($m,$this->field_name($k));
			}
		}
		return $r;
	}

	function fetch_assoc($query)
	{
		$this->result_set = $this->query($query);
		$r = @mysql_fetch_assoc($this->result_set) or $this->error('시스템 사정에 따라 결과를 전송할 수 없습니다.',$this->_error());
		return $r;
	}

	function fetch_object($query)
	{
		$this->result_set = $this->query($query);
		$r =  @mysql_fetch_object($this->result_set) or $this->error('시스템 사정에 따라 결과를 전송할 수 없습니다.',$this->_error());
		return $r;
	}

	function field_name($field_index)
	{
		return @mysql_field_name($this->result_set,$field_index);
	}

	function num_rows()
	{
		return @mysql_num_rows($this->result_set);
	}

	function is_rows()
	{
		return $this->num_rows();
	}

	function affected_rows()
	{
		return @mysql_affected_rows();
	}

	function is_affected()
	{
		return $this->affected_rows();
	}

	function num_fields()
	{
		return @mysql_num_fields($this->result_set);
	}

	function is_fields()
	{
		return $this->num_fields();
	}

	function is_tables($table_name,$owner)
	{
		$tables = array();
		$this->result_set = @mysql_list_tables($owner[2]);
		while ($r = mysql_fetch_row($this->result_set)) $tables[] = $r[0];
		return (in_array($table_name, $tables));
	}

	function log_error($qry)
	{
		$str = str_replace('\'','\\\'',$qry);
		$query = "insert into im_error (domain,url,error,writeday) values('".$_SERVER['HTTP_HOST']."', '".$_SERVER['REQUEST_URI']."', '$str', now())";
		//echo $query;
		//exit;
		$this->query($query);
	}

	function safestr($str)
	{
		return @mysql_escape_string($str);
	}

	function _error()
	{
		return mysql_errno() . ": " . mysql_error();
	}

	function error($msg, $hmsg='')
	{
		echo "<!--
			$msg
			$hmsg
			-->";
		exit;
	}
}


/**
* ------------------------------------------------------------
* | MySQL query interface definition                                         
* ------------------------------------------------------------
* | @version	class.mysql.php v 1.0 2010-12-21                             
* | @since					                                                        
* | @author		이경진								                            
* | @update     2013-02-04 이종규 - 국내샵링커->EC텐쵸용도로 변환
*-------------------------------------------------------------
*/

/* MySQL debug mode */
define('DB_MYSQL_ERROR_DEBUG',	1);
define('DB_MYSQL_ERROR_IGNORE',	0);

/* 쿼리 에러 발생시 스크립트 계속 실행 여부 */
define('DB_MYSQL_ONERROR_GO',	1);
define('DB_MYSQL_ONERROR_STOP',	0);

/* message output format */
define('DB_MYSQL_OUTPUT_HTML',	1);
//define('DB_MYSQL_OUTPUT_JS',	2);
define('DB_MYSQL_OUTPUT_TXT',	3);

if (!class_exists('MySQL_I')) {

	class MySQL_I
	{
		/**
		* MySQL 디비커넥션
		*
		* @var		objeck
		* @access	private
		*/
		private $_connection;

		/**
		* MySQL 쿼리결과
		*
		* @var		objeck
		* @access	private
		*/
		private $result_set;

		/**
		* MySQL debug mode
		*
		* @var		int
		* @access	private
		*/
		private $mysql_debug;
		
		/**
		 * SQL 쿼리 에러 발생시 스크립트 계속 실행 여부
		 *
		 * @var		int
		 * @access	private
		 */
		private $mysql_onerror_go;

		/**
		* error message output format (html|js|txt)
		* default value 'html'
		*
		* @var		int
		* @access	private
		*/
		private $msg_output_format;

		private $varsBound = false;

		/**
		* db_mysql constructor
		*
		* @param	int		$debug_mode
		* @param	int		$onerror_go
		* @param	int		$output_format
		*
		* @access	public
		*/
		public function __construct(	$debug_mode=DB_MYSQL_ERROR_IGNORE,
										$onerror_go=DB_MYSQL_ONERROR_STOP,
										$output_format=DB_MYSQL_OUTPUT_HTML)
		{
			$this->mysql_debug			= $debug_mode;
			$this->mysql_onerror_go		= $onerror_go;
			$this->msg_output_format	= $output_format;
		} // end func __consturct

		/**
		* get API version
		*
		* @return	double
		* @access	public
		*/
		public function api_version()
		{
			return 1.0;
		} // end func api_version


		public function setDebugMode($debug_mode)
		{
			$this->mysql_debug = $debug_mode;
		} // end func setDebugMode

		public function getDebugMode()
		{
			return $this->mysql_debug;
		} // end func getDebugMode

		public function SetOnErrorGo($onerror_go)
		{
			$this->mysql_onerror_go = $onerror_go;
		} // end func SetOnErrorGo

		public function GetOnErrorGo()
		{
			return $this->mysql_onerror_go;
		} // end func GetOnErrorGo

		public function setOutputFormat($format)
		{
			$this->msg_output_format = $format;
		} // end func setOutputFormat

		public function getOutputFormat()
		{
			return $this->msg_output_format;
		} // end func getOutputFormat
		
		/**
		* connect to a database
		*
		* @param	string		$svc	database connection service name
		* @return	object				MySQL link identifier on success, false on failure
		* @since
		* @access	public
		*/
		public function connect($svc)
		{
			// 디비서버 접속경로
			$dsninfo = $this->get_dsn($svc);
			// 객체생성
			$mysqli = new mysqli($dsninfo['host'], $dsninfo['user'], $dsninfo['pwd'], $dsninfo['db']);
			// 문자셋 설정
			$mysqli->set_charset('utf8');

			// 생성객체 클래스내 격납
			$this->_connection = $mysqli;
			// 예외처리
			if ($this->mysql_debug == DB_MYSQL_ERROR_DEBUG && mysqli_connect_errno()) {
				return $this->raiseError();
			}
			return $mysqli;
		}

//		public function connect_pdo($svc)
//		{
//			$dsninfo = $this->get_dsn($svc);
//	//		$mysqli = new PDO($dsninfo['host'], $dsninfo['user'], $dsninfo['pwd'], $dsninfo['db']);
//			$_connectionString = "mysql:host=" . $dsninfo['host'] . "; dbname=" . $dsninfo['db'];
//			$mysqli = new PDO($_connectionString, $dsninfo['user'], $dsninfo['pwd']);
//
//			$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//			$error = $mysqli->errorInfo();
//			if($error[0] != "") {
//				  print "<p>DATABASE CONNECTION ERROR:</p>";
//				  print_r($error);
//			}
//
//
//	//		if ($this->mysql_debug == DB_MYSQL_ERROR_DEBUG && mysqli_connect_errno()) $this->raiseError();
//			return $mysqli;
//		} // end func connect

		/**
		* disconnect from the database
		*
		* @param	mixed	$_connection
		*
		* @return	void
		* @access	public
		*/
		public function db_disconnect()
		{
			// DB커넥션 객체
			$_connection = $this->_connection;
			if (is_array($_connection)) {
				foreach ($_connection as $key => $obj) {
					if (is_object($obj)) $obj->close();
					$_connection[$key] = null;
				}
			} else {
				if (is_object($_connection)) $_connection->close();
				$_connection = null;
			}
		} // end func disconnect

		/**
		* Get MySQL error information
		*
		* @param	object		$mysqli		mysqli object
		* @param	mixed		$result		true/false/mysql result object
		* @param	string		$query
		*
		* @return	void
		* @access	private
		*/
		private function raiseError(&$mysqli=null, &$result=false, $query='')
		{
			if (!$result) {
				if (is_object($mysqli)) {
					$errno	= $mysqli->errno;
					$errmsg	= $mysqli->error;
				} else {
					$errno	= mysqli_connect_errno();
					$errmsg	= mysqli_connect_error();
				}
				
				switch ($this->msg_output_format) {
					case DB_MYSQL_OUTPUT_HTML :
						$error_msg = "<p class=\"ltorange_bold\">[Error# : " . $errno . "]\n";
						$error_msg .= $errmsg . "<br />\n" . nl2br($query) . "</p>\n";
						return $error_msg;
						break;
					case DB_MYSQL_OUTPUT_TXT :
						$error_msg = printf("[Error# : %s] %s\n%s\n", $errno, $errmsg, $query);
						return $error_msg;
						break;
					default :
						return get_class($this) . "::raiseError() error : unknown output type (" . $this->msg_output_format . ")\n";
				} // end switch
				if ($this->mysql_onerror_go == DB_MYSQL_ONERROR_STOP) exit;
			} // end if
		} // end func raiseError
		
		/**
		* send a query
		*
		* @param	string		$query
		* @param	object		$mysqli
		* @param	int			$resultmode
		*
		* @return	mixed		true/false/MySQL result object
		* @access	public
		*/
		public function query($query, $resultmode=MYSQLI_STORE_RESULT)
		{
			$mysqli = $this->_connection;
			$result = $mysqli->query($query, $resultmode);
			if ($mysqli->error){
				return $this->raiseError($mysqli, $result, $query);
			}
			return $result;
		} // end func query
		
		/**
		* send a multi_query
		*
		* @param	string		$query
		* @param	int			$resultmode
		*
		* @return	mixed		true/false/MySQL result object
		* @access	public
		*/
		public function multi_query($query, $resultmode=MYSQLI_STORE_RESULT)
		{
			// 멀티 결과셋
			$result_list = array();
			// 커넥션
			$mysqli = $this->_connection;

			// 멀티쿼리 실행
			if (mysqli_multi_query($mysqli, $query)) {
				
				do {

					/* 처음결과셋을 저장 */
					if ($result = mysqli_store_result($mysqli)) {
						// 결과셋 추가
						array_push($result_list ,$result->fetch_array());
						// 결과초기화
						mysqli_free_result($result);
							
					// TODO 인수에러
					}	else	{
					
					}
				} while (mysqli_next_result($mysqli));

				// 내부격납
				$this->result_set = $result_list;
				
				// 성공 리턴
				return 1;
				
			// 예외처리
			}	else	{
				//에러 리턴
				return $this->raiseError($mysqli, $result, $query);
			}
		}

		// 쿼리결과셋 가져오기
		public function get_resultSet()
		{
			return $this->result_set;
		}
		// 쿼리결과셋 초기화
		public function free_resultSet()
		{
			$this->result_set=NULL;
		}

		/**
		* fetch a row
		*
		* @param	object		$result
		* @param	int			$fetchmode {MYSQL_ASSOC|MYSQL_NUM|MYSQLI_ASSOC|MYSQLI_NUM}
		* @param	int			$rownum
		*
		* @return	array
		* @access	public
		*/
		public function fetch_array($query, $fetchmode=MYSQL_ASSOC, $rownum=null)
		{
			// 쿼리실행
			$result = $this->query($query);
			
			//결과 배열격납
			$arr = array();
			if (is_object($result)) {
				if ($rownum != null) {
					if (!$result->data_seek($rownum)) return $arr;
				}
				if ($fetchmode == MYSQL_ASSOC || $fetchmode == MYSQLI_ASSOC) {
					$arr = $result->fetch_assoc();
				} else if ($fetchmode == MYSQL_NUM || $fetchmode == MYSQLI_NUM) {
					$arr = $result->fetch_row();
				} else {
					$arr = $result->fetch_array();
				}
				
				return $arr;
			// 예외 리턴
			}	else	{
				return $result;	
			}// end if
		} // end func fetch_array

		/**
		* fetch a result data
		*
		* @param    object		$result
		* @param    int         $row
		* @param    mixed       $field
		*
		* @return   mixed
		* @access   public
		* @since    v 1.2
		*/
		public function fetch_result(&$result, $row=0, $field=0)
		{
			$arr = $this->fetch_array($result, MYSQLI_BOTH, $row);
			return $arr[$field];
		} // end func fetch_result


		public function free_result()
		{	
			$result = $this->result_set;
			if (is_object($result)) $result->free();
			$result = null;
		}

		public function num_rows()
		{
			$result = $this->result_set;
			if (is_object($result))
				return $result->num_rows;
			else
				return false;
		}

		public function affected_rows(&$mysqli)
		{
			return $mysqli->affected_rows;
		}

		public function insert_id(&$mysqli)
		{
			return $mysqli->insert_id;
		}

		public function autocommit(&$mysqli, $option=FALSE)
		{
			return $mysqli->autocommit($option);
		}

		public function rollback(&$mysqli)
		{
			return $mysqli->rollback;
		}

		public function commit(&$mysqli)
		{
			return $mysqli->commit;
		}

		/**
		* prepare(stmt prerare)
		*
		* @param	object		mysqli
		* @param	object		oStmt
		* @param	string		sQuery
		* @return	object
		* @since	v 1.0
		* @access	public
		*/
		public function prepare(&$mysqli, $oStmt, $sQuery) {

			if (is_object($oStmt)) {
				$oStmt->close();
			}

			//stmt 초기화
			$oStmt = $mysqli->stmt_init();

			$oStmt->prepare($sQuery);

			return $oStmt;
		}


		public function stmt_num_rows(&$oStmt)
		{
			if (is_object($oStmt)) {
				$oStmt->store_result();
				return $oStmt->num_rows;
			} else
				return false;
		}

		/**
		* fetch_array(stmt prerare)
		*
		* @param	object		stmt
		* @return	array
		* @since	v 1.3
		* @access	public
		*/
		public function stmt_fetch_array(&$oStmt) {

			$data =$oStmt->result_metadata();
			$fields = array();
			$out = array();

			$fields[0] = $stmt;
			$count = 1;

			while($field = $data->fetch_field()) {
				$fields[$count] = &$out[$field->name];
				$count++;
			}

			call_user_func_array(array($oStmt, "bind_result") , $out);
			return $out;
		}

		/**
		* fetch_assoc(stmt prerare)
		*
		* @param	object		stmt
		* @return	array
		* @since	v 1.3
		* @access	public
		*/
		public function stmt_fetch_assoc(&$oStmt, $fetchmode=MYSQL_ASSOC) {
			// checks to see if the variables have been bound, this is so that when
			//  using a while ($row = $this->stmt->fetch_assoc()) loop the following
			// code is only executed the first time

			if (!$this->varsBound) {

	//			$oStmt->store_result();

				$meta = $oStmt->result_metadata();
				while ($column = $meta->fetch_field()) {
					// this is to stop a syntax error if a column name has a space in
					// e.g. "This Column". 'Typer85 at gmail dot com' pointed this out
					$columnName = str_replace(' ', '_', $column->name);
					$bindVarArray[] = &$oStmt->results[$columnName];

				}
	//			print_r($bindVarArray);
				call_user_func_array(array($oStmt, 'bind_result'), $bindVarArray);

				$this->varsBound = true;
			}
			if ($oStmt->fetch() != null) {
				// this is a hack. The problem is that the array $this->results is full
				// of references not actual data, therefore when doing the following:
				// while ($row = $this->stmt->fetch_assoc()) {
				// $results[] = $row;
				// }
				// $results[0], $results[1], etc, were all references and pointed to
				// the last dataset
				$i = 0;

				foreach ($oStmt->results as $k => $v) {
					if ($fetchmode == MYSQL_ASSOC || $fetchmode == MYSQLI_ASSOC) {
						$results[$i] = $v;
						$results[$k] = $v;
					} else if ($fetchmode == MYSQL_NUM || $fetchmode == MYSQLI_NUM) {
						$results[$i] = $v;
					} else {
						$results[$k] = $v;
					}
					$i++;
				}
				return $results;
			} else {

				$this->varsBound = false;
				return null;
			}

		}


		public function bind_param(&$oStmt, $params) {
			$args   = array();
			$args[] = implode('', array_values($params));

			foreach ($params as $paramName => $paramType) {
				$args[] = &$params[$paramName];
				$params[$paramName] = null;
			}

			call_user_func_array(array($oStmt, 'bind_param'), $args);

			return $params;
		}


		function ClearRecordsets($p_Result)	{

			$p_Result->free();    

			while($this->_connection->next_result()){
			  if($l_result = $this->_connection->store_result()){
					  $l_result->free();
			  }
			}
		}


		/**
		 * Ping a server connection
		 *
		 * @param	object		$mysqli
		 * @return	boolean
		 * @since	v 1.3
		 */
		public function ping()
		{
			$mysqli = $this->_connection;
			return $mysqli->ping();
		}

		/**
		* get a data source name
		*
		* @param	string	$svc	database connection service name
		* @return	array
		* @access	private
		*/
		private function get_dsn($svc)
		{
			$dsn = array(	'host'	=> null,
							'db'	=> null,
							'user'	=> null,
							'pwd'	=> null);
			switch ($svc) {
				case 'main' :
					$dsn['host']	= DB_SERVER;
					$dsn['db']		= DB_NAME;
					$dsn['user']	= DB_USER;
					$dsn['pwd']		= DB_PASSWD;
					break;
				default :
					$dsn['host']	= 'localhost';
					$dsn['db']		= 'test';
			} // end switch
			return $dsn;
		} // end func get_dsn
	} // end class db_mysql
}

?>