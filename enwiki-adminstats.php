<?php

define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');
require_once('/home/soxred93/database.inc');

$pillar = Pillar::ini_launch('/home/soxred93/configs/enwiki-adminstats.cfg');
//$pillar = Pillar::get_instance;
$site = $pillar->cursite;

mysql_connect('enwiki-p.db.ts.wikimedia.org',$toolserver_username,$toolserver_password);
@mysql_select_db('enwiki_p') or print mysql_error();

$u = array();
$users = $site->get_embeddedin('Template:Adminstats','500',$continue,'2|3');

foreach( $users as $user ) {
	$u[] = $user;
}
while( isset($users[499]) ) {
	$users = $site->get_embeddedin('Template:Adminstats','500',$continue,'2|3');
	foreach( $users as $user ) {
		$u[] = $user;
	}
}

$u = array_shuffle($u);
//$u = array("User talk:X!");

foreach ($u as $name) {
    preg_match("/User( talk)?:([^\/]*)/i", $name, $m);
    process($m[2]);
}

function process ($rawuser) {
	global $pillar;
	$user = mysql_real_escape_string($rawuser);
    $query = "SELECT count(rev_id) FROM revision WHERE rev_user_text = '$user'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    $row = mysql_fetch_assoc($result);
    $out = '{{Adminstats/Core'."\n";
    $numsqlbot = $row['count(rev_id)'];
    $out = $out . "|edits=$numsqlbot\n";
    $query = "SELECT * FROM user WHERE user_name = '$user'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    $row = mysql_fetch_assoc($result);
    $uid = $row[user_id];
    $numall = $row[user_editcount];
    $out = $out . "|ed=$numall\n";
    $query = "SELECT * FROM user_groups WHERE ug_user = '$uid'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
            if($row[ug_group] == "sysop") { $approved = 1; }
    }
    if ($approved != 1) { return; }
    $query = "SELECT count(log_action) FROM logging WHERE log_user = '$uid' AND log_type = 'newusers'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    $row = mysql_fetch_assoc($result);
    $newusers = $row['count(log_action)'];
    $out = $out . "|created=$newusers\n";
    $query = "SELECT count(log_action) FROM logging WHERE log_user = '$uid' AND log_action = 'delete'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    $row = mysql_fetch_assoc($result);
    $deletions = $row['count(log_action)'];
    $out = $out . "|deleted=$deletions\n";
    $query = "SELECT count(log_action) FROM logging WHERE log_user = '$uid' AND log_action = 'restore'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    $row = mysql_fetch_assoc($result);
    $restores = $row['count(log_action)'];
    $out = $out . "|restored=$restores\n";
    $query = "SELECT count(log_action) FROM logging WHERE log_user = '$uid' AND log_action = 'block'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    $row = mysql_fetch_assoc($result);
    $blocks = $row['count(log_action)'];
    $out = $out . "|blocked=$blocks\n";
    $query = "SELECT count(log_action) FROM logging WHERE log_user = '$uid' AND log_action = 'protect'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    $row = mysql_fetch_assoc($result);
    $protects = $row['count(log_action)'];
    $out = $out . "|protected=$protects\n";
    $query = "SELECT count(log_action) FROM logging WHERE log_user = '$uid' AND log_action = 'unprotect'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    $row = mysql_fetch_assoc($result);
    $unprotects = $row['count(log_action)'];
    $out = $out . "|unprotected=$unprotects\n";
    $query = "SELECT count(log_action) FROM logging WHERE log_user = '$uid' AND log_action = 'rights'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    $row = mysql_fetch_assoc($result);
    $grants = $row['count(log_action)'];
    $out = $out . "|rights=$grants\n";
    
    $query = "SELECT count(log_action) FROM logging WHERE log_user = '$uid' AND log_action = 'reblock'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    $row = mysql_fetch_assoc($result);
    $protects = $row['count(log_action)'];
    $out = $out . "|reblock=$protects\n";
    
    $query = "SELECT count(log_action) FROM logging WHERE log_user = '$uid' AND log_action = 'modify'";
    $result = mysql_query($query);
    if(!$result) toDie("ERROR: No result returned." . mysql_error());
    $row = mysql_fetch_assoc($result);
    $protects = $row['count(log_action)'];
    $out = $out . "|modify=$protects\n";
    
    $out = $out . '|style={{{style|}}}}}';
    echo $out;
    echo "\n";
    $toedit = "Template:Adminstats/$rawuser\n";
    echo "Editing $toedit";
    
    try {
	    $page = new Page($pillar->cursite,$toedit);
	} catch (PillarException $e) {
	    continue;
	}
	    
	try {
	    $page->put($out,"Updating Admin Stats",true);
	} catch (PillarException $e) {
	    continue;
	}
}

function toDie($newdata) {
	$f=fopen('./adminstats.log',"a");
          fwrite($f,$newdata);
          fclose($f);  
}

?>