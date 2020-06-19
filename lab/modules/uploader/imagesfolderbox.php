<?php

if(empty($standby)) $standby = JSON::load("core/standby.json");

if(empty($_GET['p'])){
	$path = $standby['uploader']['path'];
}else{
	$path = $standby['uploader']['path'] = $_GET['p'];
	JSON::save("core/standby.json", $standby);
}

$path = explode("/", $path);
define("SUBDOMAIN", array_shift($path));
define("DOMAIN", $config->{"../".SUBDOMAIN});

/*********************************************************************************************/

function folderExplorer(&$selected, $path=""){
	foreach(scandir("../".SUBDOMAIN."/data/".$path) as $val){
       	$realpath = empty($path) ? $val : $path."/".$val;
		if(is_dir("../".SUBDOMAIN."/data/".$realpath) && $val!="." && $val!=".."){
			if(current($selected)===$val){
				next($selected);
				$folders.="<a class='openfolder' href='?p=".SUBDOMAIN."/".$realpath."'>".$val."</a><div class='root' style='display:block'>".folderExplorer($selected, $realpath)." </div>";
			}else{
				$folders.="<a class='folder' href='?p=".SUBDOMAIN."/".$realpath."'>".$val."</a>";
			}
		}
	}
    return $folders;
}
	ob_start();
?>
<!DOCTYPE html>
<html>
	<head id="head">
		<meta http-equiv="Cache-Control" content="no-cache"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="/modules/uploader/tpl/embed.css"/>
		
		<script type="text/javascript" src="/js/md5.js"></script>
		<script type="text/javascript" src="/js/gbAPI.js"></script>
	</head>
	<body>
		<div id="leftbar" style="width:224px">			
			<div id="uploader">
				<div id="explorer">
<?php

	foreach(scandir("../") as $dir){
		if(($dir!=".") && ($dir!="..")){
			if($dir===SUBDOMAIN){
				$explorer .= "<a class='openfolder' href='?p=".$dir."'>".$dir."</a><div class='root'>".folderExplorer($path)."</div>";
			}else $explorer .= "<a class='folder' href='?p=".$dir."'>".$dir."</a>";
		}
	}
	print($explorer);

?>
				</div>
			</div>
		</div>
		<div id="topbar">
			<div class="toolbar right">
				<span class="tool" data-translate="title" title="select all" onclick="doc.selectAll(true)">&#xe5c3;</span>
			</div>
			<div class="toolbar">
				<span class="tool" data-translate="title" title="upload" onclick="window.top.openFilesDialog('uploader',doc.refreshExplorer)">&#xf07c;</span>
				<span class="tool" data-translate="title" title="create folder" onclick="window.top.createFolder(doc.refreshExplorer)">&#xe2cc;</span>
				<span class="tool" data-translate="title" title="remove marked" onclick="doc.removeFiles()">&#xe9ac;</span>
			</div>
		</div>
		<form id="folder">
<?php

	$realpath = implode("/", $path);
	$fullpath = "../".SUBDOMAIN."/data/".$realpath;

	foreach(scandir($fullpath) as $file){
		if(is_file($fullpath."/".$file)){
			if(reset(explode("/", mime_content_type($fullpath."/".$file)))==="image"){
				$imgs.="
				<figure class='sticker'>
					<img class='preview' src='".DOMAIN."/data/".$realpath."/".$file."'>
					<figcaption>
						<label><input name='imgpath' type='checkbox' data-path='".SUBDOMAIN."/".$realpath."/".$file."' value='".DOMAIN."/data/".$realpath."/".$file."'> ".$file."</label>
					</figcaption>
				</figure>";
			}
		}
	}
	print($imgs);

?>
		</form>
	</body>
	<script>
		var doc = document;
		var folder = doc.querySelector("#folder");
		doc.getImages = function(){
			var inp = folder.querySelectorAll("input");
			var res = [];
			for(var i=0; i<inp.length; i++){
				if(inp[i].checked){
					res.push(inp[i].value);
				}
			}
			return res;
		}
		doc.selectAll = function(checked){
			var inp = folder.querySelectorAll("input");
			for(var i=inp.length; i--;) inp[i].checked = checked;
		}
		doc.refreshExplorer = function(){
			location.reload();
		}
		doc.removeFiles = function(){
			var inp = folder.querySelectorAll("input");
			var files = [];
			for(var i=inp.length; i--;){
				if(inp[i].checked){
					files.push(inp[i].dataset.path);
				}
			}
			if(files.length){
				window.top.confirmBox("delete elements", function(){
					reauth();
					XHR.request("/uploader/actions/remove", function(xhr){
						if(isNaN(xhr.response)){
							alert(xhr.response);
						}else{
							for(var i=inp.length; i--;){
								if(inp[i].checked){
									folder.removeChild(inp[i].parent(3));
								}
							}
						}
					}, JSON.encode(files), "application/json");
				});
			}else window.top.alertBox("elements not selected");
		}
	</script>
</html>

<?php

	$tpl = ob_get_contents();
	ob_end_clean();
	
	$tpl = DOMDocument::loadHTML($tpl);
	
	$wordlist = new Wordlist(array("uploader"));
	$wordlist->translateDocument($tpl);
	
	print($tpl->saveHTML());
?>