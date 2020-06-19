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
				<span class="tool" title="JS Patterns" onclick="showPatern('js', 'ambiance')">&#xe8ab;</span>
			</div>
			<div class="toolbar right">
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('developer')">&#xf013;</span>
			</div>
        </div>
		<div id="environment"><?php include_once($_GET['path']); ?></div>
		<script>
			var editor = (function(node){ return ace.edit(node); })(doc.scripts[doc.scripts.length-1].previous());
			editor.setTheme("ace/theme/ambiance");
			editor.getSession().setMode("ace/mode/javascript");
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
			<div id="js-directory" class="tab" style="display:block">
				<div class="caption">JavaScript API</div>
				<fieldset><legend>Ajax</legend>
					<a>XMLHttpRequest.prototype method: <code>push()</code></a>
					<a>XMLHttpRequest.prototype method: <code>uploader()</code></a>
					<a>XMLHttpRequest.prototype method: <code>execute()</code></a>
					<a>XMLHttpRequest.prototype property: <code>ready</code></a>
					<a>XMLHttpRequest.prototype property: <code>default</code></a>
					<a>XMLHttpRequest.prototype property: <code>stack</code></a>
					<a>class: <code>XHR()</code></a>
					<a>function: <code>JSONP()</code></a>
				</fieldset>
				<fieldset><legend>Box</legend>
					<a>function: <code>modalBox()</code></a>
					<a>class: <code>Box()</code></a>
					<a>Box mathod: <code>drop()</code></a>
					<a>Box mathod: <code>align()</code></a>
					<a>Box mathod: <code>move()</code></a>
					<a>Box event: <code>onopen()</code></a>
					<a>Box event: <code>ondrop()</code></a>
					<a>class: <code>boxList()</code></a>
					<a>boxList property: <code>onFocus</code></a>
					<a>boxList mathod: <code>focus()</code></a>
					<a>boxList mathod: <code>drop()</code></a>
					<a>boxList mathod: <code>clear()</code></a>
				</fieldset>
				<fieldset><legend>Object</legend>
					<a>function: <code>inArray()</code></a>
					<a>function: <code>flip()</code></a>
					<a>function: <code>join()</code></a>
				</fieldset>
				<fieldset><legend>Array</legend>
					<a>Array.prototype method: <code>inArray()</code></a>
					<a>Array.prototype method: <code>toJSON()</code></a>
					<a>Array.prototype method: <code>flip()</code></a>
				</fieldset>
				<fieldset><legend>String</legend>
					<a>String.prototype method: <code>trim()</code></a>
					<a>String.prototype method: <code>levenshtein()</code></a>
					<a>String.prototype method: <code>translite()</code></a>
					<a>String.prototype method: <code>isFormat()</code></a>
					<a>String.prototype method: <code>jsonToObj()</code></a>
					<a>String.prototype method: <code>format()</code></a>
				</fieldset>
				<fieldset><legend>Number</legend>
					<a>function: <code>random()</code></a>
				</fieldset>
				<fieldset><legend>HTMLDocument</legend>
					<a>HTMLDocument.prototype property: <code>width</code></a>
					<a>HTMLDocument.prototype property: <code>height</code></a>
					<a>HTMLDocument.prototype method: <code>create()</code></a>
					<a>HTMLDocument.prototype method: <code>fragment()</code></a>
				</fieldset>
				<fieldset><legend>HTMLElement</legend>
					<a>HTMLElement.prototype method: <code>first()</code></a>
					<a>HTMLElement.prototype method: <code>last()</code></a>
					<a>HTMLElement.prototype method: <code>next()</code></a>
					<a>HTMLElement.prototype method: <code>previous()</code></a>
					<a>HTMLElement.prototype method: <code>parent()</code></a>
					<a>HTMLElement.prototype method: <code>ancestor()</code></a>
					<a>HTMLElement.prototype method: <code>insertToBegin()</code></a>
					<a>HTMLElement.prototype method: <code>insertBeforeNode()</code></a>
					<a>HTMLElement.prototype method: <code>insertAfter()</code></a>
					<a>HTMLElement.prototype method: <code>childElements()</code></a>
					<a>HTMLElement.prototype method: <code>appendChilds()</code></a>
					<a>HTMLElement.prototype method: <code>getCss()</code></a>
					<a>HTMLElement.prototype method: <code>fullScrollTop()</code></a>
					<a>HTMLElement.prototype method: <code>fullScrollLeft()</code></a>
				</fieldset>
				<fieldset><legend>COOKIES</legend>
					<a>class: <code>COOKIE()</code></a>
					<a>COOKIE method: <code>get()</code></a>
					<a>COOKIE method: <code>set()</code></a>
					<a>COOKIE method: <code>remove()</code></a>
					<a>COOKIE method: <code>clear()</code></a>
				</fieldset>
				<fieldset><legend>URL</legend>
					<a>function: <code>splitParams()</code></a>
				</fieldset>
				<fieldset><legend>Storage</legend>
					<a>class: <code>session()</code></a>
					<a>session method: <code>open()</code></a>
					<a>function: <code>reauth()</code></a>
				</fieldset>
				<fieldset><legend>Date</legend>
					<a>function: <code>date()</code></a>
					<a>class: <code>datepicker()</code></a>
				</fieldset>
				<fieldset><legend>Other</legend>
					<a>class: <code>softScroll()</code></a>
					<a>softScroll method: <code>x()</code></a>
					<a>softScroll method: <code>y()</code></a>
				</fieldset>
			</div>
		</div>
    </body>
</html>