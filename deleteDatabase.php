
<html>
<head> 
 <title> Tabellen löschen </title> 
</head>
<body>
</body>
</html>





<?php
include 'database.php';

$conn = OpenCon();
if ($conn->connect_errno) 
    {echo 'Error: '. $conn->error;  } 
else { 
    $sql = "TRUNCATE linkTable";
    
    if ($conn->query($sql) === TRUE) {
      echo "Data of table LinkTable successfully deleted<br>";
    } else {
      echo "<br>Error deleting table data: <br>" . $conn->error;
    }

    $sql = "TRUNCATE wordTable";
    
    if ($conn->query($sql) === TRUE) {
      echo "<br>Data of table WordTable successfully deleted<br>";
    } else {
      echo "<br>Error deleting table data:<br> " . $conn->error;
    }

    $sql = "TRUNCATE wordLinkTable";
    
    if ($conn->query($sql) === TRUE) {
      echo "<br>Data of table WordLinkTable successfully deleted<br>";
    } else {
      echo "<br>Error deleting table data:<br> " . $conn->error;
    }
}

CloseCon($conn);
?>