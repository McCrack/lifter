<?php

$feed = $mySQL->query("SELECT `PageID`,`preview` FROM `gb_sitemap`");

foreach($feed as $itm){
	print "<div>".$itm['preview']."</div>";
	//$itm['preview'] = str_replace("http://lab.","//",$itm['preview']);
	//$upd = $mySQL->query("UPDATE `gb_sitemap` SET `preview`='".$itm['preview']."' WHERE `PageID`=".$itm['PageID']." LIMIT 1");
	//print("<div>".$itm['PageID'].": ".$upd."</div>");
}

?>