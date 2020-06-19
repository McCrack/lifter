<?php

    $brancher->auth() or die(include_once("modules/auth/page.php"));
	
	function Explorer(&$selected, $path=""){
		$root = "../".PAGE."/themes";
		foreach(scandir($root."/".$path) as $val){
			$subpath = empty($path) ? $val : $path."/".$val;
			$realpath = $root."/".$subpath;
			if(is_dir($realpath) && $val!="." && $val!=".."){
				if(current($selected)===$val){
					next($selected);
					$folders.="<a class='openfolder' href='/themes/".PAGE."/".$subpath."'>".$val."</a><div class='root'>".Explorer($selected, $subpath)."</div>";
				}else $folders.="<a class='folder' href='/themes/".PAGE."/".$subpath."'>".$val."</a>";
			}elseif(is_file($realpath)){
				$mime = explode("/", mime_content_type($realpath));
				if(reset($mime)==="image"){
					$files.="<span class='image'>".$val."</span>";
				}else{
					$ext = explode(".", $val);
					$files.="<a class='file' href='/themes/".end($ext)."?p=".$realpath."'>".$val."</a>";
				}
			}
		}
		return $folders." ".$files;
	}
	
	
	$tree = "";
	foreach(scandir("../") as $subdomain){
		if(($subdomain!=".") && ($subdomain!="..")){
			if($subdomain===PAGE){
				$path = [SUBPAGE, PARAMETER, SUBPARAMETER];
				$tree .= "<a class='openfolder' href='/themes/".$subdomain."'>".$subdomain."</a>";
				$tree .= "<div class='root'>".Explorer($path)."</div>";
			}else $tree .= "<a class='folder' href='/themes/".$subdomain."'>".$subdomain."</a>";
		}
	}
	
	ob_start();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.themes</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/themes/tpl/themes.css">
		<link rel="stylesheet" media="all" type="text/css" href="/tpls/skins/<?php print($config->skin); ?>/skin.css">
		<script type="text/javascript" src="/js/md5.js"></script>
		<script type="text/javascript" src="/js/gbAPI.js"></script>
		<script type="text/javascript" src="/tpls/main.js"></script>
		<script type="text/javascript" src="/modules/themes/tpl/themes.js"></script>
		<script>
			window.onbeforeunload = reauth;
			var SECTION = "themes";
			window.onload=function(){
				getStandby(function(){
					doc.body.className = standby.bodymode || "leftmode";
				});
			}
		</script>
	</head>
	<body>
        <aside id="leftbar">
			<a href="/" id="goolybeep">
				<img src="/tpls/skins/subway/imgs/goolybeep.png">
			</a>
			<div id="left-panel">
			    <div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list">&#xe5c3;</span>
						<span class="tool" data-tab="files-list">&#xf07b;</span>
				    </div>
                </div>
                <div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption"><span data-translate="nodeValue">modules</span></div>
					<div class="root">
<?php
	print($brancher->tree($brancher->register));
?>
					</div>
				</div>
				<div class="tab left" id="files-list">
					<div class="caption"><span>Explorer</span></div>
<?php
	print($tree);
?>
				</div>
            </div>
        </aside>
		<div id="topbar" class="panel">
            <div class="toolbar">
				<span title="create theme" data-translate="title" class="tool" onclick="createTheme()">&#xe924;</span>
				<span title="create file" data-translate="title" class="tool" onclick="createFile()">&#xf15b;</span>
				<span title="create folder" data-translate="title" class="tool" onclick="createFolder(location.reload, 'themes')">&#xe2cc;</span>
				<span title="upload" data-translate="title" class="tool" onclick="openFilesDialog('themes')">&#xf07c;</span>
				<span class="tool" data-translate="title" title="unzip" onclick="unzip()">&#xe90a;</span>
				<span class="tool" data-translate="title" title="import images" onclick="importImagesDialog(function(){	window.location.reload(); })">&#xe909;</span>
				<span class="tool" data-translate="title" title="remove" onclick="removeElements()">&#xe9ac;</span>
			</div>
            <div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('themes')">&#xf013;</span>
			</div>
        </div>
		<div id="environment">
		<table cellspacing="0" cellpadding="3" width="100%" bgcolor="#FFF">
			<col width="46"><col><col width="100"><col width="120">
			<tbody onclick="selectFile(this, event.target)">
<?php
	
	$path = "";
	if(PAGE){
		$root = "../".PAGE."/themes";
		if(SUBPAGE){
			$path = SUBPAGE;
			if(PARAMETER){
				$path .= "/".PARAMETER;
				if(SUBPARAMETER) $path .= "/".SUBPARAMETER;
			}
		}
	}else $root = "..";
	foreach(scandir($root."/".$path) as $file){
		$realpath = $root."/".(empty($path) ? $file : $path."/".$file);
		if(is_file($realpath)){
			$info = stat($realpath);
			$size = round($info['size']/1024, 2)." KB";
			$created = date("d M, H:i",$info['ctime']);
			$mime = mime_content_type($realpath);
			$filetype = explode("/", $mime);
			if($filetype[0]==="image"){
				$preview = $config->{"../".PAGE}."/themes/".$path."/".$file;
			}elseif(file_exists("images/mime/".$filetype[1].".png")){
				$preview = "/images/mime/".$filetype[1].".png";
			}elseif(file_exists("images/mime/".$filetype[0].".png")){
				$preview = "/images/mime/".$filetype[0].".png";
			}else $preview = "/images/mime/application.png";
			
			$files .= "
			<tr data-realpath='".$realpath."' ondblclick='openFile(this)'>
				<td align='center'><img src='".$preview."' onmouseover='showFile(event)' onmouseout='hideFile()' data-type='".$mime."' data-size='".$size."'></td>
				<td onclick='editable(event)' onkeydown='if(event.keyCode==13){ this.blur(); return false; }'>".$file."</td>
				<td align='center' class='grey'>".$size."</td>
				<td align='center' class='grey'>".$created."</td>
			</tr>";
		}elseif(is_dir($realpath) && $file!="." && $file!=".."){
			$folders .= "
			<tr data-realpath='".$realpath."' data-path='".(PAGE ? (empty($path) ? PAGE."/".$file : PAGE."/".$path."/".$file) : $file)."' ondblclick='openFolder(this)'>
				<td align='center'><img src='/images/mime/folder.png'></td>
				<td onclick='editable(event)' onkeydown='if(event.keyCode==13){ this.blur(); return false; }' colspan='3'>".$file."</td>
			</tr>";
		}
	}
	print($folders." ".$files);
?>
			</tbody>
		</table>
		<button id="fixed-btn" onclick="multiSelect(this)">Ctrl</button>
		</div>
		<div id="rightbar">
			<aside class="tabbar right">
				<div class="toolbar">
					<span class="tool" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<div id="manual" class="tab" style="display:block">
<?php
	include_once("modules/manual/embed.php");
?>
			</div>
		</div>
    </body>
</html>


<?php

    $tpl = ob_get_contents();
	ob_end_clean();
	
	$tpl = DOMDocument::loadHTML($tpl);
	
	$wordlist = new Wordlist(array("base","modules","uploader"));
	$wordlist->translateDocument($tpl);
	
	print($tpl->saveHTML());

?>