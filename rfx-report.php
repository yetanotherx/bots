<?php

ini_set('memory_limit','16M');

define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');
require_once('/home/soxred93/rfalib3.php');

$pillar = Pillar::ini_launch('/home/soxred93/configs/rfx-report.cfg');
$site = $pillar->cursite;

$enablepage = "User:SoxBot/Run/RfXReport";
try {
	$run = new Page($site,$enablepage);
	$run = $run->get_text();
} catch (PillarException $e) {
	die( "Got an error when getting the enable page.\n" );
}
if( !preg_match( '/(enable|yes|run|go|start)/i', $run ) ) {
	die( "Bot is disabled.\n" );
}

$output = "User:X!/RfX Report";

function bailout($message) {
    echo "Fatal Error\n";
    echo "$message\n";
    exit;
}

function countDown($time) {
  // make a unix timestamp for the given date
  $c = $time;
 
  // get current unix timestamp
  $t = time();
 
  // calculate the difference
  $d = ($c - $t);
  if ($d < 0) $d = 0;
 
  $days = floor($d/60/60/24);
  $hours = floor(($d - $days*60*60*24)/60/60);
  $mins = floor(($d - $days*60*60*24 - $hours*60*60)/60);
 
  return array('days' => $days, 'hours' => $hours, 'mins' => $mins);
 
}

$probedits = 10;
$problems = array();
$problems[RfA] = array();
$problems[RfB] = array();
$numprobs = 0;
function processrfb($cantidate) {
	global $site;
	global $problems;
	global $probedits;
	global $numprobs;
	$rfabasename = "Wikipedia:Requests for bureaucratship/";
	$myRFA = new RFA();
	$buffer = new Page($site,$rfabasename . $cantidate);
	$buffer = $buffer->get_text();

	$result = $myRFA->analyze($buffer);
	$d_support = $myRFA->support;
	$d_oppose = $myRFA->oppose;
	$d_neu = $myRFA->neutral;
	$problems[RfB][$cantidate] = array();

	if ($result !== TRUE) {
		bailout($myRFA->lasterror);
	}
	$enddate = $myRFA->enddate;
	$tally = count($myRFA->support).'/'.count($myRFA->oppose).'/'.count($myRFA->neutral);
	$opposes = count($myRFA->oppose);
	$supports = count($myRFA->support);
	$neutrals = count($myRFA->neutral);
	$n_dup = 0;
	foreach($myRFA->duplicates as $dup) {
		$n_dup++;
	}
	$dups = "no";
	if ($n_dup > 0) {
		$dups = "'''yes'''";
	}
	$enddate2 = strtotime($enddate);
	$now = strtotime(date("F j, Y, g:i a"));
	/*$newbs = 0;
	foreach ($problems[RfB][$cantidate] as $newb) {
		$newbs++;
	}
	$numprobs = $numprobs + $newbs;
	if ($newbs > 0) { $s2 = "|problems=yes"; }*/
	if ($now > $enddate2) { 
		echo "EXPIRED!\n"; 
		$s1 = '|expired=yes';
		$timeleft = "'''EXPIRED'''";
	} 
	else { 
		echo "NOT EXPIRED!\n"; 
		$timeleft = countDown($enddate2);
		$timeleft = $timeleft['days']. ' days, ' . $timeleft['hours'] . ' hours';
	}
	echo "{{Bureaucrat candidate|candidate= $cantidate|support= $supports|oppose= $opposes|neutral= $neutrals|end date= $enddate |time left=$timeleft|dups= $dups$s1}}";
	return "{{Bureaucrat candidate|candidate= $cantidate|support= $supports|oppose= $opposes|neutral= $neutrals|end date= $enddate |time left=$timeleft|dups= $dups$s1|crat=yes}}";
}
function processrfa($cantidate) {
	global $site;
	global $problems;
	global $probedits;
	global $numprobs;
	echo "$cantidate\n";
	$rfabasename = "Wikipedia:Requests for adminship/";
	$myRFA = new RFA();
	$buffer = new Page($site,$rfabasename . $cantidate);
	$buffer = $buffer->get_text();

	$result = $myRFA->analyze($buffer);
	$d_support = $myRFA->support;
	$d_oppose = $myRFA->oppose;
	$d_neu = $myRFA->neutral;
	$problems[RfA][$cantidate] = array();
	/*foreach($d_support as $onesupp) {
		#echo "Checking supporter: $onesupp[name]\n";
		$supporter = $onesupp[name];
		$supclass = new User($site,$supporter);
		$s_stat = $supclass->get_editcount();
		//$supporter = urlencode($supporter);
		//$s_cont = file_get_contents("http://en.wikipedia.org/w/query.php?what=contribcounter&format=php&titles=User:$supporter");
		//$s_ncont = unserialize($s_cont);
		//$key = array_keys($s_ncont[pages]);
		//$key2 = $key[0];
		//$s_stat = $s_ncont[pages][$key2][contribcounter][count];
 
		if($s_stat < $probedits && $supporter != "") { 
			$t_upage = new Page($site,"User:$supporter");
			$t_upage = $t_upage->get_text();
			$redir = preg_match("/redirect(_| |)\[\[.*\]\]/i", $t_upage);
			if (!$redir) {
				echo "PROBLEM!\n";
				array_push($problems[RfA][$cantidate], $supporter); 
			}
		}
	}
	foreach($d_oppose as $oneopp) {
		#echo "Checking opposer: $oneopp[name]\n";
		$opposer = $oneopp[name];
		$oppclass = new User($site,$opposer);
		$o_stat = $oppclass->get_editcount();
		$opposer = urlencode($opposer);
		$o_cont = file_get_contents("http://en.wikipedia.org/w/query.php?what=contribcounter&format=php&titles=User:$opposer");
		$o_ncont = unserialize($o_cont);
		$key = array_keys($o_ncont[pages]);
		$key2 = $key[0];
		$o_stat = $o_ncont[pages][$key2][contribcounter][count];
		
		if($o_stat < $probedits && $opposer != "") { 
			$t_upage = new Page($site,"User:$opposer");
			$t_upage = $t_upage->get_text();
			$redir = preg_match("/redirect(_| |)\[\[.*\]\]/i", $t_upage);
			if (!$redir) {
				echo "PROBLEM!\n";
				array_push($problems[RfA][$cantidate], $opposer); 
			}
		}
	}
	foreach($d_neu as $oneneu) {
		#echo "Checking opposer: $oneopp[name]\n";
		$neu = $oneneu[name];
		$neuclass = new User($site,$neu);
		$n_stat = $neuclass->get_editcount();
		
		$neu = urlencode($neu);
		$n_cont = file_get_contents("http://en.wikipedia.org/w/query.php?what=contribcounter&format=php&titles=User:$neu");
		$n_ncont = unserialize($n_cont);
		$key = array_keys($n_ncont[pages]);
		$key2 = $key[0];
		$n_stat = $n_ncont[pages][$key2][contribcounter][count];
		
		if($n_stat < $probedits && $neu != "") { 
			$t_upage = new Page($site,"User:$neu");
			$t_upage = $t_upage->get_text();
			$redir = preg_match("/redirect(_| |)\[\[.*\]\]/i", $t_upage);
			if (!$redir) {
				echo "PROBLEM!\n";
				array_push($problems[RfA][$cantidate], $neu); 
			}
		}
	}
	*/
	if ($result !== TRUE) {
		bailout($myRFA->lasterror);
	}
	$enddate = $myRFA->enddate;
	$tally = count($myRFA->support).'/'.count($myRFA->oppose).'/'.count($myRFA->neutral);
	
	$opposes = count($myRFA->oppose);
	$supports = count($myRFA->support);
	$neutrals = count($myRFA->neutral);
	$n_dup = 0;
		foreach($myRFA->duplicates as $dup) {
		$n_dup++;
	}
	$dups = "no";
	if ($n_dup > 0) {
		$dups = "'''yes'''";
	}
	$enddate2 = strtotime($enddate);
	$now = strtotime(date("F j, Y, g:i a"));
	/*foreach ($problems[RfA][$cantidate] as $newb) {
		$newbs++;
	}
	$numprobs = $numprobs + $newbs;
	if ($newbs > 0) { $s2 = "|problems=yes"; }*/
	if ($now > $enddate2) { 
		echo "EXPIRED!\n"; 
		$s1 = '|expired=yes';
		$timeleft = "'''EXPIRED'''";
	} 
	else { 
		echo "NOT EXPIRED!\n"; 
		$timeleft = countDown($enddate2);
		$timeleft = $timeleft['days']. ' days, ' . $timeleft['hours'] . ' hours';
	}
	echo "{{Bureaucrat candidate|candidate= $cantidate|support= $supports|oppose= $opposes|neutral= $neutrals|end date= $enddate |time left=$timeleft|dups= $dups$s1}}";
	return "{{Bureaucrat candidate|candidate=$cantidate|support=$supports|oppose=$opposes|neutral=$neutrals|end date=$enddate |time left=$timeleft|dups=$dups|rfa=yes$s1}}";
}
$findrfa = "/\{\{Wikipedia:Requests for adminship\/(.*)\}\}/";
$findrfb = "/\{\{Wikipedia:Requests for bureaucratship\/(.*)\}\}/";
$rfabuffer = new Page($site,"Wikipedia:Requests for adminship");
$rfabuffer = $rfabuffer->get_text();
#$rfabuffer = sxGetPage("User:SQL/RFATest");
preg_match_all($findrfa, $rfabuffer, $matches);
$numrfa = 0;
echo "Processing RfA's!\n";
$out = "<noinclude>{{shortcut|WP:RFXR||WP:BNRX|WP:BN/RfX Report}}</noinclude>\n".'{| {{{style|align="{{{align|right}}}" cellspacing="0" cellpadding="0" style="white-space:nowrap; clear: {{{clear|left}}}; margin-top: 0em; margin-bottom: .5em; float: {{{align|right}}};padding: .5em 0em 0em 1.4em; background: none;"}}}'."\n|\n{| class=\"wikitable\"\n! RfA candidate !! S !! O !! N !! S% !! Ending (UTC) !! Time left !! Dups? !! Report" . $out;
foreach ($matches[1] as $rfa) {
	if ($rfa == "Front matter" || $rfa == "bureaucratship") {
		echo "Skipping non-rfa stuff\n";
	} else {
		$result = processrfa($rfa);
		$out = $out . "\n|-\n" . $result;
		$numrfa++;
	}
}
$out = $out . "\n|-\n! RfB candidate !! S !! O !! N !! S% !! Ending (UTC) !! Time left !! Dups? !! Report";
echo "Processing RFB's!\n";
preg_match_all($findrfb, $rfabuffer, $matches);
$numrfb = 0;
foreach ($matches[1] as $rfb) {
	$result = processrfb($rfb);
	$out = $out . "\n|-\n" . $result;
	$numrfb++;
}
$out = $out . "\n|}<div align=\"right\">\n''Last updated by '''~~~''' at '''~~~~~'''''\n</div>\n|}\n";
echo $out;

$rfabuffer = new Page($site,"User:SoxBot/Run");
$rfabuffer = $rfabuffer->get_text();
$runme = ltrim($rfabuffer);
$runme = rtrim($runme);
if (preg_match('/(yes|enable|true)/iS',$runme)) {
	/*if ($numprobs > 0) {
		$reportbuffer = new Page($site,$output);
		$reportbuffer = $reportbuffer->put($out, "Updating RFB Report, $numrfa RFA's, $numrfb RFB's, $numprobs [[User:SQL/RfX Report/Potential problems|problems]]");

		$propage = "==Possible RfX Problems==\nThe following discussions contain Supports, opposes, or neutrals, from users with fewer than $probedits edits.\n===Requests for Adminship===\n";
		foreach ($problems[RfA] as $cname => $ulist) {
			$propage = $propage . "====$cname====\n";
			foreach ($problems[RfA][$cname] as $pnewb) {
				$propage = $propage . "* $pnewb\n";
			}
		}
		$bpro = 0;
		$bpropage = "===Requests for Bureaucratship===\n";
		foreach ($problems[RfB] as $cname => $ulist) {
			$bpropage = $bpropage . "====$cname====\n";
			foreach ($problems[RfB][$cname] as $pnewb) {
				$bpropage = $bpropage . "* $pnewb\n";
				$bpro++;
			}
		}
		if ($bpro > 0) {
			$propage = $propage . $bpropage;
		}
		$reportbuffer = new Page($site,"User:X!/RfX Report/Potential problems");
		$reportbuffer = $reportbuffer->put($propage,"Updating RfX Problem Report");
	} else {*/
		$reportbuffer = new Page($site,$output);
		$reportbuffer = $reportbuffer->put($out, "Updating RFB Report, $numrfa RFAs, $numrfb RFBs");
	/*}*/
} else {
	echo "Could not run, disabled!\n";
}
if ($numprobs > 0) {
	print_r($problems);
}
?>
