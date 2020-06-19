<?php

	$brancher->auth("uploader") or die(include_once("modules/auth/page.php"));
	
?>
<!DOCTYPE html>
<html>
	<head id="head">
		<meta http-equiv="Cache-Control" content="no-cache"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/uploader/tpl/uploader.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?php print($config->themes); ?>/theme.css">
		<style>
			#topbar,#environment{
				margin:0;
				width:calc(100% - 38px);
			}
			body.leftmode>#rightbar:hover{ width:280px; }
		</style>
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/uploader/tpl/uploader.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=uploader" async></script>
		<script>
			window.onbeforeunload = reauth;
			
			const ONLY_IMAGES=true, SHOWFILES=false, FOLDER_CONTENT=true;
			standby.subdomain = location.pathname.split(/\//)[3] || standby.subdomain || "www";
			window.onload=function(){
				translate.fragment();
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

			doc.getImages = function(){
				var inp = environment.querySelectorAll("input");
				var res = [];
				for(var i=0; i<inp.length; i++){
					if(inp[i].checked){
						let src = pathToURL(inp[i].next().dataset.path);
						res.push(src);
					}
				}
				return res;
			}
			doc.selectAll = function(checked){
				var inp = environment.querySelectorAll("input");
				for(var i=inp.length; i--;) inp[i].checked = checked;
			}
			var openFile = function(path){
				let src = pathToURL(path);
				parent.window.boxList[parent.window.boxList.onFocus].openFile(src);
			}
		</script>
	</head>
	<body class="leftmode">
		<div id="topbar" class="panel">
			<input class="tool" name="pathline">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="out folder" onclick="outFolder()">&#xf0b1;</span>
				<span class="tool" data-translate="title" title="create folder" onclick="createFolder()">&#xe2cc;</span>
			</div>
			<div class="toolbar right">
				<span class="tool" data-translate="title" title="select all" onclick="doc.selectAll(true)">&#xe5c3;</span>
			</div>
		</div>
		<form id="environment" data-type="openfolder" data-path="" oncontextmenu="return createContextMenu(event)" ondblclick="Open(event)" onsubmit="return false">
<?php


$folders = "";
foreach(scandir("../") as $dir){
	if(($dir!="." && $dir!="..") && is_dir("../".$dir)){
		$folders .= "<a href='/uploader/embed/".$dir."' class='file-sticker'><figure data-context='folder'><img src='/images/mime/folder.png'>".$dir."</figure></a>";
	}
}
print $folders;

?>
		</form>
		<div id="rightbar">
			<aside class="tabbar left">
				<div class="toolbar">
					<span class="tool" data-translate="title" title="upload" onclick="uploadFiles(environment.dataset.path)">&#xf07c;</span>
					<span class="tool" data-translate="title" title="import images" onclick="importImagesDialog()">&#xe909;</span>
					<span class="tool" data-translate="title" title="remove" onclick="removeElements()">&#xe9ac;</span>
				</div>
			</aside>
			<div class="tab" id="explorer" style="display:block">

<?php

	$tree = "";
	foreach(scandir("../") as $subdomain){
		if(($subdomain!=".") && ($subdomain!="..") && is_dir("../".$subdomain)){
			$tree .= "<a class='domain' href='/uploader/embed/".$subdomain."'>".$subdomain."</a><div data-root='".$subdomain."'></div>";
		}
	}
	print($tree);

?>
			</div>
		</div>
	</body>
</html>