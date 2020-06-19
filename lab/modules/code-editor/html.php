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
				<span class="tool" title="HTML Patterns" onclick="showPatern('html')">&#xe8ab;</span>
			</div>
			<div class="toolbar right">
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('developer')">&#xf013;</span>
			</div>
        </div>
		<xmp id="environment"><?php print file_get_contents($_GET['path']); ?></xmp>
		<script>
			var editor = (function(node){ return ace.edit(node); })(doc.scripts[doc.scripts.length-1].previous());
			editor.setTheme("ace/theme/twilight");
			editor.getSession().setMode("ace/mode/html");
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
					<span class="tool" data-tab="css-directory">&#xeae4;</span>
				</div>
			</aside>
			<div id="html-directory" class="tab" style="display:block">
				<div class="caption">HTML Wordlist</div>
				<fieldset><legend>A</legend>
					<a>a</a>
					<a>abbr</a>
					<a>address</a>
					<a>area</a>
					<a>article</a>
					<a>aside</a>
					<a>audio</a>
				</fieldset>
				<fieldset><legend>B</legend>
					<a>b</a>
					<a>base</a>
					<a>bdi</a>
					<a>bdo</a>
					<a>blockquote</a>
					<a>body</a>
					<a>br</a>
					<a>button</a>
				</fieldset>
				<fieldset><legend>C</legend>
					<a>canvas</a>
					<a>caption</a>
					<a>cite</a>
					<a>code</a>
					<a>col</a>
					<a>colgroup</a>
				</fieldset>
				<fieldset><legend>D</legend>
					<a>data</a>
					<a>datalist</a>
					<a>dd</a>
					<a>del</a>
					<a>details</a>
					<a>dfn</a>
					<a>dialog</a>
					<a>div</a>
					<a>dl</a>
					<a>dt</a>
				</fieldset>
				<fieldset><legend>E, F</legend>
					<a>em</a>
					<a>embed</a>
					<a>fieldset</a>
					<a>figcaption</a>
					<a>figure</a>
					<a>footer</a>
					<a>form</a>
				</fieldset>
				<fieldset><legend>H</legend>
					<a>h1</a>
					<a>h2</a>
					<a>h3</a>
					<a>h4</a>
					<a>h5</a>
					<a>h6</a>
					<a>head</a>
					<a>header</a>
					<a>hr</a>
					<a>html</a>
				</fieldset>
				<fieldset><legend>I, K, L</legend>
					<a>i</a>
					<a>iframe</a>
					<a>img</a>
					<a>input</a>
					<a>ins</a>
					<a>kbd</a>
					<a>keygen</a>
					<a>label</a>
					<a>legend</a>
					<a>li</a>
					<a>link</a>
				</fieldset>
				<fieldset><legend>M, N</legend>
					<a>main</a>
					<a>map</a>
					<a>mark</a>
					<a>menu</a>
					<a>menuitem</a>
					<a>meta</a>
					<a>meter</a>
					<a>nav</a>
					<a>noscript</a>
				</fieldset>
				<fieldset><legend>O</legend>
					<a>object</a>
					<a>ol</a>
					<a>optgroup</a>
					<a>option</a>
					<a>output</a>
				</fieldset>
				<fieldset><legend>P, Q, R</legend>
					<a>p</a>
					<a>param</a>
					<a>picture</a>
					<a>pre</a>
					<a>progress</a>
					<a>q</a>
					<a>rp</a>
					<a>rt</a>
					<a>rtc</a>
					<a>ruby</a>
				</fieldset>
				<fieldset><legend>S</legend>
					<a>s</a>
					<a>samp</a>
					<a>script</a>
					<a>section</a>
					<a>select</a>
					<a>small</a>
					<a>source</a>
					<a>span</a>
					<a>strong</a>
					<a>style</a>
					<a>sub</a>
					<a>summary</a>
					<a>sup</a>
				</fieldset>
				<fieldset><legend>T</legend>
					<a>table</a>
					<a>tbody</a>
					<a>td</a>
					<a>template</a>
					<a>textarea</a>
					<a>tfoot</a>
					<a>th</a>
					<a>thead</a>
					<a>time</a>
					<a>title</a>
					<a>tr</a>
					<a>track</a>
				</fieldset>
				<fieldset><legend>U, V, W, X</legend>
					<a>u</a>
					<a>ul</a>
					<a>var</a>
					<a>video</a>
					<a>wbr</a>
				</fieldset>
			</div>
		</div>
    </body>
</html>