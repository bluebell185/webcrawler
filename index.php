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
            foreach($links as $l) {
                if (substr($l,0,7)!='http://')
                echo "<br>Link: $crawl->base/$l";
            }
        ?>
    </body>
</html>