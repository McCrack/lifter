<?php
	
$brancher->auth(["developer"]) or die("Access denied!");
	
switch(SUBPAGE){
	case "tree":
		$path = file_get_contents('php://input');
		$items = explode("/", $path);
		print buildTree($items);
	break;
	case "load-folder":
		$path = file_get_contents('php://input');
		foreach(scandir($path) as $file){
			$fullpath = $path."/".$file;
			if(is_dir($fullpath) && $file!="." && $file!=".."){
				$folders.="<span class='folder' data-path='".$fullpath."'>".$file."</span>";
			}elseif(is_file($fullpath)){
				$mime = explode("/", mime_content_type($fullpath));
				if(reset($mime)==="image"){
					$files .= "<span class='image' data-path='".$fullpath."'>".$file."</span>";
				}else $files .= "<span class='file' data-path='".$fullpath."'>".$file."</span>";
			}
		}
		print $folders." ".$files;
	break;
	case "copy":
		$p = JSON::load('php://input');
		$items = explode("/", $p['src']);
		if(is_file($p['src'])){
			copy($p['src'], $p['dest']."/".end( $items ));
		}elseif(is_dir($p['src'])) copyFolder($p['src'], $p['dest']."/".end( $items ));
		
		$items = explode("/", $p['dest']);
		print buildTree($items);
	break;
	case "move":
		$p = JSON::load('php://input');
		$items = explode("/", $p['src']);
		rename($p['src'], $p['dest']."/".end( $items ));
		
		$items = explode("/", $p['dest']);
		print buildTree($items);
	break;
	
	case "create-file":
		$fullpath = file_get_contents('php://input');		// Get file name
		file_put_contents($fullpath, "\n");
		$items = array_slice(explode("/", $fullpath), 0, -1);
		print buildTree($items);
	break;
	case "create-folder":
		$fullpath = file_get_contents('php://input');
		@mkpath($fullpath);
		$items = array_slice(explode("/", $fullpath), 0, -1);
		print buildTree($items);
	break;
	case "rename":
		$p = JSON::load('php://input');	// Get old and new names
		rename($p['old'], $p['new']);	// Rename item
	break;
	case "remove":
		$path = file_get_contents('php://input');
		if(is_dir($path)){
			deletedir($path);			// Remove folder
		}elseif(is_file($path)){
			unlink($path);				// Remove file
		}
	break;
	case "unzip":
		$fullpath = file_get_contents('php://input');
		$items = array_slice(explode("/", $fullpath), 0, -1);
		$type = mime_content_type($fullpath);
		if($type==="application/zip"){
			$zip = new ZipArchive;
			if($zip->open($fullpath)){
				$zip->extractTo( implode("/", $items) );
				$zip->close();
			}
		}
		print buildTree($items);
	break;
	case "save-file":
		$data = file_get_contents('php://input');
		file_put_contents($_GET['path'], $data);
		print($saved);
	break;
	case "download":
		$fsize = filesize($_GET['path']); 
		$mime = explode( "/", mime_content_type($_GET['path']) );
		header('Pragma: public');
		header("Content-Length: ".$fsize); 
		header("Content-Type: application/".end($mime));
		header("Content-Disposition: attachment; filename=".end(explode("/", $_GET['path'])));
		header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
		
		readfile($_GET['path']);
	break;
	case "create-zip":
		$fullpath = file_get_contents('php://input');
		$locale = end(explode("/",$fullpath));
		$zip = new ZipArchive;
		$zip->open($fullpath.".zip", ZipArchive::CREATE);
		folderToZip($fullpath, $zip, $locale);
		$zip->close();
		
		$items = explode("/", $fullpath);
		print buildTree($items);
	break;
	/*
	case "create-theme":
		$p = JSON::load('php://input');
		$path = "../".$p['subdomain']."/themes/".$p['name'];
		mkpath($path);
		copyFolder("modules/themes/templates/".$p['template'], $path);
	break;
	*/
	default:break;
}
	
/**********************************************************************************/

function buildTree($items){
	$root = implode("/", array_slice($items, 0, 2));
	$map = array_slice($items, 2);
	$tree['ajax'] = ["path"=>$root."/ajax", "type"=>"folder", "content"=>[]];
	$tree['modules'] = ["path"=>$root."/modules", "type"=>"folder", "content"=>[]];
	$tree['themes'] = ["path"=>$root."/themes", "type"=>"folder", "content"=>[]];
	$mode = array_shift($map);
	if($mode==="themes"){
		$tree['themes']['type'] = "openfolder";
		$tree['themes']['content'] = buildFolderTree($root."/themes", $map);
	}elseif($mode==="modules"){
		$tree['modules']['type'] = "openfolder";
		$tree['modules']['content'] = buildFolderTree($root."/modules", $map);
	}elseif($mode==="ajax"){
		$tree['ajax']['type'] = "openfolder";
		$tree['ajax']['content'] = buildFolderTree($root."/ajax", $map);
	}
	return JSON::stringify($tree);
}

?>