<?php

define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');
require_once('/home/soxred93/textdiff/textdiff.php');
require_once('/home/soxred93/rfalib3.php');

$pillar = Pillar::ini_launch('/home/soxred93/configs/rfx-tally.cfg');
$site = $pillar->cursite;

$name = "Wikipedia:Requests for adminship";
try {
	//$rfa_main = new Page($site,$name);
	$open_rfxs = $site->get_embeddedin("Template:Rfatally",50,$continue,$namespace = 4);
} catch (PillarException $e) {
	die();
}
print_r($open_rfxs);
/*$rfa_main_text = $rfa_main->get_text();
preg_match_all('/\{\{Wikipedia:(.*?)\}\}/', $rfa_main_text, $open_rfxs);
$open_rfxs = $open_rfxs[1];*/

$tallys = array();
foreach( $open_rfxs as $open_rfx ) {
	if( in_array( $open_rfx, array( 'Wikipedia:Requests for adminship/Front matter', 'Wikipedia:Requests for adminship/bureaucratship', 'Wikipedia:Requests for adminship' ) ) ) continue;
	if( !preg_match( '/Wikipedia:Requests for (admin|bureaucrat)ship/i', $open_rfx ) ) continue;
	//$open_rfx = str_replace(array('Wikipedia:Requests for adminship/','Wikipedia:Requests for bureaucratship/'),'',$open_rfx);
	
	$myRFA = new RFA();
	
	try {
		$rfa_page = new Page($site,/*$name.'/'.*/$open_rfx);
	} catch (PillarException $e) {
		continue;
	}
	$rfa_text = $rfa_page->get_text();
 
	$result = $myRFA->analyze($rfa_text);

	if ($result !== TRUE) {
		continue;
	}
	
	$opposes = 0;
	foreach( $myRFA->oppose as $oppose ) {
		//if( $oppose['name'] != 'DougsTech' && !preg_match('/User:DougsTech\/RFAreason1/i', $oppose['context'] ) ) {
			$opposes++;
			echo "YA\n";
		//}
	}

	$tally = count($myRFA->support).'/'.$opposes.'/'.count($myRFA->neutral);
	
	$open_rfx = str_replace(array('Wikipedia:Requests for adminship/','Wikipedia:Requests for bureaucratship/'),'',$open_rfx);
	$tallys[$open_rfx] = $tally;
}

$out = "{{#switch: {{{1|{{SUBPAGENAME}}}}}\n";

foreach( $tallys as $rfa => $tally ) {
	$out .= "|$rfa= ($tally)\n";
}

$out .= "|#default= (0/0/0)\n}}";

echo $out;

try {
	$tally_page = new Page($site,"User:X!/Tally");
} catch (PillarException $e) {
	die();
}

try {
	$tally_page->put($out,"Updating RFA tally",true);
} catch (PillarException $e) {
}

?>
