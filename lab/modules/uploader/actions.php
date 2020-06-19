<?php

$brancher->auth(["uploader"]) or die("Access denied!");

switch(SUBPAGE){
	case "create-folder":
		$fullpath = file_get_contents('php://input');
		@mkpath($fullpath);
		$files = $folders = [];
		$path = explode("/", $fullpath);
		$path = implode("/", array_slice($path, 0, -1));
		foreach(scandir($path) as $file){
			$fullpath = $path."/".$file;
			if(($file!="." && $file!="..") && is_dir($fullpath)){
				$folders[$file] = ["path"=>$fullpath,"type"=>"folder"];
			}elseif(is_file($fullpath)) $files[$file] = ["path"=>$fullpath, "type"=>mime_content_type($fullpath)];
		}
		print JSON::stringify($folders+$files);
	break;
	case "rename":
		$p = JSON::load('php://input');				// Get old and new names
		rename($p['old'], $p['new']);				// Rename item
	break;
	case "remove":
		$pathlist = JSON::load('php://input');
		foreach($pathlist as $path){
			if(is_dir($path)){
				deletedir($path);					// Remove folder
			}elseif(is_file($path)) unlink($path);	// Remove file
		}
		$items = explode("/", reset($pathlist));
		$map = array_slice($items, 3);
		print JSON::stringify( buildFolderTree(implode("/", array_slice($items, 0, 3)), $map) );
	break;
	case "copy":
		$p = JSON::load('php://input');
		foreach($p['src'] as $src){
			if(is_file($src)){
				copy($src, $p['dest']."/".end( explode("/", $src) ));
			}elseif(is_dir($src)) copyFolder($src, $p['dest']);
		}
		$items = explode("/", $p['dest']);
		$map = array_slice($items, 3);
		print JSON::stringify( buildFolderTree(implode("/", array_slice($items, 0, 3)), $map) );
	break;
	case "move":
		$p = JSON::load('php://input');
		foreach($p['src'] as $src){
			rename($src, $p['dest']."/".end( explode("/", $src) ));
		}
		$items = explode("/", $p['dest']);
		$map = array_slice($items, 3);
		print JSON::stringify( buildFolderTree(implode("/", array_slice($items, 0, 3)), $map) );
	break;
	case "unzip":
		$fullpath = file_get_contents('php://input');
		$items = explode("/", $fullpath);
		$type = mime_content_type($fullpath);
		if($type==="application/zip"){
			$zip = new ZipArchive;
			if($zip->open($fullpath)){
				$zip->extractTo( implode("/", array_slice($items, 0, -1)) );
				$zip->close();
			}
		}
		$map = array_slice($items, 3, -1);
		$root = array_slice($items, 0, 3);
		print JSON::stringify( buildFolderTree(implode("/", $root), $map) );
	break;
	case "create-zip":
		$path = file_get_contents('php://input');
		$items = explode("/",$path);
		$locale = end($items);
		$zip = new ZipArchive;
		$zip->open($path.".zip", ZipArchive::CREATE);
		folderToZip($path, $zip, $locale);
		$zip->close();
		
		$path = implode("/", array_slice($items, 0, -1));
		foreach(scandir($path) as $file){
			$fullpath = $path."/".$file;
			if(($file!="." && $file!="..") && is_dir($fullpath)){
				$folders[$file] = ["path"=>$fullpath,"type"=>"folder"];
			}elseif(is_file($fullpath)) $files[$file] = ["path"=>$fullpath, "type"=>mime_content_type($fullpath)];
		}
		print JSON::stringify($folders+$files);
	break;
	case "import":
		$p = JSON::load('php://input');		// Get images list
		
		foreach($p['sourcelist'] as $url){
			$file = file_get_contents($url);
			$filename = translite(basename($url), "_");
			file_put_contents($p['dest']."/".$filename, $file);
		}		
	break;
	case "download":
		$fsize = filesize($_GET['path']); 
		
		header('Pragma: public');
		header("Content-Length: ".$fsize); 
		header("Content-Type: ".mime_content_type($_GET['path']));
		header("Content-Disposition: attachment; filename=".end(explode("/", $_GET['path'])));
		header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
		
		readfile($_GET['path']);
	break;
	case "upload":
		$data = file_get_contents('php://input');
		if(empty($_GET['seek'])){				
			$size = file_put_contents($_GET['path']."/".$_GET['file'], $data);
		}else{
			$file = fopen($_GET['path']."/".$_GET['file'], "a");
			$size = fwrite($file, $data);
			fclose($file);
		}
		$size = ($size/1022)>>0;
		$strlen = 36 - strlen((STRING)$size) - strlen($_GET['file']);
		printf("<tt>%s%'.".$strlen."d KB</tt><br>", $_GET['file'], $size);
	break;
	case "tree":
		
		$path = file_get_contents('php://input');
		$items = explode("/", $path);
		$map = array_slice($items, 3);
		
		print JSON::stringify( buildFolderTree(implode("/", array_slice($items, 0, 3)), $map) );
		
	break;
	case "get-folder":
		$path = file_get_contents('php://input');
		$files = $folders = [];
		foreach(scandir($path) as $file){
			$fullpath = $path."/".$file;
			if(($file!="." && $file!="..") && is_dir($fullpath)){
				$folders[$file] = ["path"=>$fullpath,"type"=>"folder"];
			}elseif(is_file($fullpath)) $files[$file] = ["path"=>$fullpath, "type"=>mime_content_type($fullpath)];
		}
		print JSON::stringify($folders+$files);
	break;
	
	case "show-folder":
		$path = file_get_contents('php://input');
		$standby = JSON::load("core/standby.json");
		$standby['uploader']['path'] = $path;
		JSON::save("core/standby.json", $standby);
		$path = explode("/", $standby['uploader']['path']);
		$subdomain = array_shift($path);
		$path = implode("/", $path);
		$fullpath = "../".$subdomain."/data".(empty($path) ? "" : "/".$path);
		
		define("DOMAIN", $config->{"../".$subdomain});
		foreach(scandir($fullpath) as $file){
			$realpath = empty($path) ? $file : $path."/".$file;
			if(is_file($fullpath."/".$file)){
				if(reset(explode("/", mime_content_type($fullpath."/".$file)))==="image"){
					$files .= "
					<label class='sticker'>
						<img class='preview' src='".DOMAIN."/data/".$realpath."'><br>
						".$file."
					</label>";
				}
			}elseif(is_dir($fullpath."/".$file) && $file!="." && $file!=".."){
				$dirs .= "
					<label class='sticker'>
						<img data-path='".$subdomain."/".$realpath."' class='preview' src='/images/mime/folder.png'><br>
						".$file."
					</label>";
			}
		}
		print($dirs."".$files);
	break;
	case "refresh":
		$standby = JSON::load("core/standby.json");
		$path = explode("/", $standby['uploader']['path']);
		define("SUBDOMAIN", array_shift($path));
		define("DOMAIN", $config->{"../".SUBDOMAIN});
		foreach(scandir("../") as $dir){
			if(($dir!=".") && ($dir!="..")){
				if($dir===SUBDOMAIN){
					$explorer .= "<a class='openfolder' href='?p=".$dir."'>".$dir."</span><div class='root'>".folderExplorer($path)."</a>";
				}else $explorer .= "<a class='folder' href='?=".$dir."'>".$dir."</a>";
			}
		}
		print($explorer);
	break;
	case "open-folder":
		$standby = JSON::load("core/standby.json");
		$standby['uploader']['path'] = file_get_contents('php://input');
		JSON::save("core/standby.json", $standby);
		$path = explode("/", $standby['uploader']['path']);
		define("SUBDOMAIN", array_shift($path));
		define("DOMAIN", $config->{"../".SUBDOMAIN});
		foreach(scandir("../") as $dir){
			if(($dir!=".") && ($dir!="..")){
				if($dir===SUBDOMAIN){
					$explorer .= "<span class='openfolder'>".$dir."</span><div class='root'>".folderExplorer($path)."</div>";
				}else $explorer .= "<span class='folder' data-path='".$dir."' onclick='openFolder(this.dataset.path)'>".$dir."</span>";
			}
		}
		print($explorer);
	break;
	case "remove-folder":
		$standby = JSON::load("core/standby.json");
		$path = explode("/", $standby['uploader']['path']);
		define("SUBDOMAIN", array_shift($path));
		define("DOMAIN", $config->{"../".SUBDOMAIN});
		deletedir("../".SUBDOMAIN."/data/".implode("/", $path));
		array_pop($path);
		$standby['uploader']['path'] = SUBDOMAIN."/".implode("/", $path);
		JSON::save("core/standby.json", $standby);
		foreach(scandir("../") as $dir){
			if(($dir!=".") && ($dir!="..")){
				if($dir===SUBDOMAIN){
					$explorer .= "<span class='openfolder'>".$dir."</span><div class='root'>".folderExplorer($path)."</div>";
				}else $explorer .= "<span class='folder' data-path='".$dir."' onclick='openFolder(this.dataset.path)'>".$dir."</span>";
			}
		}
		print($explorer);
	break;
	default:break;
}


/*******************************************************************************************************/

function folderExplorer(&$selected, $path=""){
	foreach(scandir("../".SUBDOMAIN."/data/".$path) as $val){
       	$realpath = empty($path) ? $val : $path."/".$val;
		if(is_dir("../".SUBDOMAIN."/data/".$realpath) && $val!="." && $val!=".."){
			if(current($selected)===$val){
				next($selected);
				$folders.="<a class='openfolder' href='?p=".SUBDOMAIN."/".$path."'>".$val."</a><div class='root' style='display:block'>".folderExplorer($selected, $realpath)." </div>";
			}else{
				$folders.="<a class='folder' href='?p=".SUBDOMAIN."/".$realpath."'>".$val."</a>";
			}
		}elseif(is_file("../".SUBDOMAIN."/data/".$realpath)){
			$mime = reset(explode("/", mime_content_type("../".SUBDOMAIN."/data/".$realpath)));
			if($mime==="image"){
				$files.="<span class='image' data-path='".DOMAIN."/data/".$realpath."' onclick='doc.setImage(this.dataset.path)'>".$val."</span>";
			}
		}
	}
    return $folders."".$files;
}
?>