<?php
include 'database.php';

$conn = OpenCon();
if ($conn->connect_errno) 
    { echo 'Error'; } 
else { 
    $sql = "CREATE TABLE linkTable (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        link VARCHAR(500) NOT NULL,
        titel VARCHAR(200),
        reg_date TIMESTAMP NULL 
        )";
    
    if ($conn->query($sql) === TRUE) {
      echo "Table LinkTable created successfully";
    } else {
      echo "Error creating table: " . $conn->error;
    }
}

CloseCon($conn);
?>