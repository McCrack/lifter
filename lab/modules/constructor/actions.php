<?php

$brancher->auth() or die("Access denied!");

switch(SUBPAGE){
	case "openfile":
		$standby = JSON::load("core/standby.json");
		$data = file_get_contents("../".$standby['constructor']['subdomain']."/modules/".$standby['constructor']['path']);
		header("Content-type: text/plain");
		print($data);
	break;
	case "savefile":
		$data = file_get_contents('php://input');
		$standby = JSON::load("core/standby.json");
		$saved = file_put_contents("../".$standby['constructor']['subdomain']."/modules/".$standby['constructor']['path'], $data);
		print($saved);
	break;
	case "rename":
		$p = JSON::load('php://input');		// Get old and new names
		rename($p['old'], $p['new']);		// Rename item
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
	case "upload":
		$data = file_get_contents('php://input');
		$standby = JSON::load("core/standby.json");
		$path = "../".$standby['constructor']['subdomain']."/modules/".$standby['constructor']['path'];
		if(SUBPARAMETER){				
			$file = fopen($path."/".PARAMETER, "a");
			fwrite($file, $data);
			fclose($file);
		}else{
			file_put_contents($path."/".PARAMETER, $data);
		}
	break;
	case "unzip":
		$path = file_get_contents('php://input');
		$type = mime_content_type($path);
		if($type==="application/zip"){
			$zip = new ZipArchive;
			if($zip->open($path)){
				print($zip->extractTo(dirname($path)));
				$zip->close();
			}
		}
	break;
	case "create-folder":
		$folder = file_get_contents('php://input');			// Get folder name
		$standby = JSON::load("core/standby.json");			// Get current path
		
		$path = explode("/", $standby['constructor']['path']);// Build new folder path
		$path[] = $folder;
		$path = $standby['constructor']['path'] = implode("/", $path);
		
		mkpath("../".$standby['constructor']['subdomain']."/modules/".$path);										// Make new folder
		JSON::save("core/standby.json", $standby);
	break;
	case "create-file":
		$file_name = file_get_contents('php://input');		// Get file name
		$standby = JSON::load("core/standby.json");			// Get current path
		$path = $standby['constructor']['path'];
		$fullpath = "../".$standby['constructor']['subdomain']."/modules/".$path;
		
		if(is_file($fullpath)){
			$path = dirname($fullpath);
		}
		$path = explode("/", $path);						// Build new folder path
		$path[] = $file_name;
		$path = $standby['constructor']['path'] = implode("/", $path);
		$fullpath = "../".$standby['constructor']['subdomain']."/modules/".$path;
		
		if(file_exists($fullpath)){
			print("file already");
		}else{
			file_put_contents($fullpath, "");
			JSON::save("core/standby.json", $standby);
		}
	break;
	case "create-installer":
		
		$standby = JSON::load("core/standby.json");
		$module = $standby['constructor']['module'];
		$standby = JSON::stringify(array($module=>$standby[$module]));

		file_put_contents("modules/".$module."/install/standby.json", $standby);
		
		$brancher->buildBranch($brancher->createMap($module));
		$branch = JSON::stringify($brancher->current);
		
		@mkpath("modules/".$module."/install");
		file_put_contents("modules/".$module."/install/brancher.json", $branch);
		
		$zip = new ZipArchive();
		$zip->open("installs/".$module.".zip", ZipArchive::CREATE);
		fillingZipArchive($zip, $module);
		$zip->close();
		
		print("installs/".$module.".zip");
	break;
	default: break;	
}

/*************************************************************************************/
	
function filesExplorer($selected, $path=""){
	foreach(scandir("../".SUBDOMAIN."/modules/".$path) as $val){
		$realpath = empty($path) ? $val : $path."/".$val;
		if(is_dir("../".SUBDOMAIN."/modules/".$realpath) && $val!="." && $val!=".."){
			if(current($selected)===$val){
				next($selected);
				$folders.="<a class='openfolder' href='?d=".SUBDOMAIN."&p=".$realpath."'>".$val."</a><div class='root' style='display:block'>".filesExplorer($selected, $realpath)." </div>";
			}else{
				$folders.="<a class='folder' href='?d=".SUBDOMAIN."&p=".$realpath."'>".$val."</a>";
			}
        }elseif(is_file("../".SUBDOMAIN."/modules/".$realpath)){
			$mime = reset(explode("/", mime_content_type("../".SUBDOMAIN."/modules/".$realpath)));
			$type = end(explode(".", $val));
			$files.="<a class='file' data-datatype='".$mime."' data-filetype='".$type."' data-path='".$realpath."' onclick='openFile(this)'>".$val."</a>";
		}
    }
    return $folders.$files;
}

?>