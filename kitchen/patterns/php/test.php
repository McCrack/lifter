$created = date("c", $page['created']);
	$preview = getimagesize($page['preview']);
	$canonical = PROTOCOL."://".$config->domain."/".$page['language']."/".$page['ID'];