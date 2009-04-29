<?php

include '/home/soxred93/wikibot.classes.php';


$wpapi = new wikipediaapi;
$wpq = new wikipediaquery;
$wpi = new wikipediaindex;
$http = new http;

$text = $wpq->getpage('User:X!/globaluserpage');

$wikis = array('ar.wikipedia', 'de.wikipedia', 'dv.wikipedia', 'es.wikipedia', 'fi.wikipedia', 'it.wikipedia', 'no.wikipedia', 'pt.wikipedia', 'ru.wikipedia', 'species.wikipedia', 'test.wikipedia');

foreach ($wikis as $wiki) {
	$wpi->indexurl = 'http://'.$wiki.'.org/w/index.php';
	$wpapi->apiurl = 'http://'.$wiki.'.org/w/api.php';
	$wpq->queryurl = 'http://'.$wiki.'.org/w/query.php';
	
	$wpapi->login('User:X!', file_get_contents('/home/soxred93/.pass2'));

	$wpi->forcepost('User:X!', $text, 'Posting userpage from [[m:en:User:X!/globaluserpage|global userpage]]');

	$http->post('http://'.$wiki.'.org/w/api.php?action=logout', array());
	
	sleep(10);
}

?>