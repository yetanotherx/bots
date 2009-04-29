<?php

//Load includes
include '/home/soxred93/wikibot.classes.php';
include '/home/soxred93/rfalib2.php';
$pass = file_get_contents('/home/soxred93/.password');
$user = "SoxBot";
 
//Setting needed vars
$wpapi  = new wikipediaapi;
$wpq    = new wikipediaquery;
$wpi    = new wikipediaindex;
 
//Logging in
$wpapi->login($user,$pass);
echo "Logged In!\n";

function getEndDate($rfa) {
	global $wpq;
	$myRFX = new RFA();
	$result = $myRFX->analyze($wpq->getpage('Wikipedia:Requests for adminship/'.$rfa));
	$enddate = $myRFX->enddate;
	$enddate2 = strtotime($enddate);
	$now = strtotime(date("F j, Y, g:i a"));
	$now = $now + 18000;
	if ($now > $enddate2) { 
		echo "$rfa EXPIRED ($now > $enddate2)!\n";
		return true;
	}
	else {
		return false;
	}
}

$findrfa = "/\{\{Wikipedia:Requests for adminship\/(.*)\}\}/";
$findrfb = "/\{\{Wikipedia:Requests for bureaucratship\/(.*)\}\}/";
$rfabuffer = $wpq->getpage("Wikipedia:Requests for adminship");

preg_match_all($findrfa, $rfabuffer, $matches);
$numrfa = 0;
$numorfa = 0;
echo "Processing RfA's!\n";

foreach ($matches[1] as $rfa) {
	if ($rfa == "Front matter" || $rfa == "bureaucratship") {
		echo "Skipping non-rfa stuff\n";
	} else {
		$numrfa++;
		if ( getEndDate($rfa) ) {
			$numorfa++;
		}
	}
}

preg_match_all($findrfb, $rfabuffer, $matches);
$numrfb = 0;
$numorfb = 0;
foreach ($matches[1] as $rfb) {
	$numrfb++;
	if ( getEndDate($rfb) ) {
		$numorfb++;
	}
}

echo "$numrfa RfAs, $numrfb RfBs.\n";

echo "$numorfa overdue RfAs, $numorfb overdue RfBs.\n";

$numchuu = 0;
$crats = array();
		$cratsd = $wpapi->users(null,500,"bureaucrat");
		foreach ($cratsd as $crat) $crats[] = preg_quote($crat['name'],'/');
		$crats = implode('|',$crats);
		$origdata = $wpq->getpage('Wikipedia:Changing username/Usurpations');
		$data = preg_replace('/\=\=\=\s*(January|February|March|April|May|June|July|August|September|October|November|December)\s*\d{1,2},\s*\d{4}\s*\=\=\=/','',preg_replace('/\'\'Requests left here will be filled no earlier than (January|February|March|April|May|June|July|August|September|October|November|December)\s*\d{1,2},\s*\d{4}\.\'\'/','',preg_replace('/\=\=\=\s*Unknown\s*\=\=\=/','',$origdata)));
		$header = explode('====',$data,2);
		$data = '===='.$header[1];
		$header = $header[0];
		$datatopost = $header;
		preg_match_all('/\=\=\=\=\s*(\S.*)\s*\=\=\=\=(.*)(?=\=\=\=\=\s*\S.*\s*\=\=\=\=(.*)|$)/Us',$data,$m,PREG_SET_ORDER);
		$footer = explode($m[count($m)-1][2],$data);
		$footer = $footer[1];
		$cmt = 0;
//		echo $footer."\n\n";
//		print_r($m);
		echo $crats."\n";
		foreach ($m as $k => &$request) {
			if (preg_match('/\{\{Done\}\}.*(\d{2}):(\d{2}), (\d+) ([a-zA-Z]+) (\d{4}) \(UTC\)/i',$request[2],$match)) {
				if (preg_match('/User:('.$crats.')/i',$request[2])) {
					$month = array('January' => 1, 'February' => 2, 'March' => 3,
						'April' => 4, 'May' => 5, 'June' => 6, 'July' => 7,
						'August' => 8, 'September' => 9, 'October' => 10,
						'November' => 11, 'December' => 12
					);
					if ((time() - gmmktime($match[1],$match[2],0,$month[$match[4]],$match[3],$match[5])) > 12*60*60) {
						$darchive[] = $request;
						unset($m[$k]);
					}
				}
				continue;
			}
			if (preg_match('/\{\{Not ?done\}\}.*(\d{2}):(\d{2}), (\d+) ([a-zA-Z]+) (\d{4}) \(UTC\)/i',$request[2],$match)) {
				if (preg_match('/User:('.$crats.')/i',$request[2])) {
					$month = array('January' => 1, 'February' => 2, 'March' => 3,
						'April' => 4, 'May' => 5, 'June' => 6, 'July' => 7,
						'August' => 8, 'September' => 9, 'October' => 10,
						'November' => 11, 'December' => 12
					);
					if ((time() - gmmktime($match[1],$match[2],0,$month[$match[4]],$match[3],$match[5])) > 48*60*60) {
						$narchive[] = $request;
						unset($m[$k]);
					}
				}
				continue;
			}
			$numchuu++;
		
}

$numchu = 0;

$data = $wpq->getpage('Wikipedia:Changing username');
preg_match_all('/\=\=\=\s*(\S.*)\s*\=\=\=(.*)(?=\=\=\=\s*\S.*\s*\=\=\=(.*)|$)/Us',$data,$m,PREG_SET_ORDER);

foreach ($m as $request) {

	if (empty($request[0])) {
    	die("Bad API Result\n");
	}

	if (preg_match('/\{\{[oO]n\s?hold/i',$request[0])) continue;
	if (preg_match('/\{\{[dD]one\}\}/i',$request[0])) continue;
	if (preg_match('/\{\{[nN]ot\s?done\}\}/i',$request[0])) continue;
	if (preg_match('/\{\{CHU/i',$request[0])) continue;
	if (preg_match('/\{\{\[\[Image:.*/i',$request[0])) continue;

	$numchu++;

}

$numsul = 0;

$data = $wpq->getpage('Wikipedia:Changing username/SUL');
preg_match_all('/\=\=\=\s*(\S.*)\s*\=\=\=(.*)(?=\=\=\=\s*\S.*\s*\=\=\=(.*)|$)/Us',$data,$m,PREG_SET_ORDER);

foreach ($m as $request) {

	if (empty($request[0])) {
    	die("Bad API Result\n");
	}

	if (preg_match('/\{\{[oO]n\s?hold/i',$request[0])) continue;
	if (preg_match('/\{\{[dD]one\}\}/i',$request[0])) continue;
	if (preg_match('/\{\{[nN]ot\s?done\}\}/i',$request[0])) continue;
	if (preg_match('/\{\{Clerk\s?note\}\}/i',$request[0])) continue;
	if (preg_match('/\{\{\[\[Image:.*/i',$request[0])) continue;

	$numsul++;

}

$numbrfa = 0;
$text = $wpq->getpage('Wikipedia:BAG/Status');
	preg_match_all('/\|-(.+?)(?=\|-)/is', $text, $record);
	foreach ($record[1] as $bot) {
		$numbrfa++;
	}

$numnfbrfa = 0;
$text = $wpq->getpage('Wikipedia:Bots/Requests for approval/Approved');
	preg_match_all('/\{\{BRFA\|(.*)\|(.*)\|Approved\|(.*)\}\}/i', $text, $record);
	print_r($record);
	foreach ($record[0] as $bot) {
		$numnfbrfa++;
	}


echo "$numchuu USURP requests, $numchu CHU requests, $numrfa RfAs, $numrfb RfBs, $numorfa overdue RfAs, $numorfb overdue RfBs, $numbrfa BRFAs, $numsul SUL CHU requests, $numnfbrfa Approved BRFAs.\n";

$text = "{{Cratstats/Core|usurp=$numchuu|chu=$numchu|chusul=$numsul|rfa=$numrfa|rfb=$numrfb|orfa=$numorfa|orfb=$numorfb|brfa=$numbrfa|abrfa=$numnfbrfa|style={{{style|}}}}}";
$wpi->post('Template:Cratstats', $text, 'Posting status of Bureaucrat related areas. (BOT EDIT)');

?>