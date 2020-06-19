<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));
	
	if(SUBPAGE){
		$post = $mySQL->single_row("SELECT * FROM `gb_sitemap` CROSS JOIN `gb_pages` USING(`PageID`) CROSS JOIN `gb_static` USING(`PageID`) WHERE `name` LIKE '".SUBPAGE."' AND `language` LIKE '".PAGE."' LIMIT 1");
		$content = gzdecode($post['content']);
		$name = SUBPAGE;
	}else{
		$post['PageID'] = 0;
		$post['parent'] = "root";
		$name = "";
	}
	$cng = new config("../".$config->{"base folder"}."/".$config->{"config file"});
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.sitemap</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/sitemap/tpl/sitemap.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
			
		<script type="text/javascript" src="/js/md5.js"></script>
		<script type="text/javascript" src="/js/gbAPI.js"></script>
		<script type="text/javascript" src="/tpls/main.js"></script>
		
		<script src="/modules/sitemap/tpl/sitemap.js"></script>
		<script src="/modules/editor/tpl/editor.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=editor" async charset="utf-8"></script>
		<script src="/js/ace/src-min/ace.js" type="text/javascript" charset="utf-8"></script>
		<script>
			window.onbeforeunload = reauth;
		</script>
	</head>
	<body>
		<aside id="leftbar">
			<a href="/" id="goolybeep">	</a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list">&#xe5c3;</span>
						<span class="tool" data-tab="sitemap">&#xe9bc;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?=$brancher->tree($brancher->register)?>
					</div>
				</div>
				<div class="tab left" id="sitemap">
					<div class="caption">
						<select class="tool right" style="margin-right:10px" onchange="reloadTree(this.value)">
<?php 
	
	$laguageSet = $cng->languageset;
	$language = PAGE ? PAGE : $cng->language;
	foreach($laguageSet as $lang){
		if($lang === $language){
			$languages .= "<option value='".$lang."' selected>".$lang."</option>";
		}else $languages .= "<option value='".$lang."'>".$lang."</option>";
	}
	print($languages);
	
?>
						</select>
						<span data-translate="textContent">sitemap</span>
					</div>
					<div id="explorer">
						<div class="root">
							<a class="tree-root-item" href="/sitemap/<?=$language?>">Root</a>
			
<?php

	function staticTree(&$items, $offset="root"){
		if(is_array($items[$offset])){
			$result.="<div class='root'>";
			foreach($items[$offset] as $key=>$val){
				$caption = empty($val['header']) ? $val['name'] : $val['header'];
				$result.="<a href='/sitemap/".$val['language']."/".$val['name']."' class='".(($val['Published']==="Published")?"green ":"")."tree-".(($val['name']===SUBPAGE) ? "root-" : "")."item'>".$caption."</a>";
				$result .= staticTree($items, $key);
			}
			$result.="</div>";
			return $result;
		}
	}

	$rows = $mySQL->tree("SELECT `parent`,`name`,`header`,`language`,`Published` FROM `gb_sitemap` WHERE `language` LIKE '".$language."' ORDER BY `PageID` ASC", "name", "parent");
	print( staticTree($rows) );

?>
						</div>
					</div>
				</div>
			</div>
		</aside>
		<form id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="add page" onclick="addPage()">&#xf0f6;</span>
				<span class="tool" data-translate="title" title="save" onclick="savePage()">&#xe962;</span>
				<span class="tool" data-translate="title" title="remove" onclick="removePage()">&#xe9ac;</span>
			</div>
			<div class="toolbar right">
				<label class="tool" style="font:18px/1.2 main"><input type="checkbox" name="published" <?php if($post['published']=="Published") print("checked"); ?>>Published</label>
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('sitemap')">&#xf013;</span>
			</div>
		</form>
		<div id="environment">
			<textarea id="content"><?=$content?></textarea>
		</div>
		<div id="rightbar">
			<aside class="tabbar right">
				<div class="toolbar">
					<span class="tool" data-translate="title" title="metadata" data-tab="meta">&#xe906;</span>
					<span class="tool" data-translate="title" title="code editor" data-tab="code-editor">&#xeae4;</span>
					<span class="tool" data-translate="title" title="document map" data-tab="document-map">&#xe902;</span>
					<span class="tool" data-translate="title" title="manual" data-tab="manual">&#xf05a;</span>
					<script>
					(function(bar){
						bar.onclick=function(event){
							rightbar.classList.toggle('fullscreen',false);
							openTab(event.target, 'rightbar');
						}
					})(document.currentScript.parentNode)
					</script>
				</div>
			</aside>
			<div id="manual" class="tab">
<?php
	
	include_once("modules/manual/embed.php");

?>
			</div>
			<form id="meta" class="tab" autocomplete="off">
				<div class="environment">
					<div class="caption">
						<small>ID:</small> <input name="pageID" value="<?=$post['PageID']?>" readonly size="3" placeholder="0">
						<!-- language -->
						<input name="language" readonly value="<?=$language?>" size="3">
						<!-- Parent -->
						<div class="right"><small>Parent:</small> <input name="prt" value="<?=$post['parent']?>" size="16"></div>
					</div>
			<!-- Type -->
				<fieldset class="right"><legend data-translate="textContent">type</legend>
					<select name="entity">
						<?php
							$types = "<option value='".$post['type']."' selected>".$post['type']."</option>";
							foreach(["category", "material"] as $entity){
								if($entity === $post['type']) continue;
								$types .= "<option value='".$entity."'>".$entity."</option>";
							}
							print $types;
						?>
					</select>
				</fieldset>
			<!-- URL Name -->					
					<fieldset><legend data-translate="textContent">page name</legend>
						<span class="tool" data-translate="title" title="vocabulary" onclick="showWordlistBox(this.next().value)">&#xe431;</span>
						<input name="url" value="<?=$name?>" placeholder="..." required style="width:calc(100% - 32px)">
					</fieldset>
			<!-- Module -->
					<fieldset class="right"><legend data-translate="textContent">module</legend>
						<select name="module">

<?php

	foreach(scandir("../".BASE_FOLDER."/modules") as $dir){
		if(($dir!="." && $dir!="..") && is_dir("../".BASE_FOLDER."/modules/".$dir)){
			if($dir === $post['module']){
				$modules .= "<option value='".$dir."' selected>".$dir."</option>";
			}elseif(file_exists("../".BASE_FOLDER."/modules/".$dir."/page.php")){
				$modules .= "<option value='".$dir."'>".$dir."</option>";
			}
		}
	}
	print($modules); 
?>

						</select>
					</fieldset>
			<!-- Header -->
					<fieldset><legend data-translate="textContent">header</legend>
						<input name="header" value="<?=$post['header']?>" placeholder="..." required>
					</fieldset>
			<!-- Template -->
					<fieldset class="right"><legend data-translate="textContent">template</legend>
						<select name="template"> 

<?php
	
	foreach(scandir("../".BASE_FOLDER."/themes/".$cng->theme."/includes") as $file){
		$file = explode(".", $file);
		if(end($file) === "html"){
			if($file[0] === $post['template']){
				$templates .= "<option value='".$file[0]."' selected>".$file[0]."</option>";
			}else $templates .= "<option value='".$file[0]."'>".$file[0]."</option>";
		}
	}
	
	print($templates);
?>
						</select>
					</fieldset>
			<!-- Subheader -->
					<fieldset><legend data-translate="textContent">subheader</legend>
						<input name="subheader" value="<?=$post['subheader']?>" placeholder="..." required>
					</fieldset>
			<!-- Context -->
					<fieldset style="width:99%;"><legend data-translate="textContent">context</legend>
						<input name="context" value="<?=$post['context']?>" placeholder="..." required>
					</fieldset>
					<img id="preview" src="<?=(empty($post['preview'])?"/images/NIA.jpg":$post['preview'])?>" height="284">
				</div>
				<div class="caption">Description</div>
				<textarea name="desc" id="description" placeholder="..."><?=$post['description']?></textarea>
				
				<div class="caption">
					<span data-translate="textContent">Options</span>
					<div class="toolbar right">
						<span title="Pattern" class="tool" onclick="showPattern(patternWithoutValidate(doc.querySelector('#options')), 'JsonToOptions');">&#xe8ab;</span>
					</div>
				</div>
				<div id="properties">
					<table id="options" class="set" rules="cols" width="100%" cellpadding="0" cellspacing="0" bordercolor="#CCC">
						<colgroup><col width="30"><col><col><col width="30"></colgroup>
						<tbody>
						<?
						$properties = JSON::parse($post['optionset']);
						if(!empty($properties)):foreach($properties as $key=>$val):?>
							<tr>
								<th title="Add value" onclick="addRow(this)" bgcolor="white"><span class="tool">&#xe908;</span></th>
								<td contenteditable="true"><?=$key?></td>
								<td contenteditable="true"><?=$val?></td>
								<th bgcolor="white" title="Delete row" onclick="deleteRow(this)"><span class='tool red'>&#xe907;</span></th>
							</tr>
						<?endforeach?>
						<?else:?>
							<tr>
								<th title="Add value" onclick="addRow(this)" bgcolor="white"><span class="tool">&#xe908;</span></th>
								<td contenteditable="true"></td>
								<td contenteditable="true"></td>
								<th bgcolor="white" title="Delete row" onclick="deleteRow(this)"><span class='tool red'>&#xe907;</span></th>
							</tr>
						<?endif?>
						</tbody>
					</table>
				</div>
				<!-- -->
			</form>
			<div id="code-editor" class="tab">
				<div class="caption">
					HTML code
					<div class="toolbar right">
						<span title="screen mode" data-translate="title" class="tool">
							&#xf066;
							<script>
							(function(btn){
								btn.onclick=function(){
									rightbar.classList.toggle('fullscreen');
									setTimeout(function(){ html.resize() },600);
								}
							})(document.currentScript.parentNode)
							</script>
						</span>
					</div>
				</div>
				<div id="html-editor">
				</div>
			</div>
			<div id="document-map" class="tab">
				<div class="caption" data-translate="textContent">document map</div>
				<div id="docmap">
					<nav id="selected-range" class="article">
						<span class="article">article</span>
					</nav>
				</div>
			</div>
		</div>
		<script>
			var	html = ace.edit(doc.querySelector("#rightbar #html-editor"));
				html.setTheme("ace/theme/twilight");
				html.getSession().setMode("ace/mode/html");
				html.setShowInvisibles(false);
				html.setShowPrintMargin(false);
				html.resize();
			var editor;
			(function(obj){
				var frame = doc.create("iframe","",{ "src":"/editor/embed", "class":"HTMLDesigner", "style":"height:100%;with:100%"});
				frame.onload = function(){
					editor = new HTMLDesigner(frame.contentWindow.document, html, doc.querySelector('#docmap'));
					editor.setValue(obj.value);
					//editor.addCSSFile("<?php print(BASE_DOMAIN."/tpls/".$cng->theme."/content.css"); ?>");
					editor.addCSSFile("/modules/editor/tpl/content.css");
					html.session.setValue(editor.getValue().trim());
					editor.onRefresh = function(){
						html.session.setValue(editor.getValue().trim());
					}
					editor.save = function(){
						var form = doc.querySelector("#meta");
						saveContent(function(response){
							if(isNaN(response)){
								console.log(response);
								if(confirm("Failed to save. Do you want to retry?")){
									editor.save();
								};
							}
						}, (form.pageID.value || false));
					}
					html.session.on('change', function(event){
						if(html.curOp && html.curOp.command.name){
							html_change = true;
							setTimeout(function(){
								if(html_change){
									editor.setValue(html.session.getValue());
									change = true;
								}
								html_change = false;
							},800);
						}
					});
				}
				obj.parentNode.replaceChild(frame, obj);
			})(doc.querySelector('#content'));
		</script>
	</body>
</html>