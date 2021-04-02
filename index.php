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
    <head>
    <title>Page Title</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    /* Style the body */
    body {
    font-family: Arial;
    margin: 0;
    }

    /* Header/Logo Title */
    .header {
    padding: 60px;
    text-align: center;
    background: #1abc9c;
    color: white;
    font-size: 30px;
    }

    /* Page Content */
    .content {display:flex; 
    flex-direction: row;
    justify-content: space-around; 

    }
    </style>
    </head>

    <body>
    <div class="header">
    <title> Suchmaschine MoKat </title>
        <h1>Suchmaschine MoKat</h1>
        </div>

     <div class="content">
	
    <div>
	<h4> Suche </h4>
	<form action="index.php" method="post">
	  <label for="search">Suchbegriff:</label><br>
	  <input type="text" id="search" name="search"><br>
	  <input type="submit" value="Los Gehts!">
	</form> </div> 
   
   
	<div> 
    <h4> Link hinzufügen </h4>
    <form action="index.php" method="post">
	  <label for="link">Link:</label><br>
	  <input type="url" id="link" name="link"><br>
	  <input type="submit" value="Hinzufügen">
	</form> </div>
</div>

    <div style= "text-align: center;">
    
        <?php
            include 'database.php';
            # Nutzer soll Errors nicht angezeigt bekommen
            error_reporting(0); 
            # Wenn der Nutzer etwas suchen möchte 
            if ($_POST["search"] != null){    
                search($_POST["search"]);
            }
            else{
                echo "<br>Die Datenbank wird gerade befüllt. Sie können gerne trotzdem nach etwas suchen!<br>";
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
            #Stoppwortliste auch großgeschriebene Worte: 
            $stoppWords = array("and", "the", "of", "to", "einer", "eine", "eines", "einem", "einen", "der",
            "die", "das", "dass", "daß", "du", "er", "sie", "es", "was", "wer", "wie",
            "wir", "und", "oder", "ohne", "mit", "am", "im", "in", "aus", "auf", "ist",
            "sein", "war", "wird", "ihr", "ihre", "ihres", "ihnen", "ihrer", "als", "für",
            "von", "mit", "dich", "dir", "mich", "mir", "mein", "sein", "kein", "durch",
            "wegen", "wird", "sich", "bei", "beim", "noch", "den", "dem", "zu", "zur",
            "zum", "auf", "ein", "auch", "werden", "an", "des", "sein", "sind", "vor",
            "nicht", "sehr", "um", "unsere", "ohne", "so", "da", "nur", "diese", "dieser",
            "diesem", "dieses", "nach", "über", "mehr", "hat", "bis", "uns", "unser",
            "unserer", "unserem", "unseres", "euch", "euers", "euer", "eurem", "ihr",
            "ihres", "ihrer", "ihrem", "alle", "vom",
            
            "And", "The", "Of", "To", "Einer", "Eine", "Eines", "Einem", "Einen", "Der",
            "Die", "Das", "Dass", "Daß", "Du", "Er", "Sie", "Es", "Was", "Wer", "Wie",
            "Wir", "Und", "Oder", "Ohne", "Mit", "Am", "Im", "In", "Aus", "Auf", "Ist",
            "Sein", "War", "Wird", "Ihr", "Ihre", "Ihres", "Ihnen", "Ihrer", "Als", "Für",
            "Von", "Mit", "Dich", "Dir", "Mich", "Mir", "Mein", "Sein", "Kein", "Durch",
            "Wegen", "Wird", "Sich", "Bei", "Beim", "Noch", "Den", "Dem", "Zu", "Zur",
            "Zum", "Auf", "Ein", "Auch", "Werden", "An", "Des", "Sein", "Sind", "Vor",
            "Nicht", "Sehr", "Um", "Unsere", "Ohne", "So", "Da", "Nur", "Diese", "Dieser",
            "Diesem", "Dieses", "Nach", "Über", "Mehr", "Hat", "Bis", "Uns", "Unser",
            "Unserer", "Unserem", "Unseres", "Euch", "Euers", "Euer", "Eurem", "Ihr",
            "Ihres", "Ihrer", "Ihrem", "Alle", "Vom");

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
                    #Falls Wort kein Stoppwort
                    if (!in_array($word, $stoppWords)){
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
        }
    ?>