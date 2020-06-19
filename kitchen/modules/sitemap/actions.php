<?php

$brancher->auth(array("sitemap")) or die("Access denied!");

switch(SUBPAGE){
	case "reload-tree":
		$rows = $mySQL->tree("SELECT `parent`,`name`,`language`,`Published` FROM `gb_sitemap` WHERE `language` LIKE '".PARAMETER."'", "name", "parent");
		$tree = staticTree($rows);
		print("<a class='tree-root-item' href='/sitemap/".PARAMETER."'>Root</a>".$tree);
	break;
	case "add-page":
		$p = JSON::load('php://input');
		$PageID = $mySQL->insert("gb_pages", array("created"=>time(), "modified"=>time(), "type"=>$p['type']));
		if(empty($PageID)){
			print("unknow error");
		}else{
			$mySQL->insert("gb_sitemap", array("PageID"=>$PageID, "parent"=>$p['parent'], "name"=>$p['name'], "soundex"=>soundex($p['name']), "language"=>$p['language']));
			$page = $mySQL->single_row("SELECT `PageID` FROM `gb_sitemap` WHERE `name` LIKE '".$p['name']."' AND `language` LIKE '".$p['language']."' LIMIT 1");
			if(empty($page)){
				print("unknow error");
			}else{
				$mySQL->single_row("INSERT INTO `gb_static` SET PageID=".$PageID);
				print($PageID);
			}
		}
	break;
	case "remove-page":
		$deleted = $mySQL->query("DELETE FROM `gb_pages` WHERE `PageID`=".PARAMETER." LIMIT 1");
		print($deleted);
	break;
	case "save-description":
		$data = file_get_contents('php://input');
		$saved = $mySQL->update("gb_static",  ["description"=>$mySQL->escape_string($data)], "`PageID`=".PARAMETER);
		print $saved;
	break;
	case "save-subheader":
		$data = file_get_contents('php://input');
		$saved = $mySQL->update("gb_sitemap",  ["subheader"=>$mySQL->escape_string($data)], "`PageID`=".PARAMETER);
		print $saved;
	break;
	case "save-header":
		$data = file_get_contents('php://input');
		$saved = $mySQL->update("gb_sitemap",  ["header"=>$mySQL->escape_string($data)], "`PageID`=".PARAMETER);
		print $saved;
	break;
	case "save-context":
		$data = file_get_contents('php://input');
		$saved = $mySQL->update("gb_static",  ["context"=>$mySQL->escape_string($data)], "`PageID`=".PARAMETER);
		print $saved;
	break;
	case "save-metadata":
		$p = JSON::load('php://input');
		
		$time = time();		
		if(empty($p['id'])){
			$material = $mySQL->single_row("SELECT * FROM `gb_sitemap` WHERE `name` LIKE '".$p['name']."' AND `language` LIKE '".$p['language']."' AND `parent` LIKE '".$p['parent']."' LIMIT 1");
			if(empty($material)){
				$p['id'] = $mySQL->insert("gb_pages", ["created"=>$time, "modified"=>$time, "type"=>$p['type']]);
				$mySQL->insert("gb_sitemap", array( 
				"PageID"=>$p['id'],
				"name"=>$p['name'],
				"header"=>$p['header'],
				"subheader"=>$p['subheader'],
				"language"=>$p['language'],
				"parent"=>$p['parent'],
				"soundex"=>soundex($p['name']),
				"preview"=>$p['preview'],
				"published"=>$p['published']
				));
			}else{
				$key = "/".$p['parent']."/".$p['title'].".".$p['language'];
				$answer[$key] = sprintf("%'.".(48 - strlen($key))."s <span class='red'>already exists!</span>", "");
				exit(JSON::stringify($answer));
			}
		}else{
			$mySQL->query("UPDATE `gb_pages` SET `type`='".$p['type']."', `modified`=".$time." WHERE `PageID`=".$p['id']." LIMIT 1");
			$mySQL->query("UPDATE `gb_sitemap` SET 
			`name`='".$p['name']."',
			`language`='".$p['language']."',
			`parent`='".$p['parent']."',
			`soundex`='".soundex($p['name'])."',
			`preview`='".$p['preview']."',
			`header`='".$p['header']."',
			`subheader`='".$p['subheader']."',
			`published`='".$p['published']."'
			WHERE `PageID`=".$p['id']." LIMIT 1");
		}	
		
		$answer = array();
			
		$sitemap = $mySQL->single_row("SELECT `PageID`,`header`,`published`,`language`,`name`,`parent`,`subheader`,`preview` FROM `gb_sitemap` WHERE `PageID` = ".$p['id']." LIMIT 1");
		
		foreach($sitemap as $key=>$val){
			if($p[$key]==$val){
				$answer[$key] = sprintf("%'.".(62 - strlen($key))."s - <span class='green'>Ok</span>", $val);
			}else $answer[$key] = sprintf("%'.".(58 - strlen($key))."s - <span class='red'>Failed</span>", $val);
		}
		$answer['PageID'] = $p['id'];
		$p['options'] = JSON::encode($p['options']);
		$mySQL->query("
		INSERT INTO `gb_static` SET 
			`PageID`=".$p['id'].", 
			`module`='".$p['module']."', 
			`template`='".$p['template']."',
			`context`='".$p['context']."',
			`description`='".$p['description']."',
			`optionset`='".$p['options']."'
		ON DUPLICATE KEY UPDATE
			`module`='".$p['module']."', 
			`template`='".$p['template']."',
			`context`='".$p['context']."',
			`description`='".$p['description']."',
			`optionset`='".$p['options']."'
		");
		$static = $mySQL->single_row("SELECT `module`,`template` FROM `gb_static` WHERE `PageID` = ".$p['id']." LIMIT 1");
		foreach($static as $key=>$val){
			if($p[$key]==$val){
				$answer[$key] = sprintf("%'.".(62 - strlen($key))."s - <span class='green'>Ok</span>", $val);
			}else $answer[$key] = sprintf("%'.".(58 - strlen($key))."s - <span class='red'>Failed</span>", $val);
		}
		print(JSON::stringify($answer));
	break;
	case "save-content":
		$mySQL->update("gb_pages", array("modified"=>time()), "`PageID`=".PARAMETER);

		$data = gzencode(file_get_contents('php://input'));
		$mySQL->update("gb_static",  array("content"=>$mySQL->escape_string($data)), "`PageID`=".PARAMETER);
		
		$saved = $mySQL->single_row("SELECT `content` FROM `gb_static` WHERE `PageID`=".PARAMETER." LIMIT 1");
		if(strcmp($data, $saved['content'])){
			print("Failed save");
		}else print(PARAMETER);
	break;
	default:break;
}

/****************************************************************************/

function staticTree(&$items, $offset="root"){
	if(is_array($items[$offset])){
		$result.="<div class='root'>";
		foreach($items[$offset] as $key=>$val){
			$result.="<a href='/sitemap/".$val['language']."/".$val['name']."' class='".(($val['Published']==="Published")?"green ":"")."tree-".(($val['name']===PAGE) ? "root-" : "")."item'>".$val['name']."</a>";
			$result .= staticTree($items, $key);
		}
		$result.="</div>";
		return $result;
	}
}

?>