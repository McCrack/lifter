<?php

$brancher->auth(array("wordlist")) or die("Access denied!");

switch(SUBPAGE){
	case "create":
		$p = JSON::load('php://input');
		mkpath("../".$p['domain']."/localization");
		if(file_exists("../".$p['domain']."/localization/".$p['name'].".json")){
			print("Wordlist already exists.");
		}else{
			$created = file_put_contents("../".$p['domain']."/localization/".$p['name'].".json", '{"'.DEFAULT_LANG.'":{"":""}}');
			print($created);
		}
	break;
	case "save":
		$data = file_get_contents('php://input');
		$saved = file_put_contents("../".PARAMETER."/localization/".SUBPARAMETER.".json", $data);
		print($saved);
	break;
	
	case "remove":
		$removed = unlink("../".PARAMETER."/localization/".SUBPARAMETER.".json");
		print($removed);
	break;
	
	case "showkey":
		$url = parse_url(file_get_contents('php://input'));
		$path = explode("/", $url['path']);
		
		$wordlist = JSON::load("../".$path[1]."/localization/".$path[2].".json");
		$table = "
		<input type='hidden' name='path' value='".$path[1]."/".$path[2]."'>
		<table width='100%' rules='cols' cellpadding='4' cellspacing='0' bordercolor='#999' style='outline:1px solid #CCC'>
		<thead><tr bgcolor='#555' style='color:#EEE'><th width='60'>Key</th><td align='center'>".$path[3]."</td></tr></thead><tbody>";
		$color=16777215;
		foreach($wordlist as $lang=>$words){
			$table .= "<tr bgcolor='#".dechex($color^=1381653)."'><th>".$lang."</th><td contenteditable='true'>".$words[$path[3]]."</td></tr>";
		}
		$table .= "<tbody></table>";
		print($table);
	break;
	case "savekey":
		$p = JSON::load('php://input');
		$url = parse_url($p['path']);
		$path = explode("/", $url['path']);
		$wordlist = JSON::load("../".$path[0]."/localization/".$path[1].".json");
		foreach($wordlist as $key=>&$lang){
			$lang = array_merge($lang, $p['wordlist'][$key]);
		}
		JSON::save("../".$path[0]."/localization/".$path[1].".json", $wordlist);
	break;
	
	default:break;
}

?>