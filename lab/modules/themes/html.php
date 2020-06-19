<?php

    $brancher->auth() or die(include_once("modules/auth/page.php"));
	
	function Explorer(&$selected, $path=""){
	    $root = "../".SUBDOMAIN."/themes";
		foreach(scandir($root."/".$path) as $val){
			$subpath = empty($path) ? $val : $path."/".$val;
			$realpath = $root."/".$subpath;
			if(is_dir($realpath) && $val!="." && $val!=".."){
				if(current($selected)===$val){
					next($selected);
					$folders.="<a class='openfolder' href='/themes/".PAGE."/".$subpath."'>".$val."</a><div class='root'>".Explorer($selected, $subpath)."</div>";
				}else $folders.="<a class='folder' href='/themes/".SUBDOMAIN."/".$subpath."'>".$val."</a>";
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
	$path = explode("/", $_GET['p']);
	define("SUBDOMAIN", $path[1]);
	$tree = "";
	foreach(scandir("../") as $subdomain){
		if(($subdomain!=".") && ($subdomain!="..")){
			if($subdomain===SUBDOMAIN){
				$tree .= "<a class='openfolder' href='/themes/".$subdomain."'>".$subdomain."</a>";
				$tree .= "<div class='root'>".Explorer(array_slice($path, 3))."</div>";
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
		<link rel="stylesheet" media="all" type="text/css" href="/tpls/skins/<?php print($config->skin); ?>/skin.css">
		<script type="text/javascript" src="/js/md5.js"></script>
		<script type="text/javascript" src="/js/gbAPI.js"></script>
		<script type="text/javascript" src="/tpls/main.js"></script>
		<script type="text/javascript" src="/modules/themes/tpl/code-editor.js"></script>
		<script src="/js/ace/src-min/ace.js" type="text/javascript" charset="utf-8"></script>
		<script>
			window.onbeforeunload = reauth;
			var SECTION = "themes";
			var editor;
			window.onload = function(){
				getStandby(function(){
					doc.body.className = standby.bodymode || "leftmode";
				});
				editor = ace.edit( doc.querySelector("#environment") );
				editor.setTheme("ace/theme/twilight");
				editor.getSession().setMode("ace/mode/html");
				setTimeout(function(){
					reauth();
					XHR.request("/themes/actions/load", function(xhr){ editor.session.setValue(xhr.response); }, "<?php print($_GET['p']); ?>", "text/plain");
				},500);
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
				<span title="create file" data-translate="title" class="tool" onclick="createFile()">&#xf15b;</span>
				<span title="save" data-translate="title" class="tool" onclick="saveFile()">&#xe962;</span>
			</div>
			<div class="toolbar">
				<span class="tool" title="HTML Patterns" onclick="showPatern('html')">&#xe8ab;</span>
				<!--<input type="color" class="tool" onchange="editor.session.insert(editor.selection.getCursor(), this.value);" style="padding:1px 0px;width:28px;border-radius:0px;">-->
			</div>			
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('code-editor')">&#xf013;</span>
			</div>
		</div>
		<div id="environment"></div>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span title="images" data-translate="title" class="tool" data-tab="images">&#xe909;</span>
					<span title="manual" data-translate="title" class="tool" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<div id="images" class="tab">
				<div class="caption" data-translate="nodeValue">images</div>
				<table cellspacing="0" cellpadding="6" width="100%">
					<col width="48">
					<tbody>
<?php

	$realpath = "../".SUBDOMAIN."/themes/".$path[3]."/imgs";
	foreach(scandir($realpath) as $file){
		if(is_file($realpath."/".$file)){
			$fullpath = $config->{"../".SUBDOMAIN}."/themes/".$path[3]."/imgs/".$file;
			$rows .= "<tr ondblclick='addImageURL(`".$fullpath."`)'><td><img src='".$fullpath."'></td><td>".$file."</td></tr>";
		}
	}
	print($rows);
?>
					</tbody>
				</table>
			</div>
			<div id="manual" class="tab">
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