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
		<link rel="stylesheet" media="all" type="text/css" href="/modules/themes/tpl/code-editor.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/themes/tpl/code-editor.js"></script>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script>
			window.onbeforeunload = reauth;
			var SECTION = "themes";
			var editor, Range;
			window.onload = function(){
				getStandby(function(){
					doc.body.className = standby.bodymode || "leftmode";
				});
				editor = ace.edit( doc.querySelector("#environment") );
				editor.setTheme("ace/theme/chrome");
				editor.getSession().setMode("ace/mode/css");
				//Range = ace.require('ace/range').Range;
				setTimeout(function(){
					reauth();
					XHR.request("/themes/actions/load", function(xhr){ editor.session.setValue(xhr.response); }, "<?php print($_GET['p']); ?>", "text/plain");
				},600);
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
		<div id="topbar" class="caption">
			 <div class="toolbar">
				<span title="create file" data-translate="title" class="tool" onclick="createFile()">&#xf15b;</span>
				<span title="save" data-translate="title" class="tool" onclick="saveFile()">&#xe962;</span>
			</div>
			<div class="toolbar">
				<span class="tool" title="CSS Patterns" onclick="showPatern('css')">&#xe8ab;</span>
				<!--<span class="tool" title="CSS Templater" onclick="cssomBox()">&#xf05e;</span>-->
				<input type="color" class="tool" onchange="editor.session.insert(editor.selection.getCursor(), this.value);" style="padding:1px 0px;width:28px;border-radius:0px;">
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
					<span title="css directory" data-translate="title" class="tool" data-tab="css-directory">&#xeae6;</span>
					<span title="images" data-translate="title" class="tool" data-tab="images">&#xe909;</span>
					<span title="manual" data-translate="title" class="tool" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<div id="css-directory" class="tab" onclick="addCSSRule(event.target)">
				<div class="panel"><div class="toolbar"><label class="tool">CSS</label></div></div>
				<fieldset><legend>A</legend>
					<a title="flex-start, center, flex-end, space-between, space-around, stretch">align-content</a>
					<a title="flex-start, center, flex-end, stretch, baseline">align-items</a>
					<a title="auto, flex-start, center, flex-end, stretch, baseline">align-self</a>
					<a title="initial, inherit, unset">all</a>
					<a>animation</a>
					<a>animation-delay</a>
					<a title="normal, alternate, reverse, alternate-reverse">animation-direction</a>
					<a>animation-duration</a>
					<a title="none, forwards, backwards, both">animation-fill-mode</a>
					<a>animation-name</a>
					<a title="running, paused">animation-play-state</a>
				</fieldset>
				<fieldset><legend>B</legend>
					<a title="visible, hidden">backface-visibility</a>
					<a title="fixed, scroll, local">background-attachment</a>
					<a>background</a>
					<a title="padding-box, border-box, content-box, text">background-clip</a>
					<a>background-color</a>
					<a>background-image</a>
					<a title="padding-box, border-box, content-box">background-origin</a>
					<a title="top, left, right, bottom, center">background-position</a>
					<a title="left, right, center">background-position-x</a>
					<a title="top, bottom, center">background-position-y</a>
					<a title="no-repeat, repeat, repeat-x, repeat-y, space, round">background-repeat</a>
					<a title="auto, cover, contain">background-size</a>
					<a>border</a>
					<a>border-bottom</a>
					<a title="transparent">border-bottom-color</a>
					<a title="none, hidden, dotted, dashed, solid, double, groove, ridge, inset, outset">border-bottom-style</a>
					<a title="thin, medium, thick">border-bottom-width</a>
					<a title="collapse, separate">border-collapse</a>
					<a title="transparent">border-color</a>
					<a title="none, URL">border-image</a>
					<a>border-left</a>
					<a title="transparent">border-left-color</a>
					<a title="none, hidden, dotted, dashed, solid, double, groove, ridge, inset, outset">border-left-style</a>
					<a title="thin, medium, thick">border-left-width</a>
					<a>border-radius</a>
					<a>border-right</a>
					<a title="transparent">border-right-color</a>
					<a title="none, hidden, dotted, dashed, solid, double, groove, ridge, inset, outset">border-right-style</a>
					<a title="thin, medium, thick">border-right-width</a>
					<a>border-spacing</a>
					<a title="none, hidden, dotted, dashed, solid, double, groove, ridge, inset, outset">border-style</a>
					<a>border-top</a>
					<a title="transparent">border-top-color</a>
					<a>border-top-left-radius</a>
					<a>border-top-right-radius</a>
					<a title="none, hidden, dotted, dashed, solid, double, groove, ridge, inset, outset">border-top-style</a>
					<a title="thin, medium, thick">border-top-width</a>
					<a title="thin, medium, thick">border-width</a>
					<a title="auto">bottom</a>
					<a title="none, inset">box-shadow</a>
					<a title="content-box, border-box">box-sizing</a>
				</fieldset>
				<fieldset><legend>C</legend>
					<a title="top, bottom">caption-side</a>
					<a title="none, both, left, right">clear</a>
					<a title="auto">clip</a>
					<a title="transparent">color</a>
					<a title="auto">column-count</a>
					<a title="auto, balance, balance-all">column-fill</a>
					<a title="normal">column-gap</a>
					<a>column-rule</a>
					<a>column-rule-color</a>
					<a title="none, hidden, dotted, dashed, solid, double, groove, ridge, inset, outset">column-rule-style</a>
					<a title="thin, medium, thick">column-rule-width</a>
					<a title="none, all">column-span</a>
					<a title="auto">column-width</a>
					<a>columns</a>
					<a>content</a>
					<a title="none">counter-increment</a>
					<a title="none, inherit">counter-reset</a>
					<a title="none, default, pointer">cursor</a>
				</fieldset>
				<fieldset><legend>D, E</legend>
					<a title="ltr, rtl">direction</a>
					<a title="block, inline, inline-block, inline-table, inline-flex, flex, list-item, none, run-in, table">display</a>
					<a title="show, hide">empty-cells</a>
				</fieldset>
				<fieldset><legend>F</legend>
					<a title="none">filter</a>
					<a title="none">flex</a>
					<a title="auto">flex-basis</a>
					<a title="row, row-reverse, column, column-reverse">flex-direction</a>
					<a>flex-flow</a>
					<a>flex-grow</a>
					<a>flex-shrink</a>
					<a title="nowrap, wrap, wrap-reverse">flex-wrap</a>
					<a title="left, right, none">float</a>
					<a title="caption, icon, menu, message-box, small-caption, status-bar">font</a>
					<a>font-family</a>
					<a title="auto, normal, none">font-kerning</a>
					<a title="xx-small, x-small, small, medium, large, x-large, xx-large">font-size</a>
					<a>font-stretch</a>
					<a title="normal, italic, oblique">font-style</a>
					<a title="normal, small-caps">font-variant</a>
					<a title="lighter, normal, bold, bolder">font-weight</a>
				</fieldset>
				<fieldset><legend>H, I, J, L</legend>
					<a title="auto">height</a>
					<a title="none, manual, auto">hyphens</a>
					<a title="auto, crisp-edges, pixelated">image-rendering</a>
					<a title="flex-start, flex-end, center, space-between, space-around">justify-content</a>
					<a title="auto">left</a>
					<a title="normal">letter-spacing</a>
					<a title="normal">line-height</a>
					<a>list-style</a>
					<a title="none, url">list-style-image</a>
					<a title="inside, outside">list-style-position</a>
					<a title="circle, disc, square, armenian, decimal, decimal-leading-zero, georgian, lower-greek, lower-latin, lower-roman, upper-latin, upper-roman, none">list-style-type</a>
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
					<a title="fill, contain, cover, none">object-fit</a>
					<a>opacity</a>
					<a>order</a>
					<a>orphans</a>
					<a>outline</a>
					<a title="invert">outline-color</a>
					<a>outline-offset</a>
					<a title="none, dotted, dashed, solid, double, groove, ridge, inset, outset">outline-style</a>
					<a title="thin, medium, thick">outline-width</a>
					<a title="visible, hidden, scroll, auto">overflow</a>
					<a title="visible, hidden, scroll, auto">overflow-x</a>
					<a title="visible, hidden, scroll, auto">overflow-y</a>
				</fieldset>
				<fieldset><legend>P</legend>
					<a>padding</a>
					<a>padding-bottom</a>
					<a>padding-left</a>
					<a>padding-right</a>
					<a>padding-top</a>
					<a title="always, auto, avoid, left, right">page-break-after</a>
					<a title="always, auto, avoid, left, right">page-break-before</a>
					<a title="auto, avoid">page-break-inside</a>
					<a>perspective</a>
					<a>perspective-origin</a>
					<a title="auto, none">pointer-events</a>
					<a title="absolute, fixed, relative, static, sticky">position</a>
				</fieldset>
				<fieldset><legend>Q, R</legend>
					<a>quotes</a>
					<a title="none, both, horizontal, vertical">resize</a>
					<a>right</a>
				</fieldset>
				<fieldset><legend>T</legend>
					<a>tab-size</a>
					<a title="auto, fixed">table-layout</a>
					<a title="center, justify, left, right, start, end">text-align</a>
					<a title="center, justify, left, right, start, end">text-align-last</a>
					<a title="none, blink, line-through, overline, underline">text-decoration</a>
					<a>text-decoration-color</a>
					<a title="none, line-through, overline, underline">text-decoration-line</a>
					<a title="solid, double, dotted, dashed, wavy">text-decoration-style</a>
					<a>text-indent</a>
					<a title="clip, ellipsis">text-overflow</a>
					<a>text-shadow</a>
					<a title="capitalize, lowercase, uppercase, none">text-transform</a>
					<a>top</a>
					<a>transform</a>
					<a>transform-origin</a>
					<a title="flat, preserve-3d">transform-style</a>
					<a title="ease, ease-in, ease-out, ease-in-out, linear, step-start, step-end, steps, cubic-bezie">transition</a>
					<a>transition-delay</a>
					<a>transition-duration</a>
					<a title="none, all">transition-property</a>
				</fieldset>
				<fieldset><legend>U, V</legend>
					<a title="normal, embed, bidi-override">unicode-bidi</a>
					<a title="auto, none, text, all, contain">user-select</a>
					<a title="baseline, bottom, middle, sub, super, text-bottom, text-top, top">vertical-align</a>
					<a title="visible, hidden, collapse">visibility</a>
				</fieldset>
				<fieldset><legend>W, Z</legend>
					<a title="normal, nowrap, pre, pre-line, pre-wrap">white-space</a>
					<a>widows</a>
					<a>width</a>
					<a title="normal, break-all, keep-all">word-break</a>
					<a title="normal">word-spacingk</a>
					<a title="normal, break-word">word-wrap</a>
					<a title="horizontal-tb, vertical-rl, vertical-lr, sideways-rl, sideways-lr">writing-mode</a>
					<a title="auto">z-index</a>
					<a title="normal">zoom</a>
				</fieldset>
			</div>
			<div id="images" class="tab">
				<div class="panel"><div class="toolbar"><span class="tool" data-translate="nodeValue">images</span></div></div>
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