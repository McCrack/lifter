<?php
	
	$brancher->auth(array("themes")) or die("Access denied!");
	
switch(SUBPAGE){
	case "load":
		$path = file_get_contents('php://input');
		include_once($path);
	break;
	case "save-file":
		$data = file_get_contents('php://input');
		file_put_contents($_GET['p'], $data);
		print($saved);
	break;
	case "create-theme":
		$p = JSON::load('php://input');
		$path = "../".$p['subdomain']."/themes/".$p['name'];
		mkpath($path);
		copyFolder("modules/themes/templates/".$p['template'], $path);
	break;
	case "create-file":
		$filename = file_get_contents('php://input');		// Get file name
		$path = explode("/", $_SERVER['HTTP_REFERER']);
		list($path[3], $path[4]) = [$path[4], $path[3]];
		$path = "../".implode("/", array_slice($path, 3) );
		file_put_contents($path."/".$filename, "\n");
		print($path."/".$filename);
	break;
	case "create-folder":
		$folder = file_get_contents('php://input');			// Get folder name
		$path = explode("/", $_SERVER['HTTP_REFERER']);
		list($path[3], $path[4]) = [$path[4], $path[3]];
		$path = "../".implode("/", array_slice($path, 3) );
		mkpath($path."/".$folder);							// Make new folder
	break;
	case "rename":
		$p = JSON::load('php://input');	// Get old and new names
		rename($p['old'], $p['new']);	// Rename item
	break;
	case "unzip":
		$fullpath = file_get_contents('php://input');
		$path = implode("/", array_slice(explode("/", $fullpath), 0, -1));
		$type = mime_content_type($fullpath);
		if($type==="application/zip"){
			$zip = new ZipArchive;
			if($zip->open($fullpath)){
				print($zip->extractTo($path));
				$zip->close();
			}
		}
	break;
	case "remove":
		$p = JSON::load('php://input');		// Get items for remove
		foreach($p as $item){				// Traversal list
			if(is_dir($item)){
				deletedir($item);			// Remove folder
			}elseif(is_file($item)){
				unlink($item);				// Remove file
			}
		}
	break;
	case "import":
		$p = JSON::load('php://input');		// Get images list
		$path = explode("/", $_SERVER['HTTP_REFERER']);
		list($path[3], $path[4]) = [$path[4], $path[3]];
		$path = "../".implode("/", array_slice($path, 3) );
		$status = 1;
		foreach($p as $url){
			$file = file_get_contents($url);
			$filename = Wordlist::translite(basename($url), "_");
			$status &= (BOOL)file_put_contents($path."/".$filename, $file);
		}
		print($status);
	break;
	case "upload":
		$data = file_get_contents('php://input');
		$path = explode("/", $_SERVER['HTTP_REFERER']);
		list($path[3], $path[4]) = [$path[4], $path[3]];
		$path = "../".implode("/", array_slice($path, 3) );
		if(SUBPARAMETER){				
			$file = fopen($path."/".PARAMETER, "a");
			fwrite($file, $data);
			fclose($file);
		}else file_put_contents($path."/".PARAMETER, $data);
	break;
	default:break;
}
	
/**********************************************************************************/
	
function copyFolder($source, $dest){
	foreach(scandir($source) as $file){
		if(is_file($source."/".$file)){
			copy($source."/".$file, $dest."/".$file);
		}elseif(is_dir($source."/".$file) && $file!="." && $file!=".."){
			mkpath($dest."/".$file);
			copyFolder($source."/".$file, $dest."/".$file);
		}
	}
}
?>