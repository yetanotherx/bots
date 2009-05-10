<?php
echo date('r');
define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');
require_once('/home/soxred93/wikibot.classes.php');
$http = new http;

$pillar = Pillar::ini_launch('/home/soxred93/configs/afd-calc.cfg');
//$pillar = Pillar::get_instance;
$site = $pillar->cursite;

$u = array();
$users = $site->get_embeddedin('Template:REMOVE THIS TEMPLATE WHEN CLOSING THIS AfD','500',$continue,4);

foreach( $users as $user ) {
	if( substr($user,0,36) == 'Wikipedia:Articles for deletion/Log/' ) continue;
	if( substr($user,0,32) != 'Wikipedia:Articles for deletion/' ) continue;
	$u[] = $user;
}
while( isset($users[499]) ) {
	try {
		$users = $site->get_embeddedin('Template:REMOVE THIS TEMPLATE WHEN CLOSING THIS AfD','500',$continue,4);
	} catch (PillarException $e) {
		break;
	}
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
    
    try {
		$page = new Page($pillar->cursite,$rawuser);
	} catch (PillarException $e) {
		continue;
	}
	$time = $page->get_lastedit();
	$text = $page->get_text();
	if( strtotime($time) < strtotime('-2 weeks') ) {
		echo "WAY overdue...";
		continue;
	}
	
	if( preg_match( '/(\d{2}):(\d{2}), (\d{2}) (\w*?) (\d{4}) \(UTC\)/S', $text, $date ) ) {
		
		$nomdate = date('Y F d', strtotime("{$date[3]} {$date[4]} {$date[5]} {$date[1]}:{$date[2]}:00"));
		
	}

	$afds[$nomdate][] = $rawuser;
	
}

print_r($afds);

$out = "{| class=\"wikitable collapsible collapsed\"\n|-\n";
foreach($afds as $nomdate => $afd) {
	$out .= "!  Nominated $nomdate (".count($afd).")\n|-\n";
	foreach($afd as $a) {
		$name = substr($a,32);
		$out .= "|  [[Wikipedia:Articles for deletion/$name|$name]]\n|-\n";
	}
}
$out .= '|}';

echo $out;

try {
	$page = new Page($pillar->cursite,'User:X!/AFD report');
	$page->put($out,"Updating AFD table",true);
} catch (PillarException $e) {
	try {
		$page = new Page($pillar->cursite,'User:X!/AFD report');
		$page->put($out,"Updating AFD table",true);
	} catch (PillarException $e) {
		continue;
	}
}
echo date('r');

?>