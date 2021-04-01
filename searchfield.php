<html>

  <head>

     <title>Search</title>

  </head>

  <body>
     <br>
	<h2> Link hinzufügen </h2>
	<form action="searchfield.php" method="post">
	  <label for="link">Link:</label><br>
	  <input type="text" id="link" name="link"><br>
	  <input type="submit" value="Hinzufügen">
	</form> 
    <br>
	<h2> Suche </h2>
	<form action="searchfield.php" method="post">
	  <label for="search">Search:</label><br>
	  <input type="text" id="search" name="search"><br>
	  <input type="submit" value="Search">
	</form> 

    <?php 
    
    include 'database.php';
  #TODO prüfen, ob wirklich was drinsteht in der Suche
    if ($_POST["search"] != null){
        $searchString= $_POST["search"];
        echo "<br>SearchString: ".$searchString;
        #TODO regex wie in index.php
      
        preg_match_all('/[a-zA-Z0-9][a-zA-Z0-9\-\_]*[a-zA-Z0-9]/i', $searchString, $words);
    
        $wordid = -1;
        $linkList = array();
    
        $conn = OpenCon();
        if ($conn->connect_errno) 
                { echo 'Failed to connect to database!'; } 
        else { 
            # words durchgehen (jedes Wort der Anfrage)
            foreach($words as $wordArray){
                foreach($wordArray as $word){
                    echo "<br>Einzelnes Wort: ".$word;
                # für jedes Wort:
                    # ist Wort in Datenbank? wenn ja, dann welche ID?
                    $sql = "SELECT * FROM wordTable where word LIKE \"%" . $word . "%\"";
                    if (!$result = $conn->query($sql)) {
                        echo 'Fail'; 
                    } else {
                        if ($result->num_rows === 0){
                            echo "Wort $word nicht in Datenbank!";
                        }
                        else{
                            $row = mysqli_fetch_array($result);	
                            #TODO durch das like kommen vermutlich teilweise mehrere Worte mit! anpassen!
                            $wordid = $row['id'];
                            echo "<br>WordID: ". $wordid;
    
                            # welche Links sind mit dieser ID verbunden? -> Liste mit Links füllen
                            $sql = "SELECT * FROM wordLinkTable where wordID = \"" . $wordid . "\"";
                            if (!$result = $conn->query($sql)) {
                                echo 'Fail'; 
                            } else {
                                 # links holen mit linkid
                                $sqlString = "SELECT * FROM LinkTable where ";
                                if ($result->num_rows === 0){
                                    echo "Wort-Link nicht in Datenbank!";
                                }
                                else{
                                    $i = 0;
                                    while($row = mysqli_fetch_array($result)){
                                        $id = $row['linkid'];
                                        if($i != 0){
                                            $sqlString.="or ";
                                        }
                                        $sqlString.="id = $id ";
                                        $i++;
                                    }	
                                    echo "<br>SQL-String: ". $sqlString;
                                    #Ausführen sql   
                                    if (!$result = $conn->query($sqlString)) {
                                        echo 'Fail'; 
                                    } else {
                                        if ($result->num_rows === 0){
                                            echo "Wort-Link nicht in Datenbank!";
                                        }
                                        else{
                                            while($row = mysqli_fetch_array($result)){
                                                # Links in Liste füllen
                                                $linkList[] = $row;
                                            }
                                        }
                                    }                   
                                }
                               
                            }
                        }
                    }
                    
                }
            }
            #TODO doppelte raus
            #TODO and erzwingen quasi (also dhbw heidenheim nur seiten, die beide Wörter beinhaltet zB)
            echo "<br>Anzahl Suchergebnisse: " . count($linkList);
            foreach($linkList as $row){
                echo "<br><br>" . $row['titel'] . "<br><a href=\"".$row['link']."\">". $row['link']."</a>" ;
            }
        }
    

    }

    if ($_POST["link"] != null){
        include 'index.php';
        #TODO schauen, ob link wirklich ein link ist
        recursion(null, $_POST["link"], $conn);
    }
    

    CloseCon($conn);
    
   

   

   
   
    # Links am schluss ausgeben
	
	// $mysqli = OpenCon();
	// if ($mysqli->connect_errno) { echo 'Error'; } 
	// else { 
	// 	$sql = "SELECT user.lastname, user.age, car.brand FROM user LEFT JOIN car on car.id = user.id_car";
	// 	if (!$result = $mysqli->query($sql)) {
	// 		echo 'Error result';
	// 	} else {			
	// 			echo "<table border='1'>
	// 			<tr>
	// 			<th>User Name</th>
	// 			<th>User Age</th>
	// 			<th>Car Brand</th>
	// 			</tr>";

	// 			while($row = mysqli_fetch_array($result))
	// 			{
	// 			echo "<tr>";
	// 			echo "<td>" . $row['lastname'] . "</td>";
	// 			echo "<td>" . $row['age'] . "</td>";
	// 			echo "<td>" . $row['brand'] . "</td>";
	// 			echo "</tr>";
	// 			}
	// 			echo "</table>";
	
	// 	}
	// 	CloseCon($mysqli);
	// }

	// ?>
	<!-- <br>
	// <h2> Add User </h2>
	// <form action="add_user.php" method="post">
	//   <label for="fname">First name:</label><br>
	//   <input type="text" id="fname" name="fname"><br>
	//   <label for="lname">Last name:</label><br>
	//   <input type="text" id="lname" name="lname"><br>
	//   <label for="age">Age:</label><br>
	//   <input type="number" id="age" name="age"><br>
	//   <input type="submit" value="Add">
	// </form> 
	
	// <br>
	// <h2> Delete User </h2>
	// <form action="delete_user.php" method="post">
	//   <label for="id">User ID:</label><br>
	//   <input type="text" id="id" name="id"><br>
	//   <input type="submit" value="Delete">
	// </form> 
	
	// <br>
	// <h2> User-List </h2>

	// $mysqli = OpenCon();
	// if ($mysqli->connect_errno) { echo 'Error'; } 
	// else { 
	// 	$sql = "SELECT * FROM user";
	// 	if (!$result = $mysqli->query($sql)) {
	// 		echo 'Error result';
	// 	} else {			
	// 			echo "<table border='1'>
	// 			<tr>
	// 			<th>ID</th>
	// 			<th>Firstname</th>
	// 			<th>Lastname</th>
	// 			<th>Age</th>
	// 			</tr>";

	// 			while($row = mysqli_fetch_array($result))
	// 			{
	// 			echo "<tr>";
	// 			echo "<td>" . $row['id'] . "</td>";
	// 			echo "<td>" . $row['firstname'] . "</td>";
	// 			echo "<td>" . $row['lastname'] . "</td>";
	// 			echo "<td>" . $row['age'] . "</td>";
	// 			echo "</tr>";
	// 			}
	// 			echo "</table>";
	
	// 	}
	// 	CloseCon($mysqli);
	// }

	?>
	
  </body>

</html>
