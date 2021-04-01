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

    $sql = "TRUNCATE wordTable";
    
    if ($conn->query($sql) === TRUE) {
      echo "Data of table WordTable successfully deleted";
    } else {
      echo "Error deleting table data: " . $conn->error;
    }

    $sql = "TRUNCATE wordLinkTable";
    
    if ($conn->query($sql) === TRUE) {
      echo "Data of table WordLinkTable successfully deleted";
    } else {
      echo "Error deleting table data: " . $conn->error;
    }
}

CloseCon($conn);
?>