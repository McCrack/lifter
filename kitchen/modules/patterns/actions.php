<?php

//$brancher->auth(["patterns"]) or die("Access denied!");

switch(SUBPAGE){
	case "get-pattern":
		$data = file_get_contents($_GET['path']);
		print($data);
	break;
	case "save-pattern":
		$data = file_get_contents('php://input');
		$path = explode("/", $_GET['path']);
		file_put_contents($_GET['path']."/".PARAMETER.".".$path[1], $data);
		print( patterns_tree("patterns/".$path[1]) );
	break;
	case "create-folder":
		$path = file_get_contents('php://input');
		mkpath($path);	// Make new folder
		print( patterns_tree("patterns/".PARAMETER) );
	break;
	case "remove":
		$path = explode("/", $_GET['path']);
		if(PARAMETER==="folder"){
			 deletedir($_GET['path']);
		}elseif(file_exists($_GET['path']."/".PARAMETER.".".$path[1])){
			unlink($_GET['path']."/".PARAMETER.".".$path[1]);
		}
		print( patterns_tree("patterns/".$path[1]) );
	break;
	default:break;
}

/*******************************************************************/

function patterns_tree($path){
	$items = $dirs = "";
	foreach(scandir($path) as $file){
		if(is_file($path."/".$file)){
			$items .= "<a class='pattern-file' data-path='".$path."'>".$file."</a>";
		}elseif(is_dir($path."/".$file) && ($file!="." && $file!="..")){
			$dirs .= "<label data-translate='textContent' class='pattern-folder' data-path='".$path."/".$file."'>".$file."</label><div class='root'>".patterns_tree($path."/".$file)."</div>";
		}
	}
	return $dirs."".$items;
}

?>