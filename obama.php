<?php
define('PILLAR','PILLAR'); 
include('/home/soxred93/pillar/trunk/class.pillar.php');
include('/home/soxred93/wikibot.classes.php');
$wpi = new wikipediaindex;
$wpapi = new wikipediaapi;
$user = 'SoxBot II';
$pass = 'xjxewn';
$wpapi->login($user,$pass);
//$pillar = new Pillar('enwiki','en.wikipedia.org','w/api.php','SoxBot II','xjxewn',false);
$pillar = Pillar::ini_launch('/home/soxred93/configs/obama.cfg');
//$pillar = Pillar::get_instance;
$site = $pillar->cursite;

$pillar->add_site('results','http://www.pollingreport.com','obama_job.htm');
$request = new Request($pillar->get_site('results'),array(),false);

$body = $request->get_body();
$body = explode('<table border="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="700" cellpadding="0" id="AutoNumber27">',$body);
$body = explode('</table>',$body[1]);
$body = $body[0];

$results = preg_match_all('/'.
    '\<font face="Verdana" size="2" color="#666666"\>(.*?)\<\/font\>\<\/span\>\<\/td\>\s*\<td width="88" align="center"\>\<span lang="en-us"\>\s*\<font face="Verdana" size="2" color="#666666"\>(\d{1,3})\<\/font\>\<\/span\>\<\/td\>\s*\<td width="89" align="center"\>\<span lang="en-us"\>\s*\<font face="Verdana" size="2" color="#666666"\>(\d{1,3})\<\/font\>\<\/span\>\<\/td\>/i',$body,$m);

$dates = array_reverse($m[1]);
$sup = array_reverse($m[2]);
$opp = array_reverse($m[3]);
$approve = array();
$disapprove = array();
$neutral = array();
foreach( $dates as $key => $value ) {
	$date = preg_match('/^(\d*)\/(.*?)\/(\d*)$/',$value,$r);
	$d = $r[2];
	$m = $r[1];
	$y = $r[3];
	$d = preg_replace('/(\d*)\s?-\s?\d*/','\1',$d);
	$d = explode('/',$d);
	$d = $d[0];
	if( $d % 3 != 0 ) { continue; }
	$diff = (strtotime(date("Y-m-d"))-strtotime("20$y-$m-$d")) / (60 * 60 * 24);
	$approve[] = $diff.','.($sup[$key]*10);
	$disapprove[] = $diff.','.($opp[$key]*10);
	$neutral[] = $diff.','.((100 - $sup[$key] - $opp[$key])*10);
}

$code = file_get_contents('./public_html/Barack_Obama_approval_ratings.svg');
$code = preg_replace('/\<polyline stroke="#4A7EBB" points="(.*?)"\/\>/ms','<polyline stroke="#4A7EBB" points="'.implode(" \n",$approve).'"/>',$code);
$code = preg_replace('/\<polyline stroke="#BE4B48" points="(.*?)"\/\>/ms','<polyline stroke="#BE4B48" points="'.implode(" \n",$disapprove).'"/>',$code);
$code = preg_replace('/\<polyline stroke="#98B954" points="(.*?)"\/\>/ms','<polyline stroke="#98B954" points="'.implode(" \n",$neutral).'"/>',$code);

file_put_contents('./public_html/Barack_Obama_approval_ratings.svg', $code);
//if (date("w") == '3' || date("w") == '1' || date("w") == '5') {
	//$site->upload('Barack_Obama_approval_ratings.svg','/home/soxred93/public_html/Barack_Obama_approval_ratings.svg','Automated upload of graph');
	$wpi->upload('Barack_Obama_approval_ratings.svg','/home/soxred93/public_html/Barack_Obama_approval_ratings.svg','Automated upload of graph');
//}
//$wpi->post('User:X!/Obama Graph', $code, 'Posting the code for an SVG graph (BOT EDIT)');
?>