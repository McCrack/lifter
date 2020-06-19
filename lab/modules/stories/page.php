<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));
	$cng = new config("../".$config->{"base folder"}."/config_2018.init");
?>

<!DOCTYPE html>
<html>
	<head id="head">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.stories</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/stories/tpl/stories.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/stories/tpl/stories.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules" async charset="utf-8"></script>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script>
			var card,field,word;
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
						<span class="tool" data-tab="post-feed">&#xe904;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?=$brancher->tree($brancher->register)?>
					</div>
				</div>
				<div class="tab left" id="post-feed">
					<div class="caption">
						Feed
						<select class="tool right" onchange="reloadFeed(this.value, 1)">
						<?php
						$language = PAGE ? PAGE : $cng->language?>
						<option value="<?=$language?>" selected><?=$language?></option>
						<?foreach($cng->languageset as $lang) if($lang != $language):?>
							<option value="<?$lang?>"><?=$lang?></option>
						<?endif?>
						</select>
					</div>
					<div id="feed">
					<?php
					$limit = 20;
					$page = SUBPAGE ? SUBPAGE : 1;
					$rows = $mySQL->query("
					SELECT SQL_CALC_FOUND_ROWS * FROM gb_blogfeed 
					CROSS JOIN gb_pages USING(PageID)
					WHERE category LIKE 'stories' AND language LIKE '".$language."'
					GROUP BY ID ORDER BY PageID DESC
					LIMIT ".(($page-1)*$limit).", ".$limit);
					$count = $mySQL->single_row("SELECT FOUND_ROWS()");
					$count = reset($count);
					foreach($rows as $row):?>
						<a class="sticker" href="/stories/<?=($language.'/'.$page.'/'.$row['ID'].'/'.$row['language'])?>">
							<img src="<?=$row['preview']?>">
							<div class="header"><?=$row['header']?></div>
							<div class="options">
								<span><?=date("d M, H:i", $row['created'])?></span>
								<span><?=$row['published']?></span>
							</div>
						</a>
					<?endforeach;
					$pagination = "";
					$total = ceil($count/$limit);
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
					?>
					<div onclick="reloadFeed('<?=$language?>', event.target.textContent)" class="caption pagination" align="center">
						<?=$pagination?>
						</div>
					</div>
				</div>
			</div>
		</aside>
		<div id="topbar" class="caption">
			<div class="toolbar">
				<select name="flex" class="tool" id="valign" title="Vertical Align" onchange="editor.slideshot(-1)">
					<option value="flex-start">flex-start</option>
					<option value="flex-end">flex-end</option>
					<option value="center">center</option>
					<option value="space-between">space-between</option>
					<option value="space-around">space-around</option>
				</select>
			</div>
			<div class="toolbar">
				<span class="tool" data-translate="title" title="create post" onclick="CreatePost()">&#xf0f6;</span>
				<span class="tool" data-translate="title" title="save post" onclick="SavePost()">💾</span>
				<!--<span class="tool" data-translate="title" title="caching" onclick="cached()">&#xe90b;</span>-->
				<span class="tool" data-translate="title" title="delete post" onclick="removePost()">&#xe9ac;</span>
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('stories')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">
			<?if(PARAMETER){
				$post = $mySQL->single_row("SELECT * FROM gb_pages LEFT JOIN gb_blogfeed USING(PageID) LEFT JOIN gb_blogcontent USING(PageID) WHERE ID=".PARAMETER." AND language LIKE '".SUBPARAMETER."' LIMIT 1");
				if(empty($post)) $post = [
					"ID"		=>"", 
					"header"	=>"", 
					"subheader"	=>"",
					"tid"		=>2,
					"PageID"	=>0,
					"UserID"	=>USER_ID,
					"created"	=>time(),
					"published"	=>"Not published",
					"preview"	=>"/images/NIA.jpg"
				];
			}?>
			<textarea id="editor"><?=gzdecode($post['content'])?></textarea>
			<script>
			var editor;
			(function(environment){
				var frame = doc.create("iframe","",{ "src":"/stories/editor", "class":"HTMLDesigner"});
				frame.onload = function(){
					editor = frame.contentWindow.document;
				}
				environment.replaceChild(frame,  environment.querySelector("#editor") );
			})(document.currentScript.parentNode);
			</script>
		</div>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span class="tool" data-translate="title" title="metadata" data-tab="heading">&#xe906;</span>
					<span class="tool" data-tab="manual" data-translate="title" title="manual">&#xf05a;</span>
				</div>
			</aside>
			<form id="heading" class="tab" autocomplete="off">
				<input name="PageID" type="hidden" value="<?=$post['PageID']?>">
				<input id="landscape-tab" type="radio" name="snippets-tab" hidden checked>
				<input id="portrait-tab" type="radio" name="snippets-tab" hidden>
				<div class="caption">
					<span>ID: <input name="postID" class="tool" value="<?=$post['ID']?>" readonly size="2"></span>
					<select name="language" class="tool">
						<option value="<?=$post['language']?>" selected><?=$post['language']?></option>
						<?foreach($cng->languageset as $lang) if($lang != $post['language']):?>
							<option value="<?$lang?>"><?=$lang?></option>
						<?endif?>
					</select>

					<label for="portrait-tab">portrait</label>
					<label for="landscape-tab">landscape</label>
				</div>
				<img class="landscape" src="<?=(empty($post['preview']) ? "/images/NIA.jpg" : $post['preview'])?>" height="268px">
				
				<textarea id="header" name="header" placeholder="header" data-translate="placeholder"><?=$post['header']?></textarea>
				<textarea id="subheader" name="subheader" placeholder="subheader" data-translate="placeholder"><?=$post['subheader']?></textarea>
				<input type="hidden" name="category" value="stories">
				<fieldset><legend data-translate="textContent">template</legend>
					<label class="tool right" id="pub"><input name="published" type="checkbox" <?=(($post['published']==="Published")?'checked':'')?>></label>
					<select name="template" class="tool"> 
					<?foreach(scandir('../'.$config->{"base folder"}.'/themes/'.$cng->theme.'/includes/stories') as $file):
						$file = explode(".", $file);
						if(end($file) === "html") if($file[0] === $post['template']):?>
							<option value="<?=$file[0]?>" selected><?=$file[0]?></option>
						<?else:?><option value="<?=$file[0]?>"><?=$file[0]?></option><?endif;
					endforeach?>
					</select>
				</fieldset>
				<fieldset id="created"><legend data-translate="textContent">created</legend>
					<input type="text" name="created-date" value="<?=date("d.m.Y",$post['created'])?>" onfocus="datepicker(event, 'red')" pattern="^\d+\.+\d+\.+\d+$" placeholder="&#xe900;"> 
					<input type="text" name="created-time" value="<?=date("H:i", $post['created'])?>" pattern="^\d+:\d+$" placeholder="&#xe8b5;">
				</fieldset>
				<fieldset><legend data-translate="textContent">author</legend>
					<select name="author" class="tool">
					<?php
					$authors = $mySQL->query("SELECT UserID, Name FROM gb_staff LEFT JOIN gb_community USING(CommunityID)");
					foreach($authors as $author) if($author['UserID']===$post['UserID']):?>
						<option value="<?=$author['UserID']?>" selected><?=$author['Name']?></option>
					<?else:?>
						<option value="<?=$author['UserID']?>"><?=$author['Name']?></option>
					<?endif?>					
					</select>
				</fieldset>
				<div id="keywords">
					<div class="caption">Keywords</div>
					<div id="tags" onclick="addkeyword(event.target)">
					<?php
					$keywords = $mySQL->group_rows("SELECT tag FROM gb_keywords ORDER BY rating DESC LIMIT 32");
					foreach($keywords['tag'] as $cell):?>
						<span><?=$cell?></span>
					<?endforeach?>
					</div>
					<input type="hidden" name="tid" value="<?=$post['tid']?>">
					<?php
					$tagination = $mySQL->single_row("SELECT * FROM gb_tagination WHERE tid = ".$post['tid']." LIMIT 1");
					$cnt = count($tagination)-1;
					$IDs = [];
					for($j=0; $j<$cnt; $j++){ for($i=32; $i--;){
						if($tagination[$j] & pow(2, $i)){ $IDs[] = (32*$j) + ($i+1); }
					}}
					$tags = $mySQL->group_rows("SELECT tag FROM gb_keywords WHERE id IN (".implode(",",$IDs).")")['tag'];
					?>
					<input type="text" required name="keywords" value="<?=implode(", ", $tags)?>" placeholder="...">
				</div> <!-- End keywords -->
			</form>
			<div id="manual" class="tab">
			<? include_once("modules/manual/embed.php") ?>
			</div>
		</div>
		<script>
			var	html = ace.edit(doc.querySelector("#rightbar #html-editor"));
				html.setTheme("ace/theme/twilight");
				html.getSession().setMode("ace/mode/html");
				html.setShowInvisibles(false);
				html.setShowPrintMargin(false);
				html.resize();
			function SaveTemplate(){
				XHR.push({
					"Content-Type":"text/html",
					"addressee":"/stories/actions/save-template",
					"body":html.session.getValue(),
					"onsuccess":function(response){
						if(isNaN(response)){
							alertBox(response);
						}
					}
				});
			}
		</script>
	</body>
</html>