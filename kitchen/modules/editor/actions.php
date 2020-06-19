<?php

switch(SUBPAGE){
	case "google_ad_client":
		$config = new config("../".BASE_FOLDER."/config.init");
		print($config->{"google_ad_client"});
	break;
	case "module":
		include_once("modules/editor/modules/".PARAMETER."/box.php");
	break;
	default:break;
}

?>