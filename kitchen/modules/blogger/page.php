<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));

	//$readiness = [1=>"author",2=>"editor",4=>"admin",8=>"video-editor"];

	$cng = new config("../".$config->{"base folder"}."/".$config->{"config file"});
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
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=blogger&d[3]=taskboard" async charset="utf-8"></script>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script>window.onbeforeunload = reauth;</script>
		<style>
		.readiness{
			width:0;
			height:0;
			border-radius:50%;
			vertical-align:top;
			display:inline-block;
			border:8px solid transparent;
		}
		.readiness.author{
			border-left-color:#400;
		}
		.readiness.editor{
			border-bottom-color:#820;
		}
		.readiness.admin{
			border-right-color:#D60;
		}
		.readiness.video-editor{
			border-top-color:#E80;
		}
		</style>
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
					WHERE category LIKE 'articles' AND language LIKE '".$language."' 
					GROUP BY ID ORDER BY PageID DESC 
					LIMIT ".(($page-1)*$limit).", ".$limit);
					$count = $mySQL->single_row("SELECT FOUND_ROWS()");
					$count = reset($count);
					foreach($rows as $row):
						$readiness = [];
						if((INT)$row['readiness'] & 1) $readiness[] = "author";
						if((INT)$row['readiness'] & 2) $readiness[] = "editor";
						if((INT)$row['readiness'] & 4) $readiness[] = "admin";
						if((INT)$row['readiness'] & 8) $readiness[] = "video-editor"?>
						<a class="sticker" href="/blogger/<?=($language.'/'.$page.'/'.$row['ID'].'/'.$row['language'])?>">
							<img src="<?=$row['preview']?>">
							<div class="header"><?=$row['header']?></div>
							<div class="options">
								<span><?=date("d M, H:i", $row['created'])?></span>
								<span><label class="readiness <?=implode(" ", $readiness)?>"></label> <?=$row['published']?></span>
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
		<div id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="create post" onclick="CreatePost()">&#xf0f6;</span>
				<?if(PARAMETER):?>
				<span class="tool"><button form="heading" id="save-btn" data-translate="title" title="save post">&#xe962;</button></span>
				<?endif?>
				<!--<span class="tool" data-translate="title" title="caching" onclick="cached()">&#xe90b;</span>-->
				<span class="tool" data-translate="title" title="delete post" onclick="removePost()">&#xe9ac;</span>
			</div>
			<div class="toolbar right">
				<span class="tool" title="TaskBoard" onclick="new Box('{}', 'taskboard/box')"></span>
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('blogger')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">
			<textarea id="content"><?if(PARAMETER){
				$post = $mySQL->single_row("
				SELECT
					PageID,ID,language,published,tid,header,subheader,preview,category,subtemplate,UserID,created,cover,video,
					gb_blogcontent.content AS content,Ads,
					gb_amp.PageID AS amp,
					gb_ina.PageID AS ina
				FROM gb_pages 
				CROSS JOIN gb_blogfeed USING(PageID) 
				CROSS JOIN gb_blogcontent USING(PageID) 
				LEFT JOIN gb_ina USING(PageID) 
				LEFT JOIN gb_amp USING(PageID) 
				WHERE ID=".PARAMETER." AND language LIKE '".SUBPARAMETER."' 
				LIMIT 1");
			}
			if(empty($post)){
				$post = [
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
			}else print gzdecode($post['content'])?></textarea>
		</div>
		<div id="rightbar">
			<aside class="tabbar right">
				<div class="toolbar">
					<span class="tool" data-translate="title" title="metadata" data-tab="heading">&#xe906;</span>
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
			<form id="heading" class="tab" autocomplete="off">
				<input name="PageID" type="hidden" value="<?=$post['PageID']?>" required>
				<input id="image-tab" type="radio" name="snippets-tab" hidden checked>
				<input id="video-tab" type="radio" name="snippets-tab" hidden>
				<div class="caption">
					<span>ID: <input name="postID" class="tool" value="<?=$post['ID']?>" readonly size="2"  required></span>
					<select name="language" class="tool">
						<option value="<?=$post['language']?>" selected><?=$post['language']?></option>
						<?foreach($cng->languageset as $lang) if($lang != $post['language']):?>
							<option value="<?$lang?>"><?=$lang?></option>
						<?endif?>
					</select>
					<label for="video-tab">Video</label>
					<label for="image-tab">Image</label>
				</div>
				
				<img id="image-preview" src="<?=(empty($post['preview']) ? "/images/NIA.jpg" : $post['preview'])?>" height="268px">
				<img id="video-preview" src="<?=(empty($post['video']) ? "/images/lifter.mp4" : $post['video']); ?>" height="268px">

				<textarea id="header" name="header" placeholder="header" data-translate="placeholder" required><?=$post['header']?></textarea>
				<textarea id="subheader" name="subheader" placeholder="subheader" data-translate="placeholder" required><?=$post['subheader']?></textarea>
				<input type="hidden" name="category" value="articles">
				
				<fieldset><legend data-translate="textContent">template</legend>
					<div class="right" align="right">
						<label id="pub"><input name="published" type="checkbox" <?=(($post['published']==="Published")?'checked':'')?> hidden><tt>Published..........</tt></label>
						<br>
						<label id="amp"><input name="amp" type="checkbox" <?=(empty($post['amp'])?'':'checked')?> hidden><tt>Google AMP.........</tt></label>
						<br>
						<label id="ina"><input name="ina" type="checkbox" <?=(empty($post['ina'])?'':'checked')?> hidden><tt>Instant Articles...</tt></label>
						<br>
						<label id="video-cover">
							<input name="video-cover" type="checkbox" <?=(($post['cover']=='video')?'checked':'')?> hidden><tt>Video Cover........</tt>
						</label>
					</div>
					<select name="template" class="tool"> 
					<?foreach(glob('../'.$config->{"base folder"}.'/themes/'.$cng->theme.'/includes/articles/*.html') as $file):$file = pathinfo($file)['filename']?>
						<option <?if($file==$post['template']):?>selected<?endif?> value="<?=$file?>"><?=$file?></option>
					<?endforeach?>
					</select>
					<br>
					<label class="tool" id="ads"><input name="ads" type="checkbox" <?=(($post['Ads']==="YES")?'checked':'')?> hidden><tt data-translate="textContent">ads</tt></label>
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
				<script>
				(function(form){
					form.onsubmit=function(event){
						event.preventDefault();
						SavePost(form);
					}
				})(document.currentScript.parentNode)
				</script>
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
						}, (form.PageID.value || 0), e.getValue().trim());
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