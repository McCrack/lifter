<?php

$brancher->auth() or die("Access denied!");

switch(SUBPAGE){
	case "reload":
		$manual = $mySQL->single_row("SELECT `content` FROM `gb_documentation` WHERE `title` LIKE '".PARAMETER."' AND `language` LIKE '".SUBPARAMETER."' LIMIT 1");
		print($manual['content']);
	break;
	case "save-or-create":
		$data = file_get_contents('php://input');
		$data = $mySQL->escape_string($data);
		$saved = $mySQL->query("INSERT INTO `gb_documentation` SET `title`='".PARAMETER."', `language`='".SUBPARAMETER."', `content`='".$data."' ON DUPLICATE KEY UPDATE `content`='".$data."'");
		print($saved);
	break;
	case "save":
		$data = file_get_contents('php://input');
		$data = $mySQL->escape_string($data);
		$saved = $mySQL->query("UPDATE `gb_documentation` SET `content`='".trim($data)."' WHERE `id`=".PARAMETER." LIMIT 1");
		print($saved);
	break;
	case "create":
		$p = JSON::load('php://input');
		$saved = $mySQL->insert("gb_documentation", array("title"=>$p['name'], "language"=>$p['language'], "pid"=>$p['pid'], "content"=>"<h1>".$p['name']."</h1><hr><p><br></p>"));
		print($saved);
	break;
	default: break;
}

?>