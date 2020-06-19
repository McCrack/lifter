<!DOCTYPE html>
<html>
	<head id="head">
		<meta http-equiv="Cache-Control" content="no-cache"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="/modules/editor/tpl/editor.css"/>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<style>
<?php
$fontset="";
foreach(scandir("../".BASE_FOLDER."/fonts") as $file){
	$font = explode(".", $file);
	if(is_file("../".BASE_FOLDER."/fonts/".$file)){
		$fName = str_replace("-", " ", $font[0]);
		$fontset .= "<option>".$fName."</option>";
		switch($font[1]){
			case "otf":
			case "ttf":
				print("
				@font-face{
					font-family:'".$fName."';
					src: local('".$fName."'), url(/xhr/proxy/fonts?file=".$file.") format('truetype');
				}");
			break;
			case "woff":
				print("
				@font-face{
					font-family:'".$fName."';
					src: url(/xhr/proxy/fonts?file=".$file.") format('woff');
				}");
			break;
			default:break;
		}
	}
}


?>
		</style>
		<script src="/xhr/wordlist?d=editor" async charset="utf-8"></script>
		<script>
			window.onload = function(){
				translate.fragment();
			}
		</script>
	</head>
	<body>
		<form id="toolbar" onsubmit="return false">
			<section class="tool">
				<select name="family" data-translate="title" title="font family" class="tool" onchange="doc.setFont(this.value)"><?php print($fontset); ?></select>
				<input name="fsize" data-translate="title" title="font size" class="tool" oninput="doc.setFontSize(this.value)" placeholder="px" size="3" list="font-sizes">
				<datalist id="font-sizes">
					<option>12px</option>
					<option>14px</option>
					<option>16px</option>
					<option>18px</option>
					<option>22px</option>
					<option>24px</option>
					<option>28px</option>
					<option>32px</option>
					<option>36px</option>
					<option>48px</option>
					<option>52px</option>
					<option>60px</option>
				</datalist>
			</section>
			<label data-translate="title" title="spell check" class="tool" onclick="doc.spellCheck()">&#xea12;</label>
			<div class="tool">
				<div class="toolset">
					<label data-translate="title" title="bold" class="tool" data-tag="B" onmousedown="doc.insertTag('bold', 'B')">&#xea62;</label>
					<label data-translate="title" title="italic" class="tool" data-tag="I" onmousedown="doc.insertTag('italic','I')">&#xea64;</label>
					<label data-translate="title" title="underline" class="tool" data-tag="U" onmousedown="doc.insertTag('underline','U')">&#xea63;</label>
					<label data-translate="title" title="strike" class="tool" data-tag="S" onmousedown="doc.insertTag('strikeThrough','S')">&#xea65;</label>
				</div>
			</div>
			<div class="tool">
				<div class="toolset">
					<label data-translate="title" title="paragraph" class="tool" data-tag="P" onmousedown="doc.formatblock('p')">&#xea73;</label>
					<label data-translate="title" title="header level 1" class="tool" onmousedown="doc.formatblock('h1')">H1</label>
					<label data-translate="title" title="header level 2" class="tool" onmousedown="doc.formatblock('h2')">H2</label>
					<label data-translate="title" title="header level 3" class="tool" onmousedown="doc.formatblock('h3')">H3</label>
					<label data-translate="title" title="header level 4" class="tool" onmousedown="doc.formatblock('h4')">H4</label>
				</div>
			</div>
			<div class="tool">
				<div class="toolset">
					<label data-translate="title" title="create link" class="tool" data-tag="A" onmousedown="doc.createlink();">&#xe9cb;</label>
					<label data-translate="title" title="insert image" class="tool" data-tag="IMG" onmousedown="doc.imgBox()">&#xe90d;</label>
					<label data-translate="title" title="add video" class="tool" data-tag="VIDEO" onmousedown="doc.videoBox()">&#xe913;</label>
					<label data-translate="title" title="quote" class="tool" data-tag="blockquote" onmousedown="doc.formatblock('blockquote')">&#xe977;</label>					
					<label data-translate="title" title="bulleted list" class="tool" data-tag="UL" onmousedown="doc.list('insertUnorderedList')">&#xe9bb;</label>
					<label data-translate="title" title="numbered list" class="tool" data-tag="OL" onmousedown="doc.list('insertOrderedList')">&#xe9b9;</label>
				</div>
			</div>
			<div class="tool">
				<div class="toolset">
					<label data-translate="title" title="drop tag" class="tool" onmousedown="doc.drop()">&#xe9ac;</label>
					<label data-translate="title" title="insert free tag" class="tool" data-tag="OTHER" onmousedown="doc.freeTag()">&#xea7f;</label>
				</div>
			</div>
			<!--<label title="Adsense" class="tool" onclick="doc.module('adsense')">&#xea53;</label>-->
			<div class="tool">
				<div class="toolset">
					<label data-translate="title" title="insert figure" class="tool" data-tag="" onmousedown="doc.module('figure')">&#xe927;</label>
					<label data-translate="title" title="insert pattern" class="tool" data-tag="" onmousedown="doc.pattern()">&#xea80;</label>
					<label data-translate="title" title="insert video" class="tool" data-tag="" onmousedown="doc.module('youtube')">▶</label>
					<label data-translate="title" title="insert gallery" class="tool" data-tag="" onmousedown="doc.module('gallery')">&#xe90e;</label>
					<label data-translate="title" title="all modules" class="tool" data-tag="" onmousedown="doc.modules()">&#xea7d;</label>
				</div>
			</div>
			<div class="tool">
				<div class="toolset">
					<label data-translate="title" title="align left" class="tool" onmousedown="doc.setProperty('align','left')">&#xea77;</label>
					<label data-translate="title" title="align center" class="tool" onmousedown="doc.setProperty('align','center')">&#xea78;</label>
					<label data-translate="title" title="align justify" class="tool" onmousedown="doc.setProperty('align','justify')">&#xea7a;</label>
					<label data-translate="title" title="align right" class="tool" onmousedown="doc.setProperty('align', 'right')">&#xea79;</label>
				</div>
			</div>
			<label data-translate="title" title="element properties" class="tool right" onclick="doc.properties()">&#xe992;</label>
		</form>
		<div id="body">
			<article id="content" contenteditable="true">
		
			</article>
		</div>
	</body>
</html>