<?php

define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');

$tosearch = array(
	'{{wikify}}',
	'{{orphan}}',
	'{{uncategorized}}',
	'{{uncategorised}}',
	'{{uncat}}',
	'{{uncategorizedstub}}',
	'{{cleanup}}',
	'{{clean-up}}',
	'{{unreferenced}}',
	'{{nosources}}',
	'{{unsourced}}',
	'{{source}}',
	'{{expand}}',
	'{{work in progress}}',
	'{{merge}}',
	'{{fact}}',
	'{{citation needed}}',
	'{{prove}}',
	'{{copy}}',
	'{{encopypaste}}',
	'{{en copy paste}}',
	'{{NPOV}}',
	'{{npov}}',
	'{{POV-check}}',
	'{{POV-Check}}',
	'{{POV}}',
);

$toreplace = array(
	'{{wikify|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{orphan|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{uncat|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{uncat|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{uncat|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{uncategorizedstub|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{cleanup|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{cleanup|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{nosources|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{nosources|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{nosources|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{nosources|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{expand|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{expand|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{merge|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{fact|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{fact|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{fact|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{encopypaste|1={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{encopypaste|1={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{encopypaste|1={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{NPOV|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{NPOV|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{NPOV|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{NPOV|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
	'{{NPOV|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}',
);

$togetembeddedin = array(
	'Template:Wikify',
	'Template:Orphan',
	'Template:Uncategorized',
	'Template:Uncategorizedstub',
	'Template:Cleanup',
	'Template:Unreferenced',
	'Template:Expand',
	'Template:Merge',
	'Template:Fact',
	'Template:Copy',
	'Template:NPOV',
);

$pillar = Pillar::ini_launch('/home/soxred93/configs/datefix.cfg');
//$pillar = Pillar::get_instance;
$site = $pillar->cursite;

$p = array();

foreach( $togetembeddedin as $template ) {
	$pages = $site->get_embeddedin($template,'500',$continue,0);
	
	foreach( $pages as $page ) {
		$p[] = $page;
	}
	while( isset($pages[499]) ) {
		$pages = $site->get_embeddedin($template,'500',$continue,0);
		foreach( $pages as $page ) {
			$p[] = $page;
		}
	}
}

$p = array_unique($p);
$out = '';

require_once('/home/soxred93/textdiff/textdiff.php');

$c = 1;
foreach ($p as $pg) {
	$page = new Page($site,$pg);
	$text = $page->get_text();
	
	$newtext = str_ireplace($tosearch, $toreplace, $text);
	
	$diff = getTextDiff('unified', $text, $newtext);
        
	if( $newtext != $text ) {
		$out = "== $pg ==\n<pre>".$diff->render($textdiff)."</pre>\n\n";
		echo $out."\n\n";
	    
	    try {
	        $page->put($newtext,"Dating maintenance tags (bot edit)",true);
	    } catch (PillarException $e) {
	        continue;
	    }
	    
		$c++;
		sleep(3);
	}
}

//$page = new Page($pillar->cursite,$toedit);
//$page->put($out,"Updating Admin Stats",true);

?>