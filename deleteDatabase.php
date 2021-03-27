<?php
include 'database.php';

$conn = OpenCon();
if ($conn->connect_errno) 
    { echo 'Error'; } 
else { 
    $sql = "TRUNCATE linkTable";
    
    if ($conn->query($sql) === TRUE) {
      echo "Data of table LinkTable successfully deleted";
    } else {
      echo "Error deleting table data: " . $conn->error;
    }
}

CloseCon($conn);
?>