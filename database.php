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
		$sql = "SELECT * FROM linkTable";
		if (!$result = $conn->query($sql)) {
			echo 'Error result';
		} else {			
			return $result;	
		}
	}
?>