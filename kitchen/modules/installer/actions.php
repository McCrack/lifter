<?php

$brancher->auth(array("unstaller")) or die("Access denied!");

switch(SUBPAGE){
	case "upload":
		@mkpath("installs");
		$data = file_get_contents('php://input');
		file_put_contents("installs/".PARAMETER, $data);
		print("upload success");
	break;
	case "refresh":
		foreach(scandir("installs") as $file){
			if(is_file("installs/".$file)){
				$file = explode(".", $file);
				if(end($file)=="zip"){
					$file = reset($file);
					$items .= "<div class='tree-item'><label><input type='radio' name='filename' value='".$file."' required ".(($file===PARAMETER) ? "checked" : "").">".$file."</label></div>";
				}
			}
		}
		print($items);
	break;
	case "checkinstall":
		if(is_dir("modules/".PARAMETER)){
			print("<h2 style='color:#677' data-translate='textContent'>module already</h2>");
		}
	break;
	case "remove":
		unlink("installs/".PARAMETER.".zip");
		foreach(scandir("installs") as $file){
			if(is_file("installs/".$file)){
				$file = explode(".", $file);
				if(end($file)=="zip"){
					$file = reset($file);
					$items .= "<div class='tree-item'><label><input type='radio' name='filename' value='".$file."' required>".$file."</label></div>";
				}
			}
		}
		print($items);
	break;
	case "install":
		if(is_dir("modules/".PARAMETER)){
			print("<p>This module already exists.</p>");
		}
		$zip = new ZipArchive;
		if($zip->open("installs/".PARAMETER.".zip")){
			$zip->extractTo("modules");
			print("<p>UnZip module - Ok</p>");
			
			if(file_exists("modules/".PARAMETER."/install/standby.json")){
				$standby = JSON::load("core/standby.json");
				$subStandby = JSON::load("modules/".PARAMETER."/install/standby.json");
				$standby = array_merge_recursive($standby, $subStandby);
				JSON::save("core/standby.json", $standby);
			}
			if(file_exists("modules/".PARAMETER."/install/brancher.json")){
				//$branch = JSON::load("brancher.json");
				$branch = JSON::load("modules/".PARAMETER."/install/brancher.json");
				
				$map = $brancher->createMap(PARAMETER);
				$module = $brancher->dropBranch($brancher->register, $map);
				
				$branch = array_merge_recursive($brancher->register, $branch);
				if(JSON::save("brancher.json", $branch)){
					print("<p>Update brancher - Ok</p>");
				}
			}
			if(file_exists("modules/".PARAMETER."/install/query.sql")){
				$query = file_get_contents("modules/".PARAMETER."/install/query.sql");
				$mySQL->query($query);
			}
			if(file_exists("modules/".PARAMETER."/install/install.php")){
				include_once("modules/".PARAMETER."/install/install.php");
			}
			$zip->close();
		}else print("<p>UnZip module - Failed</p>");
	break;
	default:break;
}

?>