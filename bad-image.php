<?php

define('PILLAR','PILLAR'); 
require_once('/home/soxred93/pillar/trunk/class.pillar.php');
require_once('/home/soxred93/textdiff/textdiff.php');

$pillar = Pillar::ini_launch('/home/soxred93/configs/bad-image.cfg');
$site = $pillar->cursite;

//START GENERATING TRANSCLUSIONS
$i = array();
$images = $site->get_embeddedin('Template:Badimage','500',$continue,'6|7');

foreach( $images as $image ) {
    $i[] = $image;
}
while( isset($images[499]) ) {
    $images = $site->get_embeddedin('Template:Badimage','500',$continue,'6|7');
    foreach( $images as $image ) {
	$i[] = $image;
    }
}
//END GENERATING TRANSCLUSIONS

//START GENERATING BAD IMAGE LIST
try {
	$bil = new Page($pillar->cursite,'MediaWiki:Bad image list');
} catch (PillarException $e) {
	die();
}
$bil = $bil->get_text();
preg_match_all('/\*\s\[\[\:(File\:(.*?))\]\]/i', $bil, $bad_images);
$bad_images = $bad_images[1];
print_r($bad_images);
//END GENERATING BAD IMAGE LIST

//START PROCESSING EACH IMAGE
foreach( $i as $image ) {
	if( in_array( str_replace('File talk','File',$image), $bad_images ) ) {
		continue;
	}
	else {
		try {
			$image_page_object = new Page($pillar->cursite,$image);
		} catch (PillarException $e) {
			continue;
		}
		$image_page = $image_page_object->get_text();
		$new_image_page = str_ireplace('{{badimage}}','',$image_page);
		echo getTextDiff('unified', $image_page, $new_image_page);
		
		if( $image_page == $new_image_page ) continue;
		try {
			$image_page_object->put($new_image_page,"Removing {{badimage}}, image is not on blacklist",true);
		} catch (PillarException $e) {
			continue;
		}
		continue;
	}
	
	if( str_replace('File talk','File',$image) != $image ) {
		try {
			$image_page_object = new Page($pillar->cursite,str_replace('File talk','File',$image));
			$image_page = $image_page_object->get_text();
		} catch (PillarException $e) {
			continue;
		}
		try {
			$image_talk_page_object = new Page($pillar->cursite,$image);
			$image_talk_page = $image_talk_page_object->get_text();
		} catch (PillarException $e) {
			continue;
		}
		
		//START REMOVAL FROM TALK PAGE
		$new_image_talk_page = str_ireplace('{{badimage}}','',$image_talk_page);
		echo getTextDiff('unified', $image_talk_page, $new_image_talk_page);
		
		if( $image_talk_page == $new_image_talk_page ) continue;
		try {
			$image_talk_page_object->put($new_image_talk_page,"Removing {{badimage}}, moving to main image page",true);
		} catch (PillarException $e) {
			continue;
		}
		
		//START ADDITION TO MAIN PAGE
		if( preg_match('/\{\{badimage/i', $image_page ) ) continue;
		$new_image_page = "{{badimage}}\n$image_page";
		echo getTextDiff('unified', $image_page, $new_image_page);
		
		if( $image_page == $new_image_page ) continue;
		try {
			$image_page_object->put($new_image_page,"Adding {{badimage}}",true);
		} catch (PillarException $e) {
			continue;
		}
	}
}
//END PROCESSING EACH IMAGE

//START GENERATING TRANSCLUSIONS
$i = array();
$images = $site->get_embeddedin('Template:Badimage','500',$continue,'6|7');

foreach( $images as $image ) {
    $i[] = $image;
}
while( isset($images[499]) ) {
    $images = $site->get_embeddedin('Template:Badimage','500',$continue,'6|7');
    foreach( $images as $image ) {
	$i[] = $image;
    }
}
//END GENERATING TRANSCLUSIONS

//START GENERATING BAD IMAGE LIST
try {
	$bil = new Page($pillar->cursite,'MediaWiki:Bad image list');
} catch (PillarException $e) {
	die();
}
$bil = $bil->get_text();
preg_match_all('/\*\s\[\[\:(File\:(.*?))\]\]/i', $bil, $bad_images);
$bad_images = $bad_images[1];
//END GENERATING BAD IMAGE LIST

//START GOING THROUGH BIL
foreach( $bad_images as $bad_image ) {
	try {
		$image = new Page($pillar->cursite,$bad_image);
	} catch (PillarException $e) {
		continue;
	}
	
	//if( !$image->get_exists() ) continue;
	
	$image_page = $image->get_text();
	if( preg_match('/\{\{badimage/i', $image_page ) ) continue;
	
	$new_image_page = "{{badimage}}\n$image_page";
	echo getTextDiff('unified', $image_page, $new_image_page);
		
	if( $image_page == $new_image_page ) continue;
	try {
		$image->put($new_image_page,"Adding {{badimage}}",true);
	} catch (PillarException $e) {
		continue;
	}
}
//END GOING THROUGH BIL

?>