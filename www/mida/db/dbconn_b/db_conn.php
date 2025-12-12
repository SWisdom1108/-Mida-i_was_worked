<?php

	$conn = mysqli_connect($dbHost, $dbUser, $dbPasswd, $dbName);

	if (!$conn) {
		die("Connection failed: " . mysqli_connect_error());
	}

?>