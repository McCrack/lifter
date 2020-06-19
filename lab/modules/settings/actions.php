<?php

$brancher->auth(array("settings")) or die("Access denied!");

switch(SUBPAGE){
	case "save":
		$data = $HTTP_RAW_POST_DATA;
		if(SUBPARAMETER){
			$data = JSON::parse($data);
			$brancher = new Brancher("../".PARAMETER."/brancher.json");
			$map = $brancher->createMap(SUBPARAMETER);
			if(empty($map)){
				$brancher->register[SUBPARAMETER]['options'] = $data[SUBPARAMETER];
			}else{
				$module = &$brancher->getModule($brancher->register, $map);
				$module['options'] = $data[SUBPARAMETER];
			}			
			JSON::save("../".PARAMETER."/brancher.json", $brancher->register);
		}else $saved = file_put_contents("../".PARAMETER."/config.init", $data);
		print($saved);
	break;
	case "change-branch":
		$p = JSON::load('php://input');
		$brancher = new Brancher("../".$p['subdomain']."/brancher.json");
		$map = $brancher->createMap($p['parent']);
		$parent = &$brancher->getModule($brancher->register, $map);
		
		$map = $brancher->createMap($p['module']);
		if(empty($map)){
			$map[] = $p['module'];
			$parent['branch'][$p['module']] = ["options"=>[], "branch"=>[]];
		}else{
			$parent['branch'][$p['module']] = &$brancher->getModule($brancher->register, $map);
			$brancher->dropBranch($brancher->register, $map);
		}		
		JSON::save("../".$p['subdomain']."/brancher.json", $brancher->register);
	
		$map = $brancher->createMap($p['module']);
		print(brancherTree($brancher->register, $map));
	break;
	default:break;
}

/********************************************************************/
	
function brancherTree(&$branch, &$map){
	foreach($branch as $key=>$item){
		if($key===current($map)){
			$checked = (next($map)) ? "checked" : "disabled";
		}else $checked = "item";
		$list.="<label class='".$checked."' data-href='".$key."'><input type='radio' name='branch' value='".$key."' ".$checked."> ".$key."</label>";
		if(count($item['branch'])>0){
			$list .= "<div class='root'>".brancherTree($item['branch'], $map)."</div>";
		}
	}
	return $list;
}

?>