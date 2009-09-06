<?php

ini_set('memory_limit','16M');
set_time_limit( 60 * 5 );

define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');
require_once('/home/soxred93/wikibot.classes.php');
require_once('/home/soxred93/textdiff/textdiff.php');
require_once('/home/soxred93/database.inc');
$wpq = new wikipediaquery;
$wpapi = new wikipediaapi;

$pillar = Pillar::ini_launch('/home/soxred93/configs/chu.cfg');
$site = $pillar->cursite;

mysql_connect('yarrow.toolserver.org',$toolserver_username,$toolserver_password);
mysql_select_db('centralauth_p');

$enablepage = "User:SoxBot/Run/CHU";
try {
	$run = new Page($site,$enablepage);
	$run = $run->get_text();
} catch (PillarException $e) {
	die( "Got an error when getting the enable page.\n" );
}
if( !preg_match( '/(enable|yes|run|go|start)/i', $run ) ) {
	die( "Bot is disabled.\n" );
}

try {
	$chu_page = new Page($site,"Wikipedia:Changing username");
	$chu_page = $chu_page->get_text();
} catch (PillarException $e) {
	die( $e );
}

preg_match_all('/\=\=\=\s*(.*)\s*\→\s*(.*)\s*\=\=\=(.*)(?=\=\=\=\s*\S.*\s*\=\=\=(.*)|$)/Us',$chu_page,$m,PREG_SET_ORDER);

foreach( $m as $req ) {
	$request = $req[0];
	$from = ucfirst( preg_replace( '/^(NEW)?\s?/', '', $req[1] ) );
	$to = ucfirst( preg_replace( '/^(CURRENT)?\s?/', '', $req[2] ) );
	$content = $req[3];

	if( empty( $request ) || empty( $content ) || empty( $to ) || empty( $from ) ) {
		die( "Bad API result.\n" );
	}
	
	if (preg_match('/\{\{[oO]n\s?hold/i',$content)) continue;
	if (preg_match('/\{\{CHU.*/i',$content)) continue;
	if (preg_match('/\{\{[cC]lerk\s?note/i',$content)) continue;
	if (preg_match('/\{\{[cC]rat\s?note/i',$content)) continue;
	if (preg_match('/\{\{[dD]one\}\}/i',$content)) continue;
	if (preg_match('/\{\{[nN]ot\s?done\}\}/i',$content)) continue;
	if (preg_match('/\{\{\[\[Image:.*/i',$content)) continue;
	if (preg_match('/SoxBot/i',$content)) continue;
	if (!preg_match('/\{\{renameuser2/i',$content)) continue;

	echo "New request for $to, requested by $from.\n";
	
	$data = array();
	$data['taken'] = 'yes';

	try {
		$to_user_info = new User( $site, $to );
	} catch (PillarException $e) {
		$data['taken'] = 'no';
	}
	
	try {
		$from_user_info = new User( $site, $from );
	} catch (PillarException $e) {
		continue;
	}
	
	$data['editcount'] = $from_user_info->get_editcount();
	$tmp = null;	
	while (($tmp = count($wpapi->usercontribs($from,500,&$cont))) == 500) {
		$data['visibleedits'] += $tmp;
	}
	$data['visibleedits'] += $tmp;
	$data['deletededits'] = $data['editcount'] - $data['visibleedits'];
	unset($tmp);
	
	if (preg_match('/\[\[Special\:Contributions\/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\]\] \(\[\[User talk\:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\|talk\]\]\) \d\d\:\d\d\, \d\d \w* \d\d\d\d \(UTC\)/', $content, $ir)) {
		$data['ip'] = $ir[1];
	}
	else {
		$data['ip'] = 'no';
	}
	
	if( !$from_user_info->get_blockstatus() ) {
		$data['nowblocked'] = 'no';
	}
	else {
		$data['nowblocked'] = 'yes';
	}
	
	$data['long'] = (strlen($to) > 40)? 'yes' : 'no';
	
	$data['beta'] = 'beta';

	if( !mysql_fetch_assoc( mysql_query( 'SELECT * FROM globaluser WHERE gu_name = \''. mysql_real_escape_string($to) .'\' LIMIT 1;' ) ) ) {
		$data['sul'] = 'no';
	}
	else {
		$data['sul'] = 'yes';
	}
	
	print_r($data);
	
	$template = null;
	$changed = false;
	$errors = array();
	
	if( $data['taken'] == 'yes' ) {
		if ( $data['editcount'] != 0 ) {
			$errors[] = "Target username exists, and it has edits, deleted edits, and/or log edits";
		}
		else {
			$errors[] = 'Target username exists, but you can place a request to usurp the username at [[WP:USURP]]';
		}
	}
	
	if ($data['ip'] != 'no') {
		$errors[] = "Request was made by ip " . $data['ip'] . ", instead of the original account";
	}
	
	if ($data['nowblocked'] == "yes") {
		$errors[] = "Old username is currently blocked";
	}
	
	if ($data['long'] == "yes") {
		$errors[] = "New username is longer than 40 characters";
	}

	if ($data['sul'] == "yes") {
		$errors[] = "Target account conflicts with SUL";
	}
	
	if (count($errors) > 0) {
		$toadd = "*{{User:SoxBot/CHU|error".$data['beta']."|";
		$toadd .= implode( '; ', $errors );
		$toadd .= ".}} ~~~~\n";
		$changed = true;
	}
	else {
		$toadd = "*{{User:SoxBot/CHU|noerror".$data['beta']."}} ~~~~\n";
		$changed = true;
	}
	
	if ($changed == true) {
		try {
			$chu_page = new Page($site,"Wikipedia:Changing username");
			$chu_page_text = $chu_page->get_text();
		} catch (PillarException $e) {
			continue;
		}
		
		$append = $content . "\n" . $toadd;
		$append = str_replace($content, $append, $chu_page_text);
		if ($append == '' || empty($append)) { continue; }
		
		echo "\n\n\n\n\n\n\n\n\n\n\n".getTextDiff('unified', $chu_page_text, $append);
		
		if( $chu_page->checkexcluded() ) continue;
		
		try {
			$chu_page->put( $append, "Adding clerk notices (BOT)" );
		} catch (Pillarexception $e) {
			continue;
		}
	}
}

?>
