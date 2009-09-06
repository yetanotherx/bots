<?PHP

ini_set('memory_limit','16M');

include '/home/soxred93/wikibot.classes.php';

$wpq = new wikipediaquery;
$wpapi = new wikipediaapi;
$wpi = new wikipediaindex;
$http = new http;

$user = "SoxBot";
$pass = file_get_contents('/home/soxred93/.password');

if( !preg_match( '/(enable|yes|run|go|start)/i', $wpq->getpage("User:SoxBot/Run/Sandbox") ) ) {
	die( "Bot is disabled.\n" );
}

$wpapi->login($user,$pass);

date_default_timezone_set('UTC');//Use UTC time.

//Define the text
$sandboxtext = "{{Please leave this line alone (sandbox heading)}}\n<!-- Hello! Feel free to try your formatting and editing skills below this line. As this page is for editing experiments, this page will automatically be cleaned every 12 hours. -->";
$sandboxtalktext = "{{Please leave this line alone (sandbox talk heading)}}\n<!-- Hello!  Feel free to try your formatting and editing skills below this line.  As this page is for editing experiments, this page will automatically be cleaned every 12 hours. -->";
$introtext = "{{Please leave this line alone}}\n<!-- Feel free to change the text below this line. No profanity, please. -->";
$xXtext = "<noinclude>\nThis sandbox is itself a template.  This sandbox is for experimenting with templates.\n{{Please leave this line alone (template sandbox heading)}}\n</noinclude>\n\nIf you defined parameters such as <tt><nowiki>{{Template sandbox|First|Second|name=\"Named\"}}</nowiki></tt>:\n;First:{{{1}}}\n;Second:{{{2}}}\n;Name:{{{name}}}\n\n----\n<!-- Hello!  Feel free to try your formatting and editing skills below this line.  As this page is for editing experiments, this page will automatically be cleaned every 12 hours. -->";
$tstext = "<noinclude>\nThis sandbox is itself a template.  This sandbox is for experimenting with templates.\n{{Please leave this line alone (template sandbox heading)}}\n</noinclude>\n\nIf you defined parameters such as <tt><nowiki>{{Template sandbox|First|Second|name=\"Named\"}}</nowiki></tt>:\n;First:{{{1}}}\n;Second:{{{2}}}\n;Name:{{{name}}}\n\n----";
$tutorialtext = "{{Please leave this line alone (tutorial sandbox heading)}}\n<!-- Hello!  Feel free to try your formatting and editing skills below this line.  As this page is for editing experiments, this page will automatically be cleaned every 12 hours. -->";
$tutorialtalktext = "{{Please leave this line alone (sandbox talk heading)}}\n<!-- Hello!  Feel free to try your formatting and editing skills below this line.  As this page is for editing experiments, this page will automatically be cleaned every 12 hours. -->";
$hour = date("%H"); 
$minute = date("%i");
$touse = $wpq->getpage('User:X!/Sandbots.css');
$touse = explode("|", $touse);

$wpi->forcepost('User:'.$user.'/Source','The following is automatically generated by [[User:'.$user.'|'.$user."]].\n\n<pre>" . htmlentities(file_get_contents('sandbox.bot.php'))."</pre>",'Automated source upload.');//Post the source code
foreach ($touse as $i) {
        $pagetext = array (
        	"Wikipedia:Sandbox"=>$sandboxtext,
        	"Wikipedia talk:Sandbox"=>$sandboxtalktext,
        	"Wikipedia:Introduction"=>$introtext,
        	"Template:X1"=>$xXtext,
        	"Template talk:X1"=>$tutorialtalktext,
        	"Template:X2"=>$xXtext,
        	"Template talk:X2"=>$tutorialtalktext,
        	"Template:X3"=>$xXtext,
        	"Template talk:X3"=>$tutorialtalktext,
        	"Template:X4"=>$xXtext,
        	"Template talk:X4"=>$tutorialtalktext,
        	"Template:X5"=>$xXtext,
        	"Template talk:X5"=>$tutorialtalktext,
        	"Template:X6"=>$xXtext,
        	"Template talk:X6"=>$tutorialtalktext,
        	"Template:X7"=>$xXtext,
        	"Template talk:X7"=>$tutorialtalktext,
        	"Template:X8"=>$xXtext,
        	"Template talk:X8"=>$tutorialtalktext,
        	"Template:X9"=>$xXtext,
        	"Template talk:X9"=>$tutorialtalktext,
        	"Template:Template sandbox"=>$tstext,
        	"Wikipedia:Tutorial (Editing)/sandbox"=>$tutorialtext,
        	"Wikipedia talk:Tutorial (Editing)/sandbox"=>$tutorialtalktext,
        	"Wikipedia:Tutorial (Formatting)/sandbox"=>$tutorialtext,
        	"Wikipedia talk:Tutorial (Formatting)/sandbox"=>$tutorialtalktext,
        	"Wikipedia:Tutorial (Wikipedia links)/sandbox"=>$tutorialtext,
        	"Wikipedia talk:Tutorial (Wikipedia links)/sandbox"=>$tutorialtalktext,
        	"Wikipedia:Tutorial (External links)/sandbox"=>$tutorialtext,
        	"Wikipedia talk:Tutorial (External links)/sandbox"=>$tutorialtalktext,
        	"Wikipedia:Tutorial (Keep in mind)/sandbox"=>$tutorialtext,
        	"Wikipedia talk:Tutorial (Keep in mind)/sandbox"=>$tutorialtalktext
        );
        $isenabled = explode("=", $i);
        $pagetitle = $isenabled[0];
        if (strpos($isenabled[1], $user) !== false) {
                $currtext = $wpq->getpage($pagetitle);
                if (
                	(
                		strpos($currtext, $pagetext[$pagetitle]) === false && 
                		( 
                			$pagetitle == "Wikipedia:Sandbox" || 
                			$pagetitle == "Wikipedia talk:Sandbox" || 
                			$pagetitle == "Wikipedia:Introduction"
                		)
                	) || 
                	(
                		($hour == 00 || $hour == 12 || $hour  == 24) && 
                		(00 <= $minute && $minute >= 02)
                	)
                ) {
                        echo "Time to clean $pagetitle!\n";
                        $wpi->post($pagetitle,$pagetext[$pagetitle],'Clearing the sandbox ([[WP:BOT|BOT]] EDIT)');
                }
        }
}
?>
