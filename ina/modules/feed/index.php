<?php

header("Content-type: text/xml; charset=utf-8");

$feed = $mySQL->query("SELECT * FROM `gb_ina` INNER JOIN `gb_blogfeed` USING(`PageID`) INNER JOIN `gb_pages` USING(`PageID`) WHERE `published` LIKE 'Published' AND `created` < ".time()." GROUP BY `ID` ORDER BY `created` DESC LIMIT 100");
$cnt = count($feed);
$tpl = new XMLDocument("feed.xml");
$channel = $tpl->xpath("//channel[1]")->item(0);
for($i=0; $i<$cnt; $i++){
	$url=PROTOCOL."://".$config->domain."/".$feed[$i]['ID']."/".Wordlist::translite($feed[$i]['header'], "-", true);
	$canonical=PROTOCOL."://".$config->domain."/".$feed[$i]['language']."/".$feed[$i]['ID'];
	
	$author = $mySQL->single_row("SELECT `Name` FROM `gb_staff` INNER JOIN `gb_community` USING(`CommunityID`) WHERE `UserID`=".$feed[$i]['UserID']." LIMIT 1");
	
	$guid = $tpl->create("guid", $canonical);
	$guid->setAttribute("isPermaLink","true");
	
	$item = $tpl->create("item");
	$item->appendChild($tpl->create("title", $feed[$i]['header']));
	$item->appendChild($tpl->create("link", $url));
	$item->appendChild($guid);
	$item->appendChild($tpl->create("pubDate", date("c", $feed[$i]['created'])));
	$item->appendChild($tpl->create("author", $author['Name']));
	$item->appendChild($tpl->create("description", $feed[$i]['subheader']));
	
	$content = $tpl->create("content:encoded");
	$content->appendChild($tpl->createCDATASection(gzdecode($feed[$i]['content'])));
	
	$item->appendChild($content);
	$channel->appendChild($item);
}

$page = (STRING)$tpl;

?>