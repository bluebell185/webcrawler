<?php
	function OpenCon(){
		$dbhost = "localhost";
		$dbuser = "root";
		$dbpass = "?Passwort!";
		$db = "mydb";
		$conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
		return $conn;
	}

	function CloseCon($conn){
		$conn -> close();
	}

	function getLinkTable($conn){
		$sql = "SELECT * FROM linktable WHERE reg_date < CURRENT_DATE - INTERVAL 1 DAY or reg_date is null";
		if (!$result = $conn->query($sql)) {
			echo 'Error result';
		} else {			
			return $result;	
		}
	}
?>