<?php

$brancher->auth() or die("Access denied!");

switch(SUBPAGE){
	case "reload-tree":
		$rows = $mySQL->tree("SELECT * FROM `gb_manuals` WHERE `language` LIKE '".PARAMETER."'", "id", "pid");
		print staticTree($rows);
	break;
	case "reload":
		$manual = $mySQL->single_row("SELECT `content` FROM `gb_manuals` WHERE `title` LIKE '".PARAMETER."' AND `language` LIKE '".SUBPARAMETER."' LIMIT 1");
		print($manual['content']);
	break;
	case "save-or-create":
		$data = file_get_contents('php://input');
		$data = mysql_escape_string($data);
		$saved = $mySQL->query("INSERT INTO `gb_manuals` SET `title`='".PARAMETER."', `language`='".SUBPARAMETER."', `content`='".$data."' ON DUPLICATE KEY UPDATE `content`='".$data."'");
		print($saved);
	break;
	case "save":
		$data = file_get_contents('php://input');
		$data = mysql_escape_string($data);
		$saved = $mySQL->query("UPDATE `gb_manuals` SET `content`='".trim($data)."' WHERE `id`=".PARAMETER." LIMIT 1");
		print($saved);
	break;
	case "create":
		$p = JSON::load('php://input');
		$saved = $mySQL->insert("gb_manuals", array("title"=>$p['name'], "language"=>$p['language'], "pid"=>$p['pid'], "content"=>"<h1>".$p['name']."</h1><hr><p><br></p>"));
		print($saved);
	break;
	default: break;
}

/****************************************************************************/

function staticTree(&$items, $offset=0){
	if(is_array($items[$offset])){
		$result.="<div class='root'>";
		foreach($items[$offset] as $key=>$val){
			$result.="<a href='/manual/".$val['language']."/".$val['id']."' class='tree-item'>".$val['title']."</a>";
			$result .= staticTree($items, $key);
		}
		$result.="</div>";
		return $result;
	}
}

?>