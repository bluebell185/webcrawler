<?php
include 'database.php';

$conn = OpenCon();
if ($conn->connect_errno) 
    { echo 'Error'; } 
else { 
    $sql = "CREATE TABLE linkTable (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        link VARCHAR(500) NOT NULL,
        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
    
    if ($conn->query($sql) === TRUE) {
      echo "Table LinkTable created successfully";
    } else {
      echo "Error creating table: " . $conn->error;
    }
}

CloseCon($conn);
?>