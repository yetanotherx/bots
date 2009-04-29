<?PHP

include '/home/soxred93/wikibot.classes.php';

$wpq = new wikipediaquery;
$wpapi = new wikipediaapi;
$wpi = new wikipediaindex;
$http = new http;

$user = "SoxBot";
$pass = file_get_contents('/home/soxred93/.password');

$wpapi->login($user,$pass);

$text = $wpq->getpage('User:X!/Sox Commons');
preg_match_all('/\=\= \[\[User\:(.*)\|(.*)\]\] \=\=/', $text, $r);
unset($r[0]);
unset($r[1]);
$r = $r[2];
print_r($r);
$write = "\n\n== Automated report of SoxBots ==\n\nHere is a report of the status of all SoxBots as listed on [[User:X!/Sox Commons]]:\n<div class=\"NavFrame\"><div class=\"NavHead\" >Click [show] to expand.</div><div class=\"NavContent\" style=\"display:none;\">\n";
foreach ($r as $data) {
	$contribs = $wpapi->usercontribs($data,5);
	$count = $wpapi->users($data);
	if (!preg_match('/(yes|enable|true)/',$wpq->getpage('User:'.$user.'/Run'))) {
		$isenabled = '\'\'\'disabled\'\'\'';
	}
	else {
		$isenabled = "enabled";
	}
	$write .= ";[[User:".$data."|]]\n*".$data." has ".$count[0]['editcount']." [[Special:Contributions/".$data."|contributions]], including deleted contributions.\n*".$data." is currently [[User:".$data."/Run|".$isenabled."]].\n*".$data.'\'s last 5 contributions:'." \n";
	foreach ($contribs as $data2) {
		$write .= "**<span class=\"plainlinks\">[http://en.wikipedia.org/w/index.php?title=".urlencode($data2['title'])."&diff=prev&oldid=".$data2['revid']." ".$data2['title']." at ".$data2['timestamp'].".]</span>\n";
	}
}
$write .= "</div></div>\n:~~~~";
echo $write;
$write = $wpq->getpage('User talk:X!').$write;
$wpi->forcepost('User talk:X!', $write, 'Posting the status of the SoxBots. (BOT EDIT)');

?>
