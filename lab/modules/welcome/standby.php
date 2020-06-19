<?php
	switch(SUBPAGE){
		case "get":
			$standby = JSON::load("core/standby.json");
			print(JSON::stringify($standby[PARAMETER]));
		break;
		case "set":
			$p = JSON::load('php://input');
			$standby = JSON::load("core/standby.json");
			$standby[PARAMETER][$p['key']] = $p['value'];
			JSON::save("core/standby.json", $standby);
		break;
		default:
		break;
	}
?>