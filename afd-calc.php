<?php

define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');
require_once('/home/soxred93/svnbots/wikibot.classes.php');
require_once('/home/soxred93/database.inc');

$http = new http;

$pillar = Pillar::ini_launch('/home/soxred93/configs/afd-calc.cfg');
//$pillar = Pillar::get_instance;
$site = $pillar->cursite;

mysql_connect('enwiki-p.db.ts.wikimedia.org',$toolserver_username,$toolserver_password);
@mysql_select_db('enwiki_p') or print mysql_error();

$u = array();
$users = $site->get_embeddedin('Template:REMOVE THIS TEMPLATE WHEN CLOSING THIS AfD','500',$continue,4);

foreach( $users as $user ) {
	if( substr($user,0,36) == 'Wikipedia:Articles for deletion/Log/' ) continue;
	if( substr($user,0,32) != 'Wikipedia:Articles for deletion/' ) continue;
	$u[] = $user;
}
while( isset($users[499]) ) {
	$users = $site->get_embeddedin('Template:REMOVE THIS TEMPLATE WHEN CLOSING THIS AfD','500',$continue,4);
	foreach( $users as $user ) {
		if( substr($user,0,36) == 'Wikipedia:Articles for deletion/Log/' ) continue;
		if( substr($user,0,32) != 'Wikipedia:Articles for deletion/' ) continue;
		$u[] = $user;
	}
}

//$u = array('Wikipedia:Articles for deletion/IrishJack');

$afds = array();
foreach ($u as $name) {
	echo $name;
    $rawuser = $name;
    
	$page = new Page($pillar->cursite,$rawuser);
	$text = $page->get_lastedit();
	if( strtotime($text) < strtotime('-2 weeks') ) {
		echo "WAY overdue...";
		continue;
	}
	
	$x = $http->get('http://en.wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=timestamp&rvdir=newer&format=php&titles='.urlencode($rawuser));
	$x = unserialize($x);
	$x = array_shift($x['query']['pages']);
	$x = $x['revisions']['0']['timestamp'];
	$nomdate = date('Y F d', strtotime($x));
	
	$afds[$nomdate][] = $rawuser;
	
	/*$toedit = "Template:Adminstats/$rawuser\n";
    echo "Editing $toedit";
    $page = new Page($pillar->cursite,$toedit);
    $page->put($out,"Updating Admin Stats",true);*/
}

print_r($afds);

$out = "{| class=\"wikitable\"\n|-\n";
foreach($afds as $nomdate => $afd) {
	$out .= "!  Nominated $nomdate\n|-\n";
	foreach($afd as $a) {
		$name = substr($a,32);
		$out .= "|  [[Wikipedia:Articles for deletion/$name|$name]]\n|-\n";
	}
}
$out .= '|}';

echo $out;


?>