<?php

define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');

$pillar = Pillar::ini_launch('/home/soxred93/configs/uncat-remove.cfg');
$site = $pillar->cursite;

$template = "Template:Uncategorized";
$p = array();

$pages = $site->get_embeddedin($template,'500',$continue,0);
foreach( $pages as $page ) {
	$p[] = $page;
}
while( isset($pages[499]) ) {
	$pages = $site->get_embeddedin($template,'500',$continue,0);
	foreach( $pages as $page ) {
		$p[] = $page;
	}
}

require_once('/home/soxred93/textdiff/textdiff.php');

$c = 1;

$tofind = array(
	'Classify',
	'CatNeeded',
	'Uncategorised',
	'Uncat',
	'Categorize',
	'Categories needed',
	'Categoryneeded',
	'Category needed',
	'Category requested',
	'Categories requested',
	'Nocats',
	'Categorise',
	'Nocat',
	'Uncat-date',
	'Uncategorized-date',
	'Needs cat',
	'Needs cats',
	'Cat needed',
	'Cats needed',
);

foreach ($p as $pg) {
	if( $c > 30 ) break;
	try {
		$page = new Page($site,$pg);
	} catch (PillarException $e) {
		continue;
	}
	$text = $page->get_text();
	preg_match_all('/\[\[Category:(.*?)(\|(.*?))?\]\]/Si', $text, $cats);
	if( $cats ) {
		$cats = $cats[1];
		$remove = 'no';
		foreach( $cats as $cat ) {
			$vars = array(
				'action' => 'query',
				'prop'   => 'info',
				'titles' => 'Category:'.$cat,
			);
			
			$request = new MWRequest($site,$vars);
			$result = $request->get_result();
			if( !isset($result['query']['pages']['-1']['missing']) ) {
				$remove = 'yes';
				break;
			}
		}
		if( $remove == 'yes' ) {
			$newtext = preg_replace('/\{\{('.implode('|',$tofind).')(.*?)\}\}/i', '', $text);
			$diff = getTextDiff('unified', $text, $newtext);
			echo $diff;
			try {
				$page->put($newtext,"Removing categorization template",true);
			} catch (PillarException $e) {
				continue;
			}
			$c++;
		}
	}
}

?>