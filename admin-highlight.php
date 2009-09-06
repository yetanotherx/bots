<?php

ini_set('memory_limit','16M');

define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');
require_once('/home/soxred93/textdiff/textdiff.php');

$pillar = Pillar::ini_launch('/home/soxred93/configs/admin-highlight.cfg');
$site = $pillar->cursite;

$enablepage = "User:SoxBot/Run/AR";
try {
	$run = new Page($site,$enablepage);
	$run = $run->get_text();
} catch (PillarException $e) {
	die( "Got an error when getting the enable page.\n" );
}
if( !preg_match( '/(enable|yes|run|go|start)/i', $run ) ) {
	die( "Bot is disabled.\n" );
}

$admins = array();
//$admins = $wpapi->users(null, 5000, 'sysop');

$vars = array('action'=>'query','list'=>'allusers','augroup'=>'sysop','aulimit'=>'5000');
$request = new MWRequest($site,$vars);
$result = $request->get_result();
$admins = $result['query']['allusers'];

if( count($admins) < 1000 ) die();

$data = '';
foreach( $admins as $admin ) {
	$data .= 'adminrights[\''.str_ireplace(
	array(
		'+',
		'\\',
		'\'',
		'(',
		')',
		'%21',
		'%2C',
		'%3A',
	),
	array(
		'%20',
		'\\\\',
		'%27',
		'%28',
		'%29',
		'!',
		',',
		':',
	),
	urlencode($admin['name'])).'\']=1;'."\n";
}
if( count($admins) != (count(explode("\n",$data)) - 1) ) { die("Error?"); }
echo $data;
//$wpi->post('User:SoxBot V/adminrights-admins.js', $data, 'Updating admins list');

try {
	$page = new Page($site,"User:SoxBot/adminrights-admins.js");
} catch (PillarException $e) {
	die();
}

try {
	$page->put($data,'Updating admins list',true);
} catch (PillarException $e) {
	die();
}

?>