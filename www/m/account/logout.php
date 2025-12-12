<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php
	
	session_destroy();
	www("/m/index.php");

?>