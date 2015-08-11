<?php

  // meteo.php

	require_once('vigilancemeteo.class.php');
	require_once('config.php');
	require_once('tmhOAuth.php');
    
function tweet($message) {
  $tmhOAuth = new tmhOAuth($config);
 
	$tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update'), array(
		'status' => $message //utf8_encode($message)
	));
 
	if ($tmhOAuth->response['code'] == 200) {
	// En cours de dév, afficher les informations retournées :
	  //$tmhOAuth->pr(json_decode($tmhOAuth->response['response']));
		return TRUE;
	} else {
	// En cours de dév, afficher les informations retournées :
	  //$tmhOAuth->pr(htmlentities($tmhOAuth->response['response']));
    print_r($tmhOAuth->response['response']);
		return FALSE;
	}
}

  $tweetLevel = 3; // Niveau à partir duquel on tweet l'alerte
  $niveau_alerte = array(1 => 'vert', 2 => 'jaune', 3 => 'orange', 4 => 'rouge');

  $alea = time();
  $indexURL = "http://vigilance.meteofrance.com/data/NXFR34_LFPW_.xml?$alea";
  $imgURL = "http://vigilance.meteofrance.com/data/QGFR17_LFPW_.gif?$alea";

  $meteo = new vigilancemeteo($indexURL,$imgURL,$niveau_alerte);

  $tab = $meteo->getListByLevel();
  
  $tweeted = false;

  foreach($niveau_alerte as $level => $lib) {
    if (isset($tab[$level]) && count($tab[$level])>0) {
      $msg = "Alerte $lib sur ".implode(", ",$tab[$level]);
      echo $msg . "<br>" . "\n";
      echo $level . " " . $tweetLevel . "<br> \n";
      if ($level >= $tweetLevel) {
        tweet($msg);
        $tweeted = true;
      }
    }
  }

  if (!$tweeted) {
    echo "Aucune alerte de niveau ".$niveau_alerte[$tweetLevel]." ou supérieur actuellement (donc pas de tweet)." . "<br>" . "\n";
  }
  
?>
