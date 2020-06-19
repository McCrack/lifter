<?php

/* Metadata collection ***************************************************/
	
	$canonical = PROTOCOL."://".$config->domain."/".$page['language']."/".$page['ID'];
	$author = $mySQL->single_row("SELECT * FROM `gb_staff` LEFT JOIN `gb_community` USING(`CommunityID`) WHERE `UserID`=".$page['UserID']." LIMIT 1");

	
/* Template ********************************************************/

	ob_start(); 

	include_once("../".$subdomain."/themes/".$config->theme."/".(empty($template) ? $config->{"default template"} : $template).".html");

	$tpl = new HTMLDocument(ob_get_contents());
	ob_end_clean();
	
/* Fitting *********************************************************/

	
	/* Adsense *************/
	$ads = $tpl->xpath("//div[@class='adsense']");
	for($i=$ads->length; $i--;){
		$ads->item($i)->parentNode->removeChild($ads->item($i));
	}
	/* Headers H3 **********/
	$h3 = $tpl->xpath("//h3");
	for($i=$h3->length; $i--;){
		$h3->item($i)->parentNode->replaceChild($tpl->create("h2", $h3->item($i)->nodeValue), $h3->item($i));
	}
	/* Headers H4 **********/
	$h4 = $tpl->xpath("//h4");
	for($i=$h4->length; $i--;){
		$h4->item($i)->parentNode->replaceChild($tpl->create("p", $h4->item($i)->nodeValue), $h4->item($i));
	}
	/* Images **************/
	$img = $tpl->xpath("//img");
	for($i=$img->length; $i--;){
		if($img->item($i)->parentNode->nodeName!="figure"){
			$figure = $tpl->create("figure");
			$img->item($i)->parentNode->insertBefore($figure, $img->item($i));
			$figure->appendChild($img->item($i));
		}
	}
	
/* Save page *******************************************************/

	$mySQL->single_row("
	INSERT INTO `gb_".$config->subdomain."` SET 
		`PageID`=".$page['PageID'].",
		`content`='".$mySQL->escape_string(gzencode($tpl))."'
	ON DUPLICATE KEY UPDATE
		`content`='".$mySQL->escape_string(gzencode($tpl))."'
	");
	
	$page = (string)$tpl;
	
?>