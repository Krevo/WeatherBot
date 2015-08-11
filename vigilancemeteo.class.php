<?php

  // vigilancemeteo_class.php
  
class vigilanceMeteo {

  public $xml;
  public $img;
  public $lastUrl;

  public $niveau_alerte;
    
  function curl_redirect_exec($ch, &$redirects, $curlopt_header = false) {
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302) {
        list($header) = explode("\r\n\r\n", $data, 2);
        $matches = array();
        preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
        $url = trim(array_pop($matches));
        $url_parsed = parse_url($url);
        if (isset($url_parsed)) {
            curl_setopt($ch, CURLOPT_URL, $url);
            $redirects++;
            return $this->curl_redirect_exec($ch, $redirects);
        }
    }
    if ($curlopt_header)
        return $data;
    else {
        list(,$body) = explode("\r\n\r\n", $data, 2);
        return $body;
    }
  }

  function getUrl($url) {
  
   $this->lastUrl = $url;
   //echo "GET $url \n";   
   // Faire un get mais en fournissant les cookies de session et tout ...
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_HTTPGET, 1 );
   //curl_setopt($ch, CURLOPT_VERBOSE, 1);
   curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);  // pour envoi des cookies
   curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file); // pour reception des cookies
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);   
   curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; fr; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1");
   //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $redirect = 0;
   $getResult = $this->curl_redirect_exec($ch,$redirect);
   //echo "$redirect redirection ! \n";
   
   if (curl_errno($ch)) {
       print curl_error($ch);
   }
   curl_close($ch);
   return $getResult;
  
  }

  public function __construct($indexUrl,$imgUrl,$niveauxAlerte) {
    $this->niveau_alerte = $niveauxAlerte;
    $this->cookie_file = "./cookies.txt";
    $this->xml = simplexml_load_string($this->getUrl($indexUrl));
  }

  public function getListByLevel() {
  
    $xml = $this->xml;

    foreach($this->niveau_alerte as $level => $lib) {
      $result = $xml->xpath("//datavigilance[@couleur='$level']");
      foreach($result as $item) {
	if (strlen(''.$item['dep'])==2) { // dept en XX <=> alerte sur un département
          $this->tab[$level][] = ''.$item['dep'];
        } else { // dept en XX10 <=> alerte sur les cotes du département
	  // remplir ici le tableau pour les crues
        }
      }
    }

    return $this->tab;
  }

}

?>
