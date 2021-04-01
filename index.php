<?php
    class Crawler {
        #Seiteninhalt ???
        protected $markup = '';
        #URL in base
        public $base = '';
        #Konstruktor
        public function __construct($uri) {
            print($uri);
            $this->base = $uri;
            $this->markup = $this->getMarkup($uri);
           # print($this->markup);
        }

        public function getMarkup($uri) {
            echo $uri;
            return file_get_contents($uri);         
        }

        public function get($type) {
            #type zB images, wird dann an Methodenname angehängt
            $method = "_get_{$type}";
            if (method_exists($this, $method)){
                #entsprechende Methode wird aufgerufenüber die Funktion call_user_func
                return call_user_func(array($this, $method));
            }
        }

        // protected function _get_images() {
        //     if (!empty($this->markup)){
        //         preg_match_all('/<img([^>]+)\/>/i', $this->markup, $images);
        //         return !empty($images[1]) ? $images[1] : FALSE;
        //     }
        // }

        protected function _get_links() {
            #TODO # filtern (stehen allein in Zeile als Link)
            if (!empty($this->markup)){
                echo "Get links";
                //preg_match_all('/<a([^>]+)\>(.*?)\<\/a\>/i', $this->markup, $links);
               preg_match_all('/href=\"(.*?)\"/i', $this->markup, $links);
               #löscht .css-Links aus der Liste
               $cleanTags[1] = preg_grep("/(.*)\.css(.*)/", $links[1], PREG_GREP_INVERT);
            
            
               #^((?!.css).)*$
                #(.*)\.css(.*)
                return !empty($cleanTags[1]) ? $cleanTags[1] : FALSE;
            }
        }
    }
?>


<html>
    <body>
        <h2>Webcrawler</h2>
        <?php
            include 'database.php';
            
            $link = "";
            $result = null;
            $conn = OpenCon();
            set_time_limit(1000);
            if ($conn->connect_errno) 
                    { echo 'Failed to connect to database!'; } 
            else { 
                // safeLinks($links, $crawl, $conn, null);
                // $result = getLinkTable($conn);
                $result = getLinkTable($conn);
                recursion($result, "http://www.dhbw-heidenheim.de", $conn);
            #Es wird in 2 Ebenen durchlaufen
            

            
             }
            CloseCon($conn);
        ?>
    </body>
</html>

<?php
# http://talkerscode.com/webtricks/create-simple-web-crawler-using-php-and-mysql.php
    function recursion($result, $benutzerLink, $conn){
        echo "Benutzerlink: ".$benutzerLink;
        for ($i = 0; $i<2; $i++){
            echo "Test1";
            # für den allerersten Link bzw den Benutzerlink
            if ($result == null || $result->num_rows === 0){
                echo "TEst2 - " . $benutzerLink;
                $crawl2 = new Crawler($benutzerLink);
                $links2 = $crawl2->get('links');
                #TODO evtl überflüssig?
                #$crawl = new Crawler('http://www.dhbw-heidenheim.de');
                #$images = $crawl->get('images');
                safeLinks($links2, $crawl2, $conn, null);
            }
            else{
                # für alle weiteren Links in der Tabelle
                $row = mysqli_fetch_array($result);
                $crawl2 = new Crawler($row['link']);
                $links2 = $crawl2->get('links');
                while($row = mysqli_fetch_array($result)){                        
                    if ($links2 != null){
                        safeLinks($links2, $crawl2, $conn, $result);
                    }
                    
                    $sql = "UPDATE linktable SET reg_date= CURRENT_TIMESTAMP WHERE link = \"".$row['link']."\"";
                    if (!$result2 = $conn->query($sql)) {
                        echo "Update Rollback";
                        $conn->rollback();
                    } else {	
                        echo "Update Commit";
                        $conn->commit();
                    }
                    
                }
            }
            $result = getLinkTable($conn);
            // echo "In For-Schleife! " . $result;
            // while($row = mysqli_fetch_array($result)){                
            //     $crawl2 = new Crawler($row['link']);
            //     $links2 = $crawl2->get('links');

            //     if ($links2 != null){
            //         safeLinks($links2, $crawl, $conn, $result);
            //     }
                
            //     $sql = "UPDATE linktable SET reg_date= CURRENT_TIMESTAMP WHERE link = \"".$row['link']."\"";
            //     if (!$result2 = $conn->query($sql)) {
            //         $conn->rollback();
            //     } else {	
            //         $conn->commit();
            //     }
                
            // }
            // $result = getLinkTable($conn);
        }
    }

    
#TODO umbenennen in LinkCollector
    function safeLinks($links, $crawl, $conn, $result){
            #TODO heir wird die Liste der Links gekürzt -> Länge noch festlegen
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
                
                $sql = "SELECT * FROM linkTable where link = \"" .$link . "\"";
                if (!$result = $conn->query($sql)) {
                    echo "Error: " . $conn->error;;
                    $isInDatabase = true;
                }
                else{
                    if ($result->num_rows != 0){
                        echo "!=0";
                        $isInDatabase = true;
                    } 
                }
                # result vom Typ mysqli_result? ODER DAtenbankabfrage?
                // while($row = mysqli_fetch_array($result)){
                //     #TODO hier später evtl Aufruf der id des Links 
                //     if($row['link'] == $link){
                //         $isInDatabase = true;
                //         break;
                //     }
                // }
                // mysqli_data_seek($result, 0);

                if($isInDatabase == false){
                    #Titel finden
                    $str = $crawl->getMarkup($link);
                    $titelLink = "Kein Titel";
                    if(strlen($str)>0)
                    {
                        #$str2 = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
                        preg_match("/\<title\>(.*)\<\/title\>/is",$str,$title); // ignore case
                       # echo "<br> Titel 0: ".$title[0];
                        $titelLink = $title[1];
                        if ($titelLink == null || $titelLink == ""){
                            $titelLink = "Kein Titel";
                        }
                        settype($titelLink, "string");
                        $titelLink = substr($titelLink, 0, 200); 
                        #echo "<br> Titel 1:  $titelLink<br><br>";
                    }
                    
                    $sql = "INSERT INTO linkTable (link, titel, reg_date) VALUES (\"" . $link . "\", \"". $titelLink. "\", DEFAULT)";
                    if (!$result = $conn->query($sql)) {
                        echo "Fail";
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
            $text = strip_tags($markup);
            #TODO evtl regex anders? -> bei Umlauten wird getrennt! irgendwie verbessern! und trennen bei Bindestrich bitte auch einführen
            preg_match_all('/[a-zA-Z0-9][a-zA-Z0-9\-\_]*[a-zA-Z0-9]/i', $text, $words);
            
            $wordID = -1;
            $linkID = -1;
             # herausfinden, welche ids wort und link jeweils haben
             $sqlQuery2 = "SELECT * FROM linkTable where link = (\"" . $link . "\")";
             if (!$result = $conn->query($sqlQuery2)) {
                 echo "Error";
             }
             else{
                 $row = mysqli_fetch_array($result);	
                 #echo "<br> LinkID: " . $row['id'];
                 $linkID = $row['id'];  
             }
             #TODO was tun, wenn LinkId nicht geklappt hat?

            foreach ($words as $wordArray2){
                $wordArray = array_slice($wordArray2, 0, 100); 
                foreach($wordArray as $word){
                    #TODO optimieren
                    settype($word, "string");
                    #echo "<br> Word: " . $word;
                     # Ist Wort in Worttabelle?
                    $sqlQuery = "SELECT * FROM wordTable where word = \"" . $word . "\"";
                    if (!$result = $conn->query($sqlQuery)) {
                        echo 'Fail'; 
                    } else {
                        if ($result->num_rows === 0){	
                            # Fülle Wort in Worttabelle
                            $sql = "INSERT INTO wordTable (word) VALUES (\"" . $word . "\")";
                                if (!$result = $conn->query($sql)) {
                                    echo "Fail";
                                    $conn->rollback();
                                } else {	
                                    echo "Commit";
                                    $conn->commit();
                                } 
                        }   
                        else{
                            echo "Existiert schon";
                        }   

                        # Erfrage wordid aus Tabelle
                        $sqlQueryWord = "SELECT * FROM wordTable where word = (\"" . $word . "\")";
                        if (!$resultWord = $conn->query($sqlQueryWord)) {
                            echo "Error " . $conn->error;
                        }
                        else{
                            if ($resultWord->num_rows === 0){
                                echo "Echt jetzt???";
                            }
                            else{
                                $row = mysqli_fetch_array($resultWord);	
                                #echo "<br> WordID: " . $row['id'];
                                $wordID = $row['id']; 
                            }
                        }
                    }
                   
                    
                   


                    # Wort und Link in WortLinkTabelle specihern, es sei denn ist schon drin
                    #TODO evtl optimieren (zuviele DB-Zugriffe?)
                    $sqlQuery = "SELECT * FROM wordLinkTable where wordid = (\"" . $wordID . "\") and linkid = (\"" . $linkID . "\")";
                        if (!$result = $conn->query($sqlQuery)) {
                            echo "ups";
                        } else {	
                            if ($result->num_rows === 0){
                                $sql = "INSERT INTO wordLinkTable (linkid, wordid) VALUES (\"" . $linkID . "\", \"" . $wordID . "\")";
                                if (!$result = $conn->query($sql)) {
                                    echo "Fail";
                                    $conn->rollback();
                                } else {	
                                    echo "Commit";
                                    $conn->commit();
                                }
                            }	
                            else{
                                #echo "ist schon drin ";  
                            }    
                        }
                    }
            }
        }
    ?>