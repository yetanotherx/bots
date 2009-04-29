<?PHP
 
//Load includes
include '/home/soxred93/wikibot.classes.php';
$pass = file_get_contents('/home/soxred93/.password');
$user = "SoxBot";
 
//Setting needed vars
$wpapi  = new wikipediaapi;
$wpq    = new wikipediaquery;
$wpi    = new wikipediaindex;
 
//Logging in
$wpapi->login($user,$pass);
echo "Logged In!\n";
 
///////////////Wikipedia:AutoWikiBrowser/User_talk_templates///////////////
$UTT = $wpq->getpage('Wikipedia:AutoWikiBrowser/User_talk_templates'); //Get AWB list
//remove linebreaks
$list = ereg_replace ("\n",'',$UTT);
//Change ']]# [[' to '|'
$list = str_ireplace(']]# [[','|',$list);
//Remove the leading '# [['
$list = str_ireplace('# [[','',$list);
//Remove the ending ']]'
$list = str_ireplace(']]','',$list);
//Remove AWB header
$list = str_ireplace('<noinclude>{{pp-semi-protected|small=yes}}</noinclude>{{AWB}}{{shortcut|WP:AWB/UTT}}This page contains templates that AWB will automatically substitute on user talk pages (please make sure that general fixes are enabled, and this will be done when processing user talk pages).','',$list);
///////////////Wikipedia:AutoWikiBrowser/User_talk_templates///////////////
 
//Split the list up
$list = explode("|", $list);

foreach ($list as $template)
{
//Getting Transluctions
	echo ($template);
	$ei = $wpapi->embeddedin($template);
	foreach ($ei as $data)
	{
 
//Getting page names
		$page = ($data['title']);
 
//Skipping if article is a subpage
		if( (eregi("/",$page)) || (substr($page, 0, 10) == "User Talk:"))
		{
			echo "SKIP: $page is a subpage\n";
		}
 
//Else posting
		else
		{
 
//Only post to user talk pages
			if( substr($page, 0, 10) == "User Talk:")
			{
				echo "POST: substing $template on $page\n";
 
//Grabbing Page content to work with
				$text = $wpq->getpage($page);
 
//Set main vars
					$subst1 = $template;					//As in list
					$subst1a = str_ireplace('_',' ',$subst1);		//1 _ to SPACE
					$subst1b = str_ireplace(' ','_',$subst1);		//1 SPACE to _
 
 
//Subst all as they are in list with _ as SPACE and SPACE as _
				$text = str_ireplace("{{$subst1}}","{subst:$subst1}",$text);
				$text = str_ireplace('{{'.$subst1.'|','{{subst:'.$subst1.'|',$text);
				$text = str_ireplace("{{$subst1a}}","{subst:$subst1a}",$text);
				$text = str_ireplace('{{'.$subst1a.'|','{{subst:'.$subst1a.'|',$text);
				$text = str_ireplace("{{$subst1b}}","{subst:$subst1b}",$text);
				$text = str_ireplace('{{'.$subst1b.'|','{{subst:'.$subst1b.'|',$text);
 
//If origional contains Template: subst all without it also
				if( eregi("Template:",$template))
				{
					$templaten = str_ireplace('Template:','',$template);
 
					$subst2 = $templaten;				//rm - Template:
					$subst2a = str_ireplace('_',' ',$subst2);		//2 _ to SPACE
					$subst2b = str_ireplace(' ','_',$subst2);		//2 SPACE to _
 
//Subst all minus Template: with _ as SPACE and SPACE as _
					$text = str_ireplace("{{$subst2}}","{subst:$subst2}",$text);
					$text = str_ireplace('{{'.$subst2.'|','{{subst:'.$subst2.'|',$text);
					$text = str_ireplace("{{$subst2a}}","{subst:$subst2a}",$text);
					$text = str_ireplace('{{'.$subst2a.'|','{{subst:'.$subst2a.'|',$text);
					$text = str_ireplace("{{$subst2b}}","{subst:$subst2b}",$text);
					$text = str_ireplace('{{'.$subst2b.'|','{{subst:'.$subst2b.'|',$text);
				}
 
//Post data with all substed
				$esum = "Substing [[$template]] (BOT EDIT)";
				if( empty( $text ) ) {
    				continue;
				}

				$wpi->post($page,$text,$esum,true);
 
			}
			else
			{
				echo "SKIP: $page is not a user talk page\n";
			}
		}
		unset($templeate,$text,$page,$esum,$templeaten,$subst1,$subst1a,$subst1b,$subst2,$subst2a,$subst2b);
	}
}
 
?>
