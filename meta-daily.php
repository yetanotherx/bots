<?PHP

ini_set('memory_limit','16M');

include '/home/soxred93/wikibot.classes.php';

$wpq = new wikipediaquery;
$wpapi = new wikipediaapi;
$wpi = new wikipediaindex;
$http = new http;
$wpi->indexurl = 'http://meta.wikimedia.org/w/index.php';
$wpapi->apiurl = 'http://meta.wikimedia.org/w/api.php';
$wpq->queryurl = 'http://meta.wikimedia.org/w/query.php';

$user = "SoxBot";
$pass = file_get_contents('../.password');

$wpapi->login($user,$pass);


//Define the text
$sandboxtext = "{{/Please do not edit this line}}\n== Please edit below ==";
$hour = date("%H"); 
$minute = date("%i");

$currtext = $wpq->getpage('Meta:Sandbox');
$wpi->post('Meta:Sandbox',$sandboxtext,'Clearing the sandbox (BOT EDIT)');
?>