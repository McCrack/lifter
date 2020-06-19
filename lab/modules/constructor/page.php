<?php

	$brancher->auth() or die(include_once("modules/auth/page.php"));
		
	$standby = JSON::load("core/standby.json");
	
	if(empty($_GET['d'])){
		$path = explode("/", $standby['constructor']['path']);
		define("SUBDOMAIN", $standby['constructor']['subdomain']);
	}else{
		$path = explode("/", $_GET['p']);
		define("SUBDOMAIN", $_GET['d']);
		$standby['constructor']['path'] = $_GET['p'];
		$standby['constructor']['subdomain'] = SUBDOMAIN;
		$standby['constructor']['module'] = reset($path);
		JSON::save("core/standby.json", $standby);
	}	
	define("DOMAIN", $config->{"../".SUBDOMAIN});
	foreach(scandir("../") as $dir){
		if(($dir!=".") && ($dir!="..")){
			if($dir===SUBDOMAIN){
				$tree .= "<a class='openfolder' href='?d=".$dir."'>".$dir."</a><div class='root'>".filesExplorer($path)."</div>";
			}else $tree .= "<a class='folder' href='?d=".$dir."'>".$dir."</a>";
		}
	}
	
	$manual = reset($mySQL->single_row("SELECT `content` FROM `gb_documentation` WHERE `title` LIKE '".$standby['constructor']['module']."' AND `language` LIKE '".USER_LANG."' LIMIT 1"));
	
	ob_start();
?>
	
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.constructor</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/constructor/tpl/constructor.css">
		<link rel="stylesheet" media="all" type="text/css" href="/tpls/skins/<?php print($config->skin); ?>/skin.css">
		<script type="text/javascript" src="/js/md5.js"></script>
		<script type="text/javascript" src="/js/gbAPI.js"></script>
		<script type="text/javascript" src="/tpls/main.js"></script>
		<script type="text/javascript" src="/modules/constructor/tpl/constructor.js"></script>
		<script src="/js/ace/src-min/ace.js" type="text/javascript" charset="utf-8"></script>
		<script src="/modules/editor/tpl/editor.js"></script>
		<script>
			window.onbeforeunload = reauth;
			var SECTION = "constructor";
			window.onload=function(){
				getStandby(function(){
					doc.body.className = standby.bodymode || "leftmode";
					history.replaceState({}, "", "/constructor?d="+standby.subdomain+"&p="+standby.path);
				});
			}
		</script>
	</head>
	<body class="<?php print($standby[SECTION]['bodymode']); ?>">
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
						<?php print($brancher->tree($brancher->register)); ?>
					</div>
				</div>
				<div class="tab left" id="files-list">
					<div class="caption"><span>Explorer</span></div>
					<div class="root">
						<?php print($tree); ?>
					</div>
				</div>
			</div>
		</aside>
<?php

	$path = implode("/",$path);
	if(is_dir("../".SUBDOMAIN."/modules/".$path)){
		$color=16777215;
		foreach(scandir("../".SUBDOMAIN."/modules/".$path) as $file){
			$realpath = empty($path) ? $file : $path."/".$file;
			$fullpath = "../".SUBDOMAIN."/modules/".$realpath;
			if(is_file($fullpath)){
				$info = stat($fullpath);
				$size = round($info['size']/1024, 2)." KB";
				$created = date("d M, H:i",$info['ctime']);
				$mime = mime_content_type($fullpath);
				$filetype = explode("/", $mime);
				if(($filetype[0]==="image")){
					$preview = DOMAIN."/modules/".$realpath;
				}elseif(file_exists("images/mime/".$filetype[1].".png")){
					$preview = "/images/mime/".$filetype[1].".png";
				}elseif(file_exists("images/mime/".$filetype[0].".png")){
					$preview = "/images/mime/".$filetype[0].".png";
				}else{
					$preview = "/images/mime/application.png";
				}
				
				$type = end(explode(".", $file));
				
				$files .= "
				<tr data-path='".$realpath."' data-datatype='".$filetype[0]."' data-filetype='".$type."' ondblclick='openFile(this)'>
					<td align='center'><img src='".$preview."' data-type='".$mime."' data-size='".$size."'></td>
					<td onclick='editable(event)' onkeydown='if(event.keyCode==13){ this.blur(); return false; }'>".$file."</td>
					<td align='center' class='grey'>".$size."</td>
					<td align='center' class='grey'>".$created."</td>
				</tr>";
			}elseif(is_dir($fullpath) && $file!="." && $file!=".."){
				$folders .= "
				<tr data-path='".$realpath."' ondblclick='openFolder(this)'>
					<td align='center'><img src='/images/mime/folder.png'></td>
					<td onclick='editable(event)' onkeydown='if(event.keyCode==13){ this.blur(); return false; }' colspan='3'>".$file."</td>
				</tr>";
			}
		}
		$folder = $folders."\n".$files;
?>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="upload" onclick="openFilesDialog('constructor')">&#xf07c;</span>
				<span class="tool" data-translate="title" title="create file" onclick="createfile()">&#xf0f6;</span>
				<span class="tool" data-translate="title" title="create folder" onclick="newFolder()">&#xe2cc;</span>
				<span class="tool" data-translate="title" title="unzip" onclick="unzip()">&#xe90a;</span>
				<span class="tool" data-translate="title" title="remove" onclick="removeElements()">&#xe9ac;</span>
				<!--<span class="tool" data-translate="title" title="create installer" onclick="createInstaller()">&#xe905;</span>-->
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('constructor')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">
			<table cellspacing="0" cellpadding="3" width="100%" bgcolor="#FFF" onclick="selectFile(event)">
				<col width="46"><col><col width="100"><col width="120">
				<tbody>
<?php
	print($folder);
?>
				</tbody>
			</table>
		</div>
	
<?php
	}elseif(is_file("../".SUBDOMAIN."/modules/".$path)){
		
		$type = end(explode(".", $path));
		if($type==="js") $type = "javascript";
		
?>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="save" onclick="saveFile()">&#xe962;</span>
			</div>
			<div class="toolbar right">
				<span id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span class="tool" onclick="settingsBox('constructor')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">	
			<div id="editor">

			</div>
			<script>
			
			var	edt = ace.edit(doc.querySelector("#editor"));
				edt.setTheme("ace/theme/ambiance");
				edt.getSession().setMode("ace/mode/<?php print($type); ?>");
				edt.setShowInvisibles(false);
				edt.setShowPrintMargin(false);
			
			setTimeout(function(){							
				reauth();
				XHR.request("/constructor/actions/openfile", function(xhr){
					edt.session.setValue(xhr.response);
				}, "", "text/plain");
			},1500);
			</script>
		</div>
<?php
		
	}
?>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span class="tool" data-tab="manual">&#xf05a;</span>
					<span class="tool" data-tab="icons">&#xe90b;</span>
				</div>
			</aside>
			<form id="manual" class="tab" onchange="reloadManual(this)" onsubmit="return saveManual(this)">
				<div class="caption">
					Manual:
					<div class="toolbar right" data-lang="<?php print(USER_LANG); ?>">
						<select name="language" class="tool">
<?php 
	
	$cnf = JSON::load("config.init");
	$laguageSet = $cnf['general']['language']['valid'];
	foreach($laguageSet as $lang){
		if($lang === USER_LANG){
			print("<option value='".$lang."' selected>".$lang."</option>");
		}else print("<option value='".$lang."'>".$lang."</option>");
	}
	
?>
						</select>
					</div>
				</div>
				<div class="content">
					<?php print($manual); ?>
				</div>
				<script>
					(function(obj, e){
						var frame = doc.create("iframe","",{ src:"/editor/embed","class":"HTMLDesigner", style:"height:calc(100% - 80px);"});
						frame.onload = function(){
							e = new HTMLDesigner(frame.contentWindow.document);
							e.setValue(obj.innerHTML);
							e.addCSSFile("/modules/manual/tpl/manual.css");
							e.body.style.cssText = "background-color:#169;color:white";
						}
						obj.parentNode.replaceChild(frame, obj);
					})(doc.querySelector('#manual>.content'));
				</script>
				<div align="right">
					<button data-translate="nodeValue" type="submit">save</button>
				</div>
			</form>
			<div id="icons" class="tab">
				<div class="caption">Icon font</div>
				<table width="100%" cellpadding="5" cellspacing="0" align="center">
					<tbody>
						<tr>
						    <td><span class="icon">&#xf15b;</span><code>&amp;#xf15b;</code></td>
							<td><span class="icon">&#xe924;</span><code>&amp;#xe924;</code></td>
							<td><span class="icon">&#xe962;</span><code>&amp;#xe962;</code></td>
							<td><span class="icon">&#xe90a;</span><code>&amp;#xe90a;</code></td>
							<td><span class="icon">&#xf0f6;</span><code>&amp;#xf0f6;</code></td>
							<td><span class="icon">&#xf1c9;</span><code>&amp;#xf1c9;</code></td>
						</tr>
						<tr>
							<td><span class="icon">&#xf03e;</span><code>&amp;#xf03e;</code></td>
							<td><span class="icon">&#xe909;</span><code>&amp;#xe909;</code></td>
							<td><span class="icon">&#xf07b;</span><code>&amp;#xf07b;</code></td>
							<td><span class="icon">&#xf07c;</span><code>&amp;#xf07c;</code></td>
							<td><span class="icon">&#xf0b1;</span><code>&amp;#xf0b1;</code></td>
							<td><span class="icon">&#xe2cc;</span><code>&amp;#xe2cc;</code></td>
						</tr>
						<tr>
							<td><span class="icon">&#xe5c3;</span><code>&amp;#xe5c3;</code></td>
							<td><span class="icon">&#xf142;</span><code>&amp;#xf142;</code></td>
							<td><span class="icon">&#xf05e;</span><code>&amp;#xf05e;</code></td>
							<td><span class="icon">&#xe904;</span><code>&amp;#xe904;</code></td>
							<td><span class="icon">&#xe90b;</span><code>&amp;#xe90b;</code></td>
							<td><span class="icon">&#xf013;</span><code>&amp;#xf013;</code></td>
						</tr>
						<tr>
							<td><span class="icon">&#xf021;</span><code>&amp;#xf021;</code></td>
							<td><span class="icon">&#xe901;</span><code>&amp;#xe901;</code></td>
							<td><span class="icon">&#xf05f;</span><code>&amp;#xf05f;</code></td>
							<td><span class="icon">&#xe908;</span><code>&amp;#xe908;</code></td>
							<td><span class="icon">&#xe907;</span><code>&amp;#xe907;</code></td>
							<td><span class="icon">&#xe9ac;</span><code>&amp;#xe9ac;</code></td>
						</tr>
						<tr>
							<td><span class="icon">&#xe431;</span><code>&amp;#xe431;</code></td>
							<td><span class="icon">&#xf05a;</span><code>&amp;#xf05a;</code></td>
							<td><span class="icon">&#xeae4;</span><code>&amp;#xeae4;</code></td>
							<td><span class="icon">&#xeae6;</span><code>&amp;#xeae6;</code></td>
							<td><span class="icon">&#xea81;</span><code>&amp;#xea81;</code></td>
							<td><span class="icon">&#xe8ab;</span><code>&amp;#xe8ab;</code></td>
						</tr>
						<tr>
							<td><span class="icon">&#xe902;</span><code>&amp;#xe902;</code></td>
							<td><span class="icon">&#xe9bc;</span><code>&amp;#xe9bc;</code></td>
							<td><span class="icon">&#xe905;</span><code>&amp;#xe905;</code></td>
							<td><span class="icon">&#xf0b6;</span><code>&amp;#xf0b6;</code></td>
							<td><span class="icon">&#xe972;</span><code>&amp;#xe972;</code></td>
							<td><span class="icon">&#xe8b6;</span><code>&amp;#xe8b6;</code></td>
						</tr>
						<tr>
							<td><span class="icon">&#xe8b5;</span><code>&amp;#xe8b5;</code></td>
							<td><span class="icon">&#xe900;</span><code>&amp;#xe900;</code></td>
							<td><span class="icon">&#xf066;</span><code>&amp;#xf066;</code></td>
							<td><span class="icon">&#xe903;</span><code>&amp;#xe903;</code></td>
							<td><span class="icon">&#xf1d8;</span><code>&amp;#xf1d8;</code></td>
							<td><span class="icon">&#xea52;</span><code>&amp;#xea52;</code></td>
						</tr>
						<tr>
							<td><span class="icon">&#xe045;</span><code>&amp;#xe045;</code></td>
							<td><span class="icon">&#xe020;</span><code>&amp;#xe020;</code></td>
							<td><span class="icon">&#xe01f;</span><code>&amp;#xe01f;</code></td>
							<td><span class="icon">&#xe044;</span><code>&amp;#xe044;</code></td>
							<td><span class="icon">&#xe906;</span><code>&amp;#xe906;</code></td>
							<td><span class="icon">&#xf024;</span><code>&amp;#xf024;</code></td>
						</tr>
					</tbody>
				</table>
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