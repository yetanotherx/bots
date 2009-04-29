<?php

define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');

$pillar = Pillar::ini_launch('/home/soxred93/configs/afd-remove.cfg');
//$pillar = Pillar::get_instance;
$site = $pillar->cursite;

$template = "Template:REMOVE THIS TEMPLATE WHEN CLOSING THIS AfD";
$p = array();

$pages = $site->get_embeddedin($template,'500',$continue,4);
foreach( $pages as $page ) {
	echo $page."\n";
	if( substr($page,0,36) == 'Wikipedia:Articles for deletion/Log/' ) continue;
	if( substr($page,0,32) != 'Wikipedia:Articles for deletion/' ) continue;
	echo $page."\n";
	$p[] = $page;
}
/*while( isset($pages[499]) ) {
	$pages = $site->get_embeddedin($template,'500',$continue,4);
	foreach( $pages as $page ) {
		echo $page."\n";
		if( substr($page,0,36) == 'Wikipedia:Articles for deletion/Log/' ) continue;
		if( substr($page,0,32) != 'Wikipedia:Articles for deletion/' ) continue;
		echo $page."\n";
		$p[] = $page;
	}
}*/

require_once('/home/soxred93/textdiff/textdiff.php');

$c = 1;

foreach ($p as $pg) {
//	if( $c > 10 ) break;
	$page = new Page($site,$pg);
	$text = $page->get_text();
	
	if( preg_match('/\<!--Template:Afd top/Si', $text) ) {
		$newtext = preg_replace('/\{\{REMOVE THIS TEMPLATE WHEN CLOSING THIS AfD(.*)\}\}/i', '', $text);
		$diff = getTextDiff('unified', $text, $newtext);
		try {
			$page->put($newtext,"Removing categorization template from closed AFD",true);
		} catch (PillarException $e) {
			continue;
		}
		$c++;
	}
}

/*
try {
	$page->put($newtext,"Dating maintenance tags (bot edit)",true);
} catch (PillarException $e) {
	continue;
}
*/

?>