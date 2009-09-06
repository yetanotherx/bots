<?php

ini_set('memory_limit','16M');

define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');
require_once('/home/soxred93/textdiff/textdiff.php');
require_once('/home/soxred93/database.inc');

$pillar = Pillar::ini_launch('/home/soxred93/configs/wikicup.cfg');
$site = $pillar->cursite;

$starting_timestamp = '20090801000000';
$contestant_points = array();
$points_page_name = 'Wikipedia:WikiCup/History/2009';
$contestant_page_name = 'Wikipedia:WikiCup/History/2009/Contestants/Machine';

mysql_connect('enwiki-p.db.toolserver.org',$toolserver_username,$toolserver_password);
mysql_select_db('enwiki_p');

try {
	$contestant_page = new Page($site,$contestant_page_name);
} catch (PillarException $e) {
	die($e);
}

$contestants = explode("\n",$contestant_page->get_text());
print_r($contestants);

foreach( $contestants as $contestant ) {
	$contestant = explode('|',$contestant);
	$country = $contestant[1];
	$contestant = $contestant[0];
	echo "Starting parsing for user $contestant...\n\n";
	
	$points = array();
	
	$cont_safe = mysql_real_escape_string($contestant);
	
	$mnsp_minor_count_query = "SELECT COUNT(*) as count FROM revision JOIN page ON page_id = rev_page WHERE rev_user_text = '$cont_safe' AND page_namespace = '0' AND rev_minor_edit = '1' AND rev_timestamp > '$starting_timestamp' AND rev_comment NOT LIKE '%[[WP:HG|HG]]%' AND rev_comment NOT LIKE '%[[WP:AWB|AWB]]%' AND rev_comment NOT LIKE '%[[WP:TW|TW]]%';";
	$mnsp_minor_count = mysql_query($mnsp_minor_count_query);
	if( !$mnsp_minor_count ) die( "Mysql Error:" . mysql_error() . "\n\n" );
	$mnsp_minor_count = mysql_fetch_assoc($mnsp_minor_count);
	$mnsp_minor_count = $mnsp_minor_count['count'];
	$mnsp_minor_points = $mnsp_minor_count * 0.01;
	
	$mnsp_major_count_query = "SELECT COUNT(*) as count FROM revision JOIN page ON page_id = rev_page WHERE rev_user_text = '$cont_safe' AND page_namespace = '0' AND rev_minor_edit = '0' AND rev_timestamp > '$starting_timestamp' AND rev_comment NOT LIKE '%[[WP:HG|HG]]%' AND rev_comment NOT LIKE '%[[WP:AWB|AWB]]%' AND rev_comment NOT LIKE '%[[WP:TW|TW]]%';";
	$mnsp_major_count = mysql_query($mnsp_major_count_query);
	if( !$mnsp_major_count ) die( "Mysql Error:" . mysql_error() . "\n\n" );
	$mnsp_major_count = mysql_fetch_assoc($mnsp_major_count);
	$mnsp_major_count = $mnsp_major_count['count'];
	$mnsp_major_points = $mnsp_major_count * 0.1;
	
	$points['mainspace'] = ceil( $mnsp_major_points + $mnsp_minor_points );
	
	$contestant_sub_page_name = 'Wikipedia:WikiCup/Submissions/'.$contestant;
	try {
		$contestant_sub_page = new Page($site,$contestant_sub_page_name);
	} catch (PillarException $e) {
		die($e);
	}
	$contestant_submissions = $contestant_sub_page->get_text();

	$m = preg_split('/===(.*?)===/',$contestant_submissions);

	$sections = array(
		'GA' => $m[4],
		'FA' => $m[8],
		'FL' => $m[5],
		'FS' => $m[7],
		'FP' => $m[6],
		'FPO' => $m[3],
		'DYK' => $m[1],
		'ITN' => $m[2],
		'FT' => $m[9],
		'GT' => $m[10]
	);
	
	
	//START DYK
	$dyk_lines = explode("\n",$sections['DYK']);
	$dyk_count = 0;
	foreach($dyk_lines as $dyk) {
		if( preg_match('/^\#./',$dyk) ) $dyk_count++;
	}
	$points['DYK'] = ceil( $dyk_count * 5 );
	
	//START ITN
	$itn_lines = explode("\n",$sections['ITN']);
	$itn_count = 0;
	foreach($itn_lines as $itn) {
		if( preg_match('/^\#./',$itn) ) $itn_count++;
	}
	$points['ITN'] = ceil( $itn_count * 5 );
	
	//START FPO
	$fpo_lines = explode("\n",$sections['FPO']);
	$fpo_count = 0;
	foreach($fpo_lines as $fpo) {
		if( preg_match('/^\#./',$fpo) ) $fpo_count++;
	}
	$points['FPO'] = ceil( $fpo_count * 25 );
	
	//START GA
	$ga_lines = explode("\n",$sections['GA']);
	$ga_count = 0;
	foreach($ga_lines as $ga) {
		if( preg_match('/^\#./',$ga) ) $ga_count++;
	}
	$points['GA'] = ceil( $ga_count * 30 );
	
	//START FL
	$fl_lines = explode("\n",$sections['FL']);
	$fl_count = 0;
	foreach($fl_lines as $fl) {
		if( preg_match('/^\#./',$fl) ) $fl_count++;
	}
	$points['FL'] = ceil( $fl_count * 30 );
	
	//START FP
	$fp_lines = explode("\n",$sections['FP']);
	$fp_count = 0;
	foreach($fp_lines as $fp) {
		if( preg_match('/^\#./',$fp) ) $fp_count++;
	}
	$points['FP'] = ceil( $fp_count * 35 );
	
	//START FS
	$fs_lines = explode("\n",$sections['FS']);
	$fs_count = 0;
	foreach($fs_lines as $fs) {
		if( preg_match('/^\#./',$fs) ) $fs_count++;
	}
	$points['FS'] = ceil( $fs_count * 35 );
	
	//START FA
	$fa_lines = explode("\n",$sections['FA']);
	$fa_count = 0;
	foreach($fa_lines as $fa) {
		if( preg_match('/^\#./',$fa) ) $fa_count++;
	}
	$points['FA'] = ceil( $fa_count * 50 );
	
	//START FT
	$ft_lines = explode("\n",$sections['FT']);
	$ft_count = 0;
	foreach($ft_lines as $ft) {
		if( preg_match('/^\#\#./',$ft) ) $ft_count++;
	}
	$points['FT'] = ceil( $ft_count * 10 );
	
	//START GT
	$gt_lines = explode("\n",$sections['GT']);
	$gt_count = 0;
	foreach($gt_lines as $gt) {
		if( preg_match('/^\#\#./',$gt) ) $gt_count++;
	}
	$points['GT'] = ceil( $gt_count * 5 );
	
	$total_points = array_sum( $points );

	$contestant_points[] = array(
		'name' => $contestant,
		'country' => $country,
		'total' => $total_points,
		'points' => $points,
	);
}

print_r($contestant_points);

try {
	$points_page = new Page($site,$points_page_name);
} catch (PillarException $e) {
	die($e);
}

$points_page_text = $points_page_text_original = $points_page->get_text();

foreach( $contestant_points as $user ) {
	$name = $user['name'];
	$country = $user['country'];
	
	preg_match( 
		'/'.
		preg_quote(
			'|style="text-align:left;"|{{flagicon|'
		) . $country . 
		preg_quote(
			'}} [[User:'
		) . $name . '\|' . $name . '\]\]' . '\n(.*?)\n/mi'
	, $points_page_text, $m );
	
	$points = $m[0];
	$points = "|style=\"text-align:left;\"|{{flagicon|$country}} [[User:$name|$name]]\n|";
	
	$points .= implode('||',$user['points']);
	
	$points .= "||'''{$user['total']}'''\n";
	
	$points_page_text = str_replace($m[0],$points,$points_page_text);
}

echo getTextDiff('unified', $points_page_text_original, $points_page_text);

try {
	$points_page->put($points_page_text,"Bot: Updating WikiCup table",true);
} catch (PillarException $e) {
	die($e);
}

?>