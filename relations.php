<html>
<head> 
 <title> Tabellen erstellen </title> 
</head>
<body>
</body>
</html>


<?php
include 'database.php';

$conn = OpenCon();
if ($conn->connect_errno) 
    { echo 'Error: '. $conn->error;  } 
else { 
    $sql = "CREATE TABLE linkTable (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        link VARCHAR(500) NOT NULL,
        titel VARCHAR(200),
        reg_date TIMESTAMP NULL 
        )";
    
    if ($conn->query($sql) === TRUE) {
      echo "<br>Table LinkTable created successfully<br>";
    } else {
      echo "<br>Error creating table:<br> " . $conn->error;
    }

    $sql = "CREATE TABLE wordTable (
      id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      word VARCHAR(70) NOT NULL
      )";
  
    if ($conn->query($sql) === TRUE) {
      echo "<br>Table WordTable created successfully<br>";
    } else {
      echo "<br>Error creating table: " . $conn->error;
    }

    $sql = "CREATE TABLE wordLinkTable (
      id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      linkid int(6) NOT NULL,
      wordid int(6) NOT NULL
      )";
    
    if ($conn->query($sql) === TRUE) {
      echo "<br>Table WordLinkTable created successfully<br>";
    } else {
      echo "<br>Error creating table:<br> " . $conn->error;
    }
}

CloseCon($conn);
?>