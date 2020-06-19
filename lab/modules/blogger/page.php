<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));

?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.blogger</title>
		
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/blogger/tpl/blogger.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
		
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/editor/tpl/editor.js?1"></script>
		<script src="/modules/blogger/tpl/blogger.js?1"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=blogger" async charset="utf-8"></script>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script>
			window.onbeforeunload = function(){
				reauth();
				/*
				var XHR = new XMLHttpRequest();
					XHR.open("POST", "/blogger/actions/defrost/"+doc.querySelector("#heading").PageID.value, false);
					XHR.setRequestHeader("Content-Type", "application/json");
					XHR.send();
				*/
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
						<span class="tool" data-tab="post-feed">&#xe904;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?php print($brancher->tree($brancher->register)); ?>
					</div>
				</div>
				<div class="tab left" id="post-feed">
					<div class="caption">
						Feed
						<select class="tool right" onchange="reloadFeed(this.value, 1)">
<?php 
	
	$cng = JSON::load("../".BASE_FOLDER."/config.init");
	$laguageSet = $cng['general']['language']['valid'];
	$language = PAGE ? PAGE : $cng['general']['language']['value'];
	$languages = "";
	foreach($laguageSet as $lang){
		if($lang === $language){
			$languages .= "<option value='".$lang."' selected>".$lang."</option>";
		}else $languages .= "<option value='".$lang."'>".$lang."</option>";
	}
	print($languages);
	
?>
						</select>
					</div>
					<div id="feed">
					
<?php

	$limit = 20;
	$page = SUBPAGE ? SUBPAGE : 1;
	$rows = $mySQL->query("
	SELECT SQL_CALC_FOUND_ROWS * FROM `gb_blogfeed` 
	LEFT JOIN `gb_pages` USING(`PageID`) 
	WHERE `language` LIKE '".$language."'
	GROUP BY `ID` 
	ORDER BY `PageId` DESC 
	LIMIT ".(($page-1)*$limit).", ".$limit);
	$count = $mySQL->single_row("SELECT FOUND_ROWS()");
	$count = reset($count);
	$stikers = "";
	foreach($rows as $row):
		$published = explode(",", $row['published']);
		$published = end($published);
		?>
		<a class="sticker" <?=(($published=="Freeze")?"onclick='return false'":"")?> href="/blogger/<?=($language."/".$page."/".$row['ID']."/".$row['language'])?>">
			<img src="<?=$row['preview']?>">
			<div class="header"><?=$row['header']?></div>
			<div class="options">
				<span><?=date("d M, H:i", $row['created'])?></span>
				<span><?=$published?></span>
			</div>
		</a>
	<?endforeach;
	$pagination = "";
	$total = ceil($count/$limit);	// Total pages
	if($total>1){
		if($page>4){
			$j=$page-2;
			$pagination="<a>1</a> ... ";
		}else $j=1;
		for(; $j<$page; $j++) $pagination.="<a>".$j."</a>";					
		$pagination.="<a class='selected'>".$j."</a>";
		if($j<$total){
			$pagination.="<a>".(++$j)."</a>";
			if(($total-$j)>1){
				$pagination.=" ... <a>".$total."</a>";
			}elseif($j<$total){
				$pagination.="<a>".$total."</a>";
			}
		}
	}
	print($stikers."<div onclick='reloadFeed(`".$language."`, event.target.textContent)' class='caption pagination' align='center'>".$pagination."</div>");

?>
					</div>
				</div>
			</div>
		</aside>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="create post" onclick="CreatePost()">&#xf0f6;</span>
				<span class="tool" data-translate="title" title="save post" onclick="SavePost()">&#xe962;</span>
				<span class="tool" data-translate="title" title="caching" onclick="cached()">&#xe90b;</span>
				<span class="tool" data-translate="title" title="delete post" onclick="removePost()">&#xe9ac;</span>
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('blogger')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">
			<textarea id="content">
<?php
	
	$post = [];
	$post['tid'] = 2;
	$post['PageID'] = 0;
	$post['UserID'] = USER_ID;
	$post['created'] = time();
	$post['published'] = "Not published";
	$post['preview'] = "/images/NIA.jpg";
	
	$post['ID'] = $post['header'] = $post['subheader'] = "";
	if(PARAMETER){
		$material = $mySQL->single_row("
		SELECT * FROM gb_pages
		LEFT JOIN gb_blogfeed USING(PageID)
		LEFT JOIN gb_blogcontent USING(PageID)
		WHERE ID=".PARAMETER." AND language LIKE '".SUBPARAMETER."'
		LIMIT 1");
		if(!empty($material)){
			//$mySQL->query("UPDATE `gb_blogfeed` SET `published`=(`published`|4) WHERE `PageID`=".$material['PageID']." LIMIT 1");

			$post = $material;
			$content = gzdecode($post['content']);
			print($content);
		}else print "<h1 style='color:#A22'>Page Not Found or Freeze!</h1>";
	}
		
?>
			</textarea>
		</div>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span class="tool" data-translate="title" title="metadata" data-tab="heading">&#xe906;</span>
					<span class="tool" data-translate="title" title="code editor" data-tab="code-editor">&#xeae4;</span>
					<span class="tool" data-translate="title" title="document map" data-tab="document-map">&#xe902;</span>
					<span class="tool" data-translate="title" title="manual" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<form id="heading" class="tab" autocomplete="off">
				<input name="PageID" type="hidden" value="<?=$post['PageID']?>">
				<div class="caption">
					<div class="toolbar right">
						<label id="pub"><input name="published" type="checkbox" <?php if($post['published']==="Published") print("checked"); ?>></label>
					</div>
					<span>
						ID: <input name="postID" class="tool" value="<?=$post['ID']?>" readonly size="4">
					</span>
					
					<select name="language" class="tool">
<?php
	print($languages);
?>
					</select>
				</div>
				<img id="preview" src="<?=(empty($post['preview']) ? "/images/NIA.jpg" : $post['preview'])?>" height="268px">
				<textarea id="header" name="header" placeholder="header" data-translate="placeholder"><?=($post['header']); ?></textarea>
				<textarea id="subheader" name="subheader" placeholder="subheader" data-translate="placeholder"><?php print($post['subheader']); ?></textarea>
				<fieldset id="created"><legend data-translate="textContent">created</legend>
					<input type="text" name="created-date" value="<?=date("d.m.Y",$post['created'])?>" onfocus="datepicker(event, 'red')" pattern="^\d+\.+\d+\.+\d+$" placeholder="&#xe900;"> 
					<input type="text" name="created-time" value="<?=date("H:i", $post['created'])?>" pattern="^\d+:\d+$" placeholder="&#xe8b5;">
				</fieldset>
				<fieldset><legend data-translate="textContent">template</legend>
					<select name="template" class="tool"> 
					<?foreach(["post","gallery","adsfree","ampfree","story"] as $file):
						if($file === $post['template']):?>
							<option value="<?=$file?>" selected><?=$file?></option>
						<?else:?><option value="<?=$file?>"><?=$file?></option><?endif;
					endforeach?>
					</select>
				</fieldset>
				<div id="keywords">
					<div class="caption">Keywords</div>
					<div id="tags" onclick="addkeyword(event.target)">
<?php

	$keywords = $mySQL->group_rows("SELECT `tag` FROM `gb_keywords` ORDER BY `rating` DESC LIMIT 32");
	$tags = "";
	foreach($keywords['tag'] as $cell){
		$tags .= "<span>".$cell."</span> ";
	}
	print($tags);
	
// Получение тегов из битовых масок по ID записи ~~~~~~~~~~~~
	$tagination = $mySQL->single_row("SELECT * FROM `gb_tagination` WHERE `tid` = ".$post['tid']." LIMIT 1");
	$cnt = count($tagination)-1;
	$IDs = [];
	for($j=0; $j<$cnt; $j++){ for($i=32; $i--;){
		if($tagination[$j] & pow(2, $i)){ $IDs[] = (32*$j) + ($i+1); }
	}}
	$names = $mySQL->group_rows("SELECT `tag` FROM `gb_keywords` WHERE `id` IN (".implode(",",$IDs).")");
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

?>
					</div>
					<input type="hidden" name="tid" value="<?=$post['tid']?>">
					<input type="text" required name="keywords" value="<?=implode(", ", $names['tag'])?>" placeholder="...">
				</div> <!-- End keywords -->
			</form>
			<div id="code-editor" class="tab">
				<div class="caption" data-translate="textContent">code editor</div>
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
			<div id="manual" class="tab">
<?php
	
	include_once("modules/manual/embed.php");

?>
			</div>
		</div>
		<script>
			var	html = ace.edit(doc.querySelector("#rightbar #html-editor"));
				html.setTheme("ace/theme/twilight");
				html.getSession().setMode("ace/mode/html");
				html.setShowInvisibles(false);
				html.setShowPrintMargin(false);
				html.resize();
			
			(function(obj, e){
				var frame = doc.create("iframe","",{ "src":"/editor/embed", "class":"HTMLDesigner", "style":"height:100%;with:100%"});
				frame.onload = function(){
					e = new HTMLDesigner(frame.contentWindow.document, html, doc.querySelector('#docmap'));
					e.setValue(obj.value);
					e.addCSSFile("/modules/editor/tpl/content.css");
					html.session.setValue(e.getValue().trim());
					e.onRefresh = function(){
						html.session.setValue(e.getValue().trim());
					}
					e.save = function(){
						var form = doc.querySelector("#heading");
						saveContent(function(response){
							if(isNaN(response)){
								console.log(response);
								if(confirm("Failed to save. Do you want to retry?")){
									e.save();
								};
							}
						}, (form.PageID.value || false));
					}
					html.session.on('change', function(event){
						if(html.curOp && html.curOp.command.name){
							html_change = true;
							setTimeout(function(){
								if(html_change){
									e.setValue(html.session.getValue());
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