<?php

	header('Content-Type: text/javascript"; charset=utf-8', true);

	$language = USER_LANG;
	
	if(is_string($_GET['d']) && file_exists("../lab/localization/".$_GET['d'].".json")){
		$wl = JSON::load("../lab/localization/".$_GET['d'].".json");
		print "translate.add(".JSON::stringify($wl[$language]).", '".$_GET['d']."');\n";
	}else{
		if(is_array($_GET['d'])){
			foreach($_GET['d'] as $wordlist){
				if(file_exists("../lab/localization/".$wordlist.".json")){
					$wl = JSON::load("../lab/localization/".$wordlist.".json");
					print "translate.add(".JSON::stringify($wl[$language]).", '".$wordlist."');\n";
				}
			}
		}else{
			foreach(scandir("../lab/localization") as $wordlist){
				if(is_file("../lab/localization/".$wordlist)){
					$wordlist = explode(".", $wordlist);
					if(end($wordlist)==="json"){
						$wl = JSON::load("../lab/localization/".$wordlist);
						print "translate.add(".JSON::stringify($wl[$language]).", '".reset($wordlist)."');\n";
					}
				}
			}
		}
	}
?>