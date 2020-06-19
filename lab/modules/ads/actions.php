<?php

switch(SUBPAGE){
	case "submit":
		$chatfeed = new XMLDocument("modules/ads/adsfeed.xml");
		
		$author = $chatfeed->create("span", USER_LOGIN, array("class"=>"msg-author"));
		$time = $chatfeed->create("time", "[ ".date("d F, H:i:s")." ]", array("class"=>"msg-published","dateTime"=>date("c")));
		$body = $chatfeed->create("div", "", array("class"=>"msg-body"));
		$body->appendChild($chatfeed->createCDATASection(file_get_contents('php://input')));
		
		$item = $chatfeed->create("div", "", array("class"=>"msg-item"));
		$item->appendChild($author);
		$item->appendChild($time);
		$item->appendChild($body);
		
		$chatfeed->documentElement->appendChild($item);
		
		$messages = $chatfeed->xpath("//div[@class='msg-item']");
		if($messages->length > 100){
			$chatfeed->documentElement->removeChild($messages->item(0));
		}
		$chatfeed->save("modules/ads/adsfeed.xml");
		print($chatfeed->saveHTML());
	break;
	case "reload":
		$chatfeed = new XMLDocument("modules/ads/adsfeed.xml");
		print($chatfeed->saveHTML());
	break;
	default:break;
}

?>