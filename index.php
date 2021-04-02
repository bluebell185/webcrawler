<?php
    class Crawler {
        #Seiteninhalt 
        protected $markup = '';
        #URL in base
        public $base = '';
        #Konstruktor
        public function __construct($uri) {
            $this->base = $uri;
            $this->markup = $this->getMarkup($uri);
        }

        public function getMarkup($uri) {
            $markup = null;
            try{
                $markup = file_get_contents($uri);
            } catch (Exception $e) {
                echo 'Fehler ist aufgetreten: ',  $e->getMessage(), "<br>";
            }
            return $markup;         
        }

        public function get($type) {
            #type zB images, wird dann an Methodenname angehängt
            $method = "_get_{$type}";
            if (method_exists($this, $method)){
                #entsprechende Methode wird aufgerufen über die Funktion call_user_func
                return call_user_func(array($this, $method));
            }
        }

        protected function _get_links() {
            if (!empty($this->markup)){
               preg_match_all('/href=\"(.*?)\"/i', $this->markup, $links);
               # löscht .css-Links aus der Liste
               $cleanTags[1] = preg_grep("/(.*)\.css(.*)/", $links[1], PREG_GREP_INVERT);
               # löscht Links mit # aus der Liste
               $links[1] = preg_grep("/(.*)#(.*)/", $cleanTags[1], PREG_GREP_INVERT);
               return !empty($links[1]) ? $links[1] : FALSE;
            }
        }
    }
?>


<html>
    <body>
    <title> Suchmaschine MoKat </title>
        <h2>Suchmaschine MoKat</h2>
        <br>
	<h2> Link hinzufügen </h2>
   
	<form action="index.php" method="post">
	  <label for="link">Link:</label><br>
	  <input type="url" id="link" name="link"><br>
	  <input type="submit" value="Hinzufügen">
	</form> 

    <br>
	<h2> Suche </h2>
	<form action="index.php" method="post">
	  <label for="search">Suche:</label><br>
	  <input type="text" id="search" name="search"><br>
	  <input type="submit" value="Suche">
	</form> 

    
    <div>
        <?php
            include 'database.php';
            # Nutzer soll Errors nicht angezeigt bekommen
            error_reporting(0); 
            # Wenn der Nutzer etwas suchen möchte 
            if ($_POST["search"] != null){    
                search($_POST["search"]);
            }
            else{
                echo "Die Datenbank wird gerade befüllt. Sie können gerne trotzdem nach etwas suchen!";
            }
           
            function search($postParameter){
                $conn = OpenCon();
                if ($conn->connect_errno) 
                        { echo 'Verbindung zur Datenbank fehlgeschlagen!'; } 
                else { 
                $searchString = $postParameter;
                # Sucheingabe wird in einzelne Wörter zerlegt
                preg_match_all('/[äÄüÜöÖßa-zA-Z0-9][äÄüÜöÖßa-zA-Z0-9\-\_]*[äÄüÜöÖßa-zA-Z0-9]/i', $searchString, $words);
            
                $wordid = -1;
                $linkList = array();
            
                    # Wörter durchgehen (jedes Wort der Anfrage)
                    foreach($words as $wordArray){
                        foreach($wordArray as $word){
                            # für jedes Wort:
                            # ist Wort in Datenbank? wenn ja, dann welche ID?
                            $sql = "SELECT * FROM wordTable where word LIKE \"%" . $word . "%\"";
                            if (!$result = $conn->query($sql)) {
                                echo 'Error: '. $conn->error; 
                            } else {
                                if ($result->num_rows === 0){
                                    echo "<br> Wort $word noch nicht in Datenbank!";
                                }
                                else{
                                    # alle Ergebnisse, die durch das LIKE in der Abfrage zurückgegeben werden, durchsuchen
                                    while($row = mysqli_fetch_array($result)){
                                        $wordid = $row['id'];
                
                                        # welche Links sind mit dieser ID verbunden? -> Liste mit Links füllen
                                        $sql = "SELECT * FROM wordLinkTable where wordID = \"" . $wordid . "\"";
                                        if (!$resultWL = $conn->query($sql)) {
                                            echo 'Error: '. $conn->error; 
                                        } else {
                                            # links holen mit linkid
                                            $sqlString = "SELECT * FROM LinkTable where ";
                                            if ($resultWL->num_rows === 0){
                                                # Do nothing
                                            }
                                            else{
                                                $i = 0;
                                                while($rowWL = mysqli_fetch_array($resultWL)){
                                                    $id = $rowWL['linkid'];
                                                    if($i != 0){
                                                        $sqlString.="or ";
                                                    }
                                                    $sqlString.="id = $id ";
                                                    $i++;
                                                }	
                                                #Ausführen sql   
                                                if (!$resultL = $conn->query($sqlString)) {
                                                    echo 'Error: '. $conn->error; 
                                                } else {
                                                    if ($resultL->num_rows === 0){
                                                         # Do nothing
                                                    }
                                                    else{
                                                        while($rowL = mysqli_fetch_array($resultL)){
                                                            # Links in Liste füllen
                                                            $linkList[] = $rowL;
                                                        }
                                                    }
                                                }                   
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    # doppelte Links raus
                    $finalLinkList = array_unique($linkList, SORT_REGULAR);
                    echo "<br>Anzahl Suchergebnisse: " . count($finalLinkList);
                    foreach($finalLinkList as $row){
                        echo "<br><br>" . $row['titel'] . "<br><a href=\"".$row['link']."\">". $row['link']."</a>" ;
                    }
                }
                CloseCon($conn);
            }
            ?>
        </div> 
        
        <div hidden>
            <?php  
                $link = "";
                $result = null;
                
                $conn = OpenCon();
                set_time_limit(1000);
                if ($conn->connect_errno) 
                        { echo 'Verbindung zur Datenbank fehlgeschlagen!'; } 
                else { 
                    if ($_POST["search"] == null){
                        $result = getLinkTable($conn);
                        
                        # Wenn der Nutzer einen Link hinzufügen will
                        if ($_POST["link"] != null){
                            $link = $_POST["link"];
                            recursion(null, $link, $conn);
                        }
                        else{
                            # Wenn die Seite normal aufgerufen/aktualisiert wird
                            recursion($result, "http://www.dhbw-heidenheim.de", $conn);
                        }     
                    }    
                }
                CloseCon($conn);
            ?>
        </div> 
        
    </body>
</html>

<?php
    function recursion($result, $benutzerLink, $conn){
        for ($i = 0; $i<2; $i++){
            # für den allerersten Link bzw den Benutzerlink
            if ($result == null || $result->num_rows === 0){
                $crawl2 = new Crawler($benutzerLink);
                $links2 = $crawl2->get('links');
                $links2[] = $benutzerLink;
                LinkCollector($links2, $crawl2, $conn, null);
            }
            else{
                # für alle weiteren Links in der Tabelle
                $row = mysqli_fetch_array($result);
                $crawl2 = new Crawler($row['link']);
                $links2 = $crawl2->get('links');
                $links2[] = $benutzerLink;
                while($row = mysqli_fetch_array($result)){                        
                    if ($links2 != null){
                        LinkCollector($links2, $crawl2, $conn, $result);
                    }
                    
                    # update timestamp of visited links
                    $sql = "UPDATE linktable SET reg_date= CURRENT_TIMESTAMP WHERE link = \"".$row['link']."\"";
                    if (!$result2 = $conn->query($sql)) {
                        echo 'Error: '. $conn->error; 
                        $conn->rollback();
                    } else {	
                        $conn->commit();
                    }
                }
            }
            $result = getLinkTable($conn);
        }
    }

    function LinkCollector($links, $crawl, $conn, $result){
            $shortenedListLinks = array_slice($links, 0, 100); 
            $link = "";

            foreach($shortenedListLinks as $l) {
                $isInDatabase = false;
                if (substr($l,0,7)!='http://'){
                    if (substr($l,0,8)=='https://')
                        $link = $l;
                    else
                        $link = "$crawl->base" . $l; 
                }
                else{
                    $link = $l;
                }
                echo "<br><br>Link: $link";
                
                # Ist der Link schon in der Datenbank?
                $sql = "SELECT * FROM linkTable where link = \"" .$link . "\"";
                if (!$result = $conn->query($sql)) {
                    echo "Error: " . $conn->error;
                    $isInDatabase = true;
                }
                else{
                    if ($result->num_rows != 0){
                        $isInDatabase = true;
                    } 
                }

                # Falls Link nicht in der Datenbank ist
                if($isInDatabase == false){
                    #Titel finden
                    $str = $crawl->getMarkup($link);
                    $titelLink = "Kein Titel";
                    if(strlen($str)>0)
                    {
                        preg_match("/\<title\>(.*)\<\/title\>/is",$str,$title); 
                        $titelLink = $title[1];
                        if ($titelLink == null || $titelLink == ""){
                            $titelLink = "Kein Titel";
                        }
                        settype($titelLink, "string");
                        $titelLink = substr($titelLink, 0, 200); 
                    }
                    
                    $sql = "INSERT INTO linkTable (link, titel, reg_date) VALUES (\"" . $link . "\", \"". $titelLink. "\", DEFAULT)";
                    if (!$result = $conn->query($sql)) {
                        echo 'Error: '. $conn->error; 
                        $conn->rollback();
                    } else {	
                        echo "Commit";
                        $conn->commit();
                        $result = getLinkTable($conn);
                    }
                }

                wordCollector($link, $crawl->getMarkup($link), $conn);
            }
        }

        function wordCollector($link, $markup, $conn){
            # alle Wörter einer Seite bekommen und in Array speichern
            $text = strip_tags($markup);
            preg_match_all('/[äÄüÜöÖßa-zA-Z0-9][äÄüÜöÖßa-zA-Z0-9\-\_]*[äÄüÜöÖßa-zA-Z0-9]/i', $text, $words);
            
            $wordID = -1;
            $linkID = -1;
             # herausfinden, welche ids wort und link jeweils haben
             $sqlQuery2 = "SELECT * FROM linkTable where link = (\"" . $link . "\")";
             if (!$result = $conn->query($sqlQuery2)) {
                echo 'Error: '. $conn->error; 
             }
             else{
                 $row = mysqli_fetch_array($result);
                 $linkID = $row['id'];  
             }

            foreach ($words as $wordArray){
                # Für die Entwicklung wurde die Anzahl der Worte einer Seite stark eingeshränkt, um die Funktionsfähigkeit zu testen
                # Dies wurde nun auskommentiert: 
                # $wordArray = array_slice($wordArray2, 0, 100); 
                foreach($wordArray as $word){
                    # Ist Wort in Worttabelle?
                    $sqlQuery = "SELECT * FROM wordTable where word = \"" . $word . "\"";
                    if (!$result = $conn->query($sqlQuery)) {
                        echo 'Error: '. $conn->error; 
                    } else {
                        if ($result->num_rows === 0){	
                            # Fülle Wort in Worttabelle
                            $sql = "INSERT INTO wordTable (word) VALUES (\"" . $word . "\")";
                                if (!$result = $conn->query($sql)) {
                                    echo 'Error: '. $conn->error; 
                                    $conn->rollback();
                                } else {	
                                    echo "Commit";
                                    $conn->commit();
                                } 
                        }   

                        if ($linkID != -1){
                            # Erfrage wordid aus Tabelle
                            $sqlQueryWord = "SELECT * FROM wordTable where word = (\"" . $word . "\")";
                            if (!$resultWord = $conn->query($sqlQueryWord)) {
                                echo "Error " . $conn->error;
                            }
                            else{
                                if ($resultWord->num_rows === 0){
                                    echo "Das Wort befindet sich nicht in der Datenbank!";
                                }
                                else{
                                    $row = mysqli_fetch_array($resultWord);	
                                    $wordID = $row['id']; 
                                }
                            }
                        }
                    }

                    if ($linkID != -1){
                    # Wort und Link in WortLinkTabelle specihern, es sei denn ist schon drin
                    $sqlQuery = "SELECT * FROM wordLinkTable where wordid = (\"" . $wordID . "\") and linkid = (\"" . $linkID . "\")";
                        if (!$result = $conn->query($sqlQuery)) {
                            echo 'Error: '. $conn->error; 
                        } else {	
                            if ($result->num_rows === 0){
                                $sql = "INSERT INTO wordLinkTable (linkid, wordid) VALUES (\"" . $linkID . "\", \"" . $wordID . "\")";
                                if (!$result = $conn->query($sql)) {
                                    echo 'Error: '. $conn->error; 
                                    $conn->rollback();
                                } else {	
                                    echo "Commit";
                                    $conn->commit();
                                }
                            }	  
                        }
                    }
                }
            }
        }
    ?>