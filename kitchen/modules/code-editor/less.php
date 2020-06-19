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
				<span class="tool" title="CSS Patterns" onclick="showPatern('css', 'chrome')">&#xe8ab;</span>
			</div>
			<div class="toolbar right">
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('developer')">&#xf013;</span>
			</div>
        </div>
		<div id="environment"><?php include_once($_GET['path']); ?></div>
		<script>
			var editor = (function(node){ return ace.edit(node); })(doc.scripts[doc.scripts.length-1].previous());
			editor.setTheme("ace/theme/chrome");
			editor.getSession().setMode("ace/mode/css");
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
					<span class="tool" data-tab="css-directory">&#xeae6;</span>
				</div>
			</aside>
			<div id="css-directory" class="tab" style="display:block">
				<div class="caption">CSS Wordlist</div>
				<fieldset><legend>A</legend>
					<a>align-content</a>
					<a>align-items</a>
					<a>align-self</a>
					<a>all</a>
					<a>animation</a>
					<a>animation-delay</a>
					<a>animation-direction</a>
					<a>animation-duration</a>
					<a>animation-fill-mode</a>
					<a>animation-name</a>
					<a>animation-play-state</a>
				</fieldset>
				<fieldset><legend>B</legend>
					<a>backface-visibility</a>
					<a>background-attachment</a>
					<a>background</a>
					<a>background-clip</a>
					<a>background-color</a>
					<a>background-image</a>
					<a>background-origin</a>
					<a>background-position</a>
					<a>background-position-x</a>
					<a>background-position-y</a>
					<a>background-repeat</a>
					<a>background-size</a>
					<a>border</a>
					<a>border-bottom</a>
					<a>border-bottom-color</a>
					<a>border-bottom-style</a>
					<a>border-bottom-width</a>
					<a>border-collapse</a>
					<a>border-color</a>
					<a>border-image</a>
					<a>border-left</a>
					<a>border-left-color</a>
					<a>border-left-style</a>
					<a>border-left-width</a>
					<a>border-radius</a>
					<a>border-right</a>
					<a>border-right-color</a>
					<a>border-right-style</a>
					<a>border-right-width</a>
					<a>border-spacing</a>
					<a>border-style</a>
					<a>border-top</a>
					<a>border-top-color</a>
					<a>border-top-left-radius</a>
					<a>border-top-right-radius</a>
					<a>border-top-style</a>
					<a>border-top-width</a>
					<a>border-width</a>
					<a>bottom</a>
					<a>box-shadow</a>
					<a>box-sizing</a>
				</fieldset>
				<fieldset><legend>C</legend>
					<a>caption-side</a>
					<a>clear</a>
					<a>clip</a>
					<a>color</a>
					<a>column-count</a>
					<a>column-fill</a>
					<a>column-gap</a>
					<a>column-rule</a>
					<a>column-rule-color</a>
					<a>column-rule-style</a>
					<a>column-rule-width</a>
					<a>column-span</a>
					<a>column-width</a>
					<a>columns</a>
					<a>content</a>
					<a>counter-increment</a>
					<a>counter-reset</a>
					<a>cursor</a>
				</fieldset>
				<fieldset><legend>D, E</legend>
					<a>direction</a>
					<a>display</a>
					<a>empty-cells</a>
				</fieldset>
				<fieldset><legend>F</legend>
					<a>filter</a>
					<a>flex</a>
					<a>flex-basis</a>
					<a>flex-direction</a>
					<a>flex-flow</a>
					<a>flex-grow</a>
					<a>flex-shrink</a>
					<a>flex-wrap</a>
					<a>float</a>
					<a>font</a>
					<a>font-family</a>
					<a>font-kerning</a>
					<a>font-size</a>
					<a>font-stretch</a>
					<a>font-style</a>
					<a>font-variant</a>
					<a>font-weight</a>
				</fieldset>
				<fieldset><legend>H, I, J, L</legend>
					<a>height</a>
					<a>hyphens</a>
					<a>image-rendering</a>
					<a>justify-content</a>
					<a>left</a>
					<a>letter-spacing</a>
					<a>line-height</a>
					<a>list-style</a>
					<a>list-style-image</a>
					<a>list-style-position</a>
					<a>list-style-type</a>
				</fieldset>
				<fieldset><legend>M</legend>
					<a>margin</a>
					<a>margin-bottom</a>
					<a>margin-left</a>
					<a>margin-right</a>
					<a>margin-top</a>
					<a>max-height</a>
					<a>max-width</a>
					<a>min-height</a>
					<a>min-width</a>
				</fieldset>
				<fieldset><legend>O</legend>
					<a>object-fit</a>
					<a>opacity</a>
					<a>order</a>
					<a>orphans</a>
					<a>outline</a>
					<a>outline-color</a>
					<a>outline-offset</a>
					<a>outline-style</a>
					<a>outline-width</a>
					<a>overflow</a>
					<a>overflow-x</a>
					<a>overflow-y</a>
				</fieldset>
				<fieldset><legend>P</legend>
					<a>padding</a>
					<a>padding-bottom</a>
					<a>padding-left</a>
					<a>padding-right</a>
					<a>padding-top</a>
					<a>page-break-after</a>
					<a>page-break-before</a>
					<a>page-break-inside</a>
					<a>perspective</a>
					<a>perspective-origin</a>
					<a>pointer-events</a>
					<a>position</a>
				</fieldset>
				<fieldset><legend>Q, R</legend>
					<a>quotes</a>
					<a>resize</a>
					<a>right</a>
				</fieldset>
				<fieldset><legend>T</legend>
					<a>tab-size</a>
					<a>table-layout</a>
					<a>text-align</a>
					<a>text-align-last</a>
					<a>text-decoration</a>
					<a>text-decoration-color</a>
					<a>text-decoration-line</a>
					<a>text-decoration-style</a>
					<a>text-indent</a>
					<a>text-overflow</a>
					<a>text-shadow</a>
					<a>text-transform</a>
					<a>top</a>
					<a>transform</a>
					<a>transform-origin</a>
					<a>transform-style</a>
					<a>transition</a>
					<a>transition-delay</a>
					<a>transition-duration</a>
					<a>transition-property</a>
				</fieldset>
				<fieldset><legend>U, V</legend>
					<a>unicode-bidi</a>
					<a>user-select</a>
					<a>vertical-align</a>
					<a>visibility</a>
				</fieldset>
				<fieldset><legend>W, Z</legend>
					<a>white-space</a>
					<a>widows</a>
					<a>width</a>
					<a>word-break</a>
					<a>word-spacingk</a>
					<a>word-wrap</a>
					<a>writing-mode</a>
					<a>z-index</a>
					<a>zoom</a>
				</fieldset>
			</div>
		</div>
    </body>
</html>