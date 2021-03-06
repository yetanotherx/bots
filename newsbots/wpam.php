<?PHP

while( $month == "" ) {
	echo "What month is this newsletter?\n";
	$month = trim(fgets(STDIN));
}

include '/home/soxred93/wikibot.classes.php';

$wpq = new wikipediaquery;
$wpapi = new wikipediaapi;
$wpi = new wikipediaindex;
$user = "SoxBot";
$pass = file_get_contents('/home/soxred93/.password');
$wpapi->login($user,$pass);

if( !preg_match( '/(enable|yes|run|go|start)/i', $wpq->getpage("User:SoxBot/Run/Newsletter") ) ) {
	die( "Bot is disabled.\n" );
}

if( $argv[2] != "--nosource" ) {
	$wpi->forcepost(
		'User:'.$user.'/Source/Alternative Music',
		'The following post was automatically generated by [[User:'.$user.'|'.$user."]].\n\n<pre>" .
		htmlentities(file_get_contents(__FILE__)) . 
		"</pre>",
		'Automatic source code upload ([[WP:BOT|BOT]])'
	);
}

//$month = "March";
//$links = "User:X!/WPAM";
$temp = "Wikipedia:WikiProject Alternative music/Newsletter/$month 2009";
$summary = "Delivering newsletter. (BOT EDIT)";

$members = $wpq->getpage("Wikipedia:WikiProject Alternative music/Members");
preg_match_all('/\{\{user\|(.*?)\}\}/i', $members, $mems);
$mems = $mems[1];

$nolink = $wpq->getpage("Wikipedia:WikiProject Alternative music/Newsletter");
preg_match_all('/\<!-- (no|only)link: (.*?) --\>/', $nolink, $nol);
$nol = $nol[2];

$f = array();
foreach( $mems as $fin ) {
	$fin = str_replace( "_", " ", $fin );
	$fin = ucfirst($fin);
	$f[] = $fin;
}

foreach( $f as $val => $fi ) {
	if( in_array( $fi, $nol ) ) {
		unset( $f[$val] );
	}
}
print_r($f);

preg_match_all('/\<!-- onlylink: (.*?) --\>/', $nolink, $ol);
$ol = $ol[1];
			
foreach ($f as $data) {
	echo "Posting notice to ".$data."\n";
	$talk = $wpq->getpage("User talk:".$data);
	$write = $talk."\n== [[WP:ALTM|WikiProject Alternative Music]] Newsletter for $month 2009 ==\n{{subst:".$temp."}}\n~~~~";

	$wpi->post("User talk:".$data, $write, $summary, false, null, false);
}

foreach ($ol as $data) {
	echo "Posting link to ".$data."\n";
	$talk = $wpq->getpage("User talk:".$data);
	$write = $talk."\n== [[WP:ALTM|WikiProject Alternative Music]] Newsletter for $month 2009 ==\n
	The [[WP:ALTM|WikiProject Alternative Music]] Newsletter for $month 2009 has been released, you can view it [[Wikipedia:WikiProject Alternative music/Newsletter/$month 2009|here]].\n~~~~";

	$wpi->post("User talk:".$data, $write, $summary, false, null, false);
}

?>