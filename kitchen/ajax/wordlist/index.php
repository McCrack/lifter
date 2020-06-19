<?php

	header('Content-Type: text/javascript"; charset=utf-8', true);

	$language = USER_LANG;
	
	if(is_string($_GET['d']) && file_exists("../kitchen/localization/".$_GET['d'].".json")){
		$wl = JSON::load("../kitchen/localization/".$_GET['d'].".json");
		print "translate.add(".JSON::stringify($wl[$language]).", '".$_GET['d']."');\n";
	}else{
		if(is_array($_GET['d'])){
			foreach($_GET['d'] as $wordlist){
				if(file_exists("../kitchen/localization/".$wordlist.".json")){
					$wl = JSON::load("../kitchen/localization/".$wordlist.".json");
					print "translate.add(".JSON::stringify($wl[$language]).", '".$wordlist."');\n";
				}
			}
		}else{
			foreach(scandir("../kitchen/localization") as $wordlist){
				if(is_file("../kitchen/localization/".$wordlist)){
					$wordlist = explode(".", $wordlist);
					if(end($wordlist)==="json"){
						$wl = JSON::load("../kitchen/localization/".$wordlist);
						print "translate.add(".JSON::stringify($wl[$language]).", '".reset($wordlist)."');\n";
					}
				}
			}
		}
	}
?>