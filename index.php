<?php
    class Crawler {
        #Seiteninhalt ???
        protected $markup = '';
        #URL in base
        public $base = '';
        #Konstruktor
        public function __construct($uri) {
            $this->base = $uri;
            $this->markup = $this->getMarkup($uri);
           # print($this->markup);
        }

        public function getMarkup($uri) {
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
            if (!empty($this->markup)){
                //preg_match_all('/<a([^>]+)\>(.*?)\<\/a\>/i', $this->markup, $links);
                preg_match_all('/href=\"(.*?)\"/i', $this->markup, $links);
                return !empty($links[1]) ? $links[1] : FALSE;
            }
        }
    }

    $crawl = new Crawler('http://www.dhbw-heidenheim.de');
    #$images = $crawl->get('images');
    $links = $crawl->get('links');
?>


<html>
    <body>
        <h2>Webcrawler</h2>
        <?php
            include 'database.php';
            $result = null;
            $conn = OpenCon();
            if ($conn->connect_errno) 
                    { echo 'Failed to load data into database!'; } 
            else { 
                $result = getLinkTable($conn);
            }
            foreach($links as $l) {
                $isInDatabase = false;
                if (substr($l,0,7)!='http://'){
                    if (substr($l,0,8)=='https://')
                        echo "<br>Link: $l";
                    else
                        echo "<br>Link: $crawl->base/$l";
                }
                

                while($row = mysqli_fetch_array($result)){
                    if ($row['link'] == "$crawl->base/$l" || $row['link'] == $l){
                        $isInDatabase = true;
                        break;
                    }
                }
                if($isInDatabase == false){
                    $sql = "INSERT INTO linkTable (link, reg_date) VALUES (\"" . "$crawl->base/$l" . "\", DEFAULT)";
                    if (!$result = $conn->query($sql)) {
                        $conn->rollback();
                    } else {	
                        $conn->commit();
                        $result = getLinkTable($conn);
                    }
                }
				
            }
            CloseCon($conn);
        ?>
    </body>
</html>