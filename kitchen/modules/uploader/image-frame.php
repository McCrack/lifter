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
			#panel,#tabs{ width:36px; }
			#panel>.tab{ display:block; }
			#preview{ object-fit:cover; }			
			body:hover>#panel,.tab{ width:240px; }
			body:hover>#environment{ width:calc(100% - 241px); }
			#panel,#tabs,#explorer,#environment,.tab{ height:100%; }
			#environment{
				width:calc(100% - 37px);
				border-left:1px solid #AAA;
				transition:width 0.3s ease;
			}
			#panel{
				overflow:hidden;
				background-color:white;
				transition:width 0.3s ease;
			}
			#explorer{
				width:204px;
				overflow:auto;
			}
			#tabs{
				padding:0px;
				background:linear-gradient(to left, #DFDFDF, #FAFAFA);
			}
		</style>
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/uploader/tpl/uploader.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=uploader" async></script>
		<script>
			window.onbeforeunload = reauth;
			var preview;
			const ONLY_IMAGES=true, ONLY_VIDEOS=false, SHOWFILES=true, FOLDER_CONTENT=false;
			standby.subdomain = location.pathname.split(/\//)[3] || standby.subdomain || "www";
			window.onload=function(){
				translate.fragment();
				preview = doc.querySelector("#preview");
				environment = doc.querySelector("#environment");
				pathline = doc.querySelector("#environment>input[name='pathline']");
				root = doc.querySelector("#explorer>div[data-root='"+standby.subdomain+"']");
				root.onclick = Open;
				root.oncontextmenu = function(event){
					new Context(event.target, event);
					return false;
				}
				if(standby.subdomain) reloadExplorer(standby[standby.subdomain] || "../"+standby.subdomain+"/data");
			}
			var openFile = function(path){
				preview.src = pathToURL(path);
			}
			doc.getImage = function(){
				return preview.src;
			}
			doc.setImage = function(src){
				preview.src = src;
			}
		</script>
	</head>
	<body class="leftmode">
		<div id="environment" class="right" data-path="">
			<input type="hidden" name="pathline">
			<img id="preview" src="/images/NIA.png" align="left" width="100%" height="100%">
		</div>
		<div id="panel" class="left">
			<div class="tab">
				<aside id="tabs" class="caption left">
					<div class="toolbar">
						<span class="tool" data-translate="title" title="out folder" onclick="outFolder()">&#xf0b1;</span>
						<span class="tool" data-translate="title" title="upload" onclick="uploadFiles(environment.dataset.path)">&#xf07c;</span>
						<span class="tool" data-translate="title" title="create folder" onclick="createFolder()">&#xe2cc;</span>
						<span class="tool" data-translate="title" title="remove" onclick="removeElements()">&#xe9ac;</span>
					</div>
				</aside>
				<div id="explorer">

<?php

	$tree = "";
	foreach(scandir("../") as $subdomain){
		if(($subdomain!=".") && ($subdomain!="..") && is_dir("../".$subdomain)){
			$tree .= "<a class='domain' href='/uploader/image-frame/".$subdomain."'>".$subdomain."</a><div data-root='".$subdomain."'></div>";
		}
	}
	print($tree);

?>
				</div>
			</div>
		</div>
	</body>
</html>