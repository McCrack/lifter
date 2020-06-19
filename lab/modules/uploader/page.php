<?php

	$brancher->auth() or die(include_once("modules/auth/page.php"));
	
?>
	
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.uploader</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/uploader/tpl/uploader.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?php print($config->themes); ?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/uploader/tpl/uploader.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=uploader" async></script>
		<script>
			window.onbeforeunload = reauth;
			var openFile = downloadFile;
			const MULTISELECT=true, ONLY_IMAGES=false, SHOWFILES=false, FOLDER_CONTENT=true;
			standby.subdomain = location.pathname.split(/\//)[2] || standby.subdomain || "www";
			window.onload=function(){
				translate.fragment();
				standby.leftbar = "explorer";
				doc.body.className = standby.bodymode;
				environment = doc.querySelector("#environment");
				pathline = doc.querySelector("#topbar>input[name='pathline']");
				pathline.onchange = function(){
					let path = this.value.split(/\//);
					path[1] = "data";
					this.value = path.join("/");
					reloadExplorer("../"+this.value);
				}
				root = doc.querySelector("#explorer>div[data-root='"+standby.subdomain+"']");
				root.onclick = Open;
				root.oncontextmenu = function(event){
					new Context(event.target, event);
					return false;
				}
				if(standby.subdomain) reloadExplorer(standby[standby.subdomain] || "../"+standby.subdomain+"/data");
			}
		</script>
	</head>
	<body>
		<aside id="leftbar">
			<a href="/" id="goolybeep">	</a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list">&#xe5c3;</span>
						<span class="tool" data-tab="explorer">&#xf07b;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?php print($brancher->tree($brancher->register)); ?>
					</div>
				</div>
				<div class="tab left" id="explorer" style="display:block">
					<div class="caption">Explorer</div>
					<?foreach(scandir("../") as $subdomain) if(($subdomain!=".") && ($subdomain!="..") && is_dir("../".$subdomain)):?>
						<a class="domain" href="/uploader/<?=$subdomain?>"><?=$subdomain?></a><div data-root="<?=$subdomain?>"></div>
					<?endif?>
				</div>
			</div>
		</aside>
		<div id="topbar" class="panel">
			<input class="tool" name="pathline">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="out folder" onclick="outFolder()">&#xf0b1;</span>
				<span class="tool" data-translate="title" title="upload" onclick="uploadFiles(environment.dataset.path)">&#xf07c;</span>
				<span class="tool" data-translate="title" title="create folder" onclick="createFolder()">&#xe2cc;</span>
				<span class="tool" data-translate="title" title="import images" onclick="importImagesDialog()">&#xe909;</span>
				<span class="tool" data-translate="title" title="remove" onclick="removeElements()">&#xe9ac;</span>
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('uploader')">&#xf013;</span>
			</div>
		</div>
		<form id="environment" data-type="openfolder" data-path="" oncontextmenu="return createContextMenu(event)" ondblclick="Open(event)" onsubmit="return false">
		<?foreach(scandir("../") as $dir)	if(($dir!="." && $dir!="..") && is_dir("../".$dir)):?>
			<a href="/uploader/<?=$dir?>" class="file-sticker"><figure data-context="folder"><img src="/images/mime/folder.png"><?=$dir?></figure></a>
		<?endif?>
		</form>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span class="tool" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<div id="manual" class="tab" style="display:block;">
<?php
	
	include_once("modules/manual/embed.php");

?>

			</div>
		</div>
	</body>
</html>