<?php
	
	$brancher->auth("developer") or die(include_once("modules/auth/page.php"));
	
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.themes</title>
		
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/code-editor/tpl/code-editor.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?php print($config->themes); ?>/theme.css">
		
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/code-editor/tpl/code-editor.js"></script>
		<script src="/xhr/wordlist?d=base" async charset="utf-8"></script>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script>
			window.onload = function(){
				translate.fragment();
			}
		</script>
	</head>
	<body class="leftmode">
		<div id="topbar" class="panel">
            <div class="toolbar">
				<span title="save" data-translate="title" class="tool" onclick="saveFile()">&#xe962;</span>
				<span class="tool" title="JSON Patterns" onclick="showPatern('json')">&#xe8ab;</span>
			</div>
			<div class="toolbar right">
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('developer')">&#xf013;</span>
			</div>
        </div>
		<div id="environment"><?php include_once($_GET['path']); ?></div>
		<script>
			var editor = (function(node){ return ace.edit(node); })(doc.scripts[doc.scripts.length-1].previous());
			editor.setTheme("ace/theme/solarized_dark");
			editor.getSession().setMode("ace/mode/json");
			
			editor.setShowInvisibles(false);
			editor.setShowPrintMargin(false);
			editor.resize();
			var noChanged = true;
			var frame_handle = "<?php print($_GET['handle']); ?>";
			editor.session.on('change', function(event){
				if(noChanged && editor.curOp && editor.curOp.command.name){
					noChanged = false;
					window.parent.doc.querySelector("#topbar>label[for='"+frame_handle+"']").setAttribute("changed", true);
				}
			});
		</script>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					
				</div>
			</aside>
			<div class="tab" style="display:block">
				
			</div>
		</div>
    </body>
</html>