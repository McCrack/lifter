<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));
	$cng = new config("../".$config->{"base folder"}."/".$config->{"config file"});
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
				<span class="tool" title="TaskBoard" onclick="new Box('{}', 'taskboard/box')"></span>
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
			<div id="slide-num"></div>
			<div id="story">
				<!-- TOOLBAR -->
				<div class="left" style="height:100%">
					<form id="all-tools" autocomplete="off">
						<fieldset>
							<legend>
								<span class="tool" title="Add Cards" onclick="addSlide()">&#xe901;</span>
								CARD
								<span class="tool" onclick="removeSlide()" title="Remove Card">&#xf05f;</span>
							</legend>
							<div class="toolbar">
								<select name="flex" class="tool" id="valign" title="Vertical Align">
									<option value="flex-start">flex-start</option>
									<option value="flex-end">flex-end</option>
									<option value="center">center</option>
									<option value="space-between">space-between</option>
									<option value="space-around">space-around</option>
								</select>
							</div>
						</fieldset>
						<fieldset>
							<legend>
								<span class="tool" title="Add Text Block to current Card">
									&#xe901;
									<script>
									(function(btn){
										btn.onclick = function(){
											field = doc.create("div","",{
												"data-animate":"fade-in",
												"data-background":"transparent",
												"style":"background-color:transparent"
											});
											word = doc.create("span","",{
												"contenteditable":"true",
												"data-font":"16px",
												"data-color":"black",
												"data-background":"transparent",
												"style":"font-size:16px;color:black;background-color:transparent"
											});
											word.onfocus=function(){
												word = this;
												field = word.parentNode;
												card = field.parentNode;

												options.reset();
												options['animate'].value = field.dataset.animate;
												options['text-align'].value = field.getCss("text-align");
												options['font-size'].value = word.getCss("font-size");
												options['color'].value = word.style.color;
												options['field-bg'].value = word.style.backgroundColor;
												options['block-bg'].value = field.style.backgroundColor;
												options['flex'].value = card.className;
											}
											word.onpaste = function(event){
												pasteCaption(event);
											}
											field.appendChild(word);
											card.appendChild(field);
										}
									})(document.currentScript.parentNode)
									</script>
								</span>
								BLOCK
								<span class="tool" title="Remove current Text Block">
									&#xf05f;
									<script>
									(function(btn){
										btn.onclick = function(){
											field.parentNode.removeChild(field);
											field = word = null;
										}
									})(document.currentScript.parentNode)
									</script>
								</span>
							</legend>
							<select class="tool" name="animate" id="animate" title="Animation effect">
								<option value="fade-in">fade-in</option>
								<option value="fly-in-top">fly-in-top</option>
								<option value="fly-in-bottom">fly-in-bottom</option>
								<option value="fly-in-right">fly-in-right</option>
								<option value="fly-in-left">fly-in-left</option>
								<option value="pulse">pulse</option>
								<option value="rotate-in-left">rotate-in-left</option>
								<option value="rotate-in-right">rotate-in-right</option>
								<option value="twirl-in">twirl-in</option>
								<option value="whoosh-in-left">whoosh-in-left</option>
								<option value="whoosh-in-right">whoosh-in-right</option>
								<option value="pan-up">pan-up</option>
								<option value="pan-down">pan-down</option>
								<option value="pan-left">pan-left</option>
								<option value="pan-right">pan-right</option>
								<option value="zoom-in">zoom-in</option>
								<option value="zoom-out">zoom-out</option>
								<option disabled value="drop">drop</option>
							</select>
							<div align="right">
								Align:
								<select name="text-align" class="tool" id="align" title="Horizontal Align">
									<option value="left">left</option>
									<option value="right">right</option>
									<option value="center">center</option>
								</select>
							</div>
							<fieldset><legend>Background</legend>
								<label class="color" title="Block Background - Transparent"><input type="radio" name="block-bg" value="transparent" hidden><span style="background-color:transparent"></span></label>
								<label class="color" title="Field Background - AlphaBlack"><input type="radio" name="block-bg" value="alphablack" hidden><span style="background-color:rgba(0,0,0, .5)"></span></label>
								<label class="color" title="Field Background - AlphaWhite"><input type="radio" name="block-bg" value="alphawhite" hidden><span style="background-color:rgba(255,255,255, .8)"></span></label>
								<label class="color" title="Block Background - Silver"><input type="radio" name="block-bg" value="silver" hidden><span style="background-color:silver"></span></label>
								<label class="color" title="Block Background - SteelBlue"><input type="radio" name="block-bg" value="steelblue" hidden><span style="background-color:steelblue"></span></label>
								<label class="color" title="Block Background - SeaGreen"><input type="radio" name="block-bg" value="seagreen" hidden><span style="background-color:seagreen"></span></label>
								<label class="color" title="Block Background - Brown"><input type="radio" name="block-bg" value="brown" hidden><span style="background-color:brown"></span></label>
								<label class="color" title="Block Background - Crimson"><input type="radio" name="block-bg" value="crimson" hidden><span style="background-color:crimson"></span></label>
							</fieldset>
						</fieldset>
						<fieldset>
							<legend>
								<span class="tool" title="Add Field to current Text Block">
									&#xe901;
									<script>
									(function(btn){
										btn.onclick = function(){
											word = doc.create("span","",{
												"contenteditable":"true",
												"data-font":"16px",
												"data-color":"black",
												"data-background":"transparent",
												"style":"font-size:16px;color:black;background-color:transparent"
											});
											word.onfocus=function(){
												word = this;
												field = word.parentNode;
												card = field.parentNode;

												options.reset();
												options['animate'].value = field.dataset.animate;
												options['text-align'].value = field.getCss("text-align");
												options['font-size'].value = word.getCss("font-size");
												options['color'].value = word.style.color;
												options['field-bg'].value = word.style.backgroundColor;
												options['block-bg'].value = field.style.backgroundColor;
												options['flex'].value = card.className;
											}
											word.onpaste = function(event){ pasteCaption(event); }
											field.appendChild(word);
										}
									})(document.currentScript.parentNode)
									</script>
								</span>
								FIELD
								<span class="tool" title="Remove current Field">
									&#xf05f;
									<script>
									(function(btn){
										btn.onclick = function(){
											word.parentNode.removeChild(word);
											word = null;
										}
									})(document.currentScript.parentNode)
									</script>
								</span>
							</legend>
							<div align="right">
								Font Size:
								<select name="font-size" title="Font Size" id="font-size" class="tool">
									<option value="14px">14px</option>
									<option value="15px">15px</option>
									<option value="16px">16px</option>
									<option value="18px">18px</option>
									<option value="20px">20px</option>
									<option value="22px">22px</option>
									<option value="24px">24px</option>
									<option value="26px">26px</option>
									<option value="28px">28px</option>
									<option value="30px">30px</option>
									<option value="32px">32px</option>
									<option value="36px">36px</option>
									<option value="42px">42px</option>
								</select>
							</div>
							<fieldset><legend>Text Color</legend>
								<label class="color" title="Text Color - Black"><input type="radio" name="color" value="black" hidden><span style="background-color:black"></span></label>
								<label class="color" title="Text Color - Grey"><input type="radio" name="color" value="grey" hidden><span style="background-color:grey"></span></label>
								<label class="color" title="Text Color - White"><input type="radio" name="color" value="white" hidden><span style="background-color:white"></span></label>
								<label class="color" title="Text Color - Cornsilk"><input type="radio" name="color" value="cornsilk" hidden><span style="background-color:cornsilk"></span></label>
								<label class="color" title="Text Color - Orange"><input type="radio" name="color" value="orange" hidden><span style="background-color:orange"></span></label>
								<label class="color" title="Text Color - Crimson"><input type="radio" name="color" value="crimson" hidden><span style="background-color:crimson"></span></label>
								<label class="color" title="Text Color - SteelBlue"><input type="radio" name="color" value="steelblue" hidden><span style="background-color:steelblue"></span></label>
								<label class="color" title="Text Color - MediumAquamarine"><input type="radio" name="color" value="mediumaquamarine" hidden><span style="background-color:mediumaquamarine"></span></label>
							</fieldset>
							<fieldset><legend>Background</legend>
								<label class="color" title="Field Background - Transparent"><input type="radio" name="field-bg" value="transparent" hidden><span style="background-color:transparent"></span></label>
								<label class="color" title="Block Background - Black"><input type="radio" name="field-bg" value="black" hidden><span style="background-color:black"></span></label>
								<label class="color" title="Block Background - White"><input type="radio" name="field-bg" value="white" hidden><span style="background-color:white"></span></label>
								<label class="color" title="Field Background - Purple"><input type="radio" name="field-bg" value="purple" hidden><span style="background-color:purple"></span></label>
								<label class="color" title="Field Background - PeachPuff"><input type="radio" name="field-bg" value="peachpuff" hidden><span style="background-color:peachpuff"></span></label>
								<label class="color" title="Field Background - RosyBrown"><input type="radio" name="field-bg" value="rosybrown" hidden><span style="background-color:rosybrown"></span></label>
								<label class="color" title="Field Background - orange"><input type="radio" name="field-bg" value="orange" hidden><span style="background-color:orange"></span></label>
								<label class="color" title="Field Background - Gold"><input type="radio" name="field-bg" value="gold" hidden><span style="background-color:gold"></span></label>
							</fieldset>
						</fieldset>
						<script>
						var options;
						(function(form){
							options = form;
							form['color'].forEach(function(inp){
								inp.onchange=function(){
									word.style.color = this.value;
									word.dataset.color = this.value;
								}
							});
							form['field-bg'].forEach(function(inp){
								inp.onchange=function(){
									word.dataset.background = this.value;
									word.style.backgroundColor = this.value;
								}
							});
							form['block-bg'].forEach(function(inp){
								inp.onchange=function(){
									field.dataset.background = this.value;
									field.style.backgroundColor = this.value;
								}
							});
							form['font-size'].onchange=function(){
								word.style.fontSize = this.value;
								word.dataset.font = this.value;
							}
							form['text-align'].onchange=function(){
								field.className = this.value;
							}
							form['animate'].onchange=function(){
								field.dataset.animate = this.value;
							}
							form['flex'].onchange = function(){
								card.className = this.value;
							}
						})(document.currentScript.parentNode)
						</script>
					</form>
				</div>
				<div id="slideshow">
				<?php
				if(PARAMETER):
					$post = $mySQL->single_row("SELECT * FROM gb_pages LEFT JOIN gb_blogfeed USING(PageID) LEFT JOIN gb_blogcontent USING(PageID) WHERE ID=".PARAMETER." AND language LIKE '".SUBPARAMETER."' LIMIT 1");
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
					}else print gzdecode($post['content'])?>
					<script>
					(function(slideshow){
						slideshow.querySelectorAll("figure>figcaption>div span").forEach(function(itm){
							itm.contentEditable = true;
							itm.onfocus = function(){
								word = itm;
								field = word.parentNode;
								card = field.parentNode;

								options.reset();
								options['animate'].value = field.dataset.animate;
								options['text-align'].value = field.getCss("text-align");
								options['font-size'].value = word.getCss("font-size");
								options['color'].value = word.style.color;
								options['field-bg'].value = word.style.backgroundColor;
								options['block-bg'].value = field.style.backgroundColor;
								options['flex'].value = card.className;
							}
							itm.onpaste = function(event){
								pasteCaption(event);
							}
						});
					})(document.currentScript.parentNode)
					</script>
					<?endif?>
				</div>
			</div>
			<div class="caption" align="center">
				<button class="slideshot" data-dir="-1">❮ Prev</button>
				<button class="slideshot" data-dir="1">Next ❯</button>

				<button data-dir="-1" class="swap">❮</button> Swap <button data-dir="1" class="swap">❯</button>
			</div>
			<script>
			(function(container){
				var tm,moveslide,
					slideshow = container.querySelector("#slideshow"),
					swap = container.querySelectorAll(".caption>button.swap"),
					button = container.querySelectorAll(".caption>button.slideshot"),
					slide = container.querySelector("#slide-num"),
					valign = document.querySelector("#valign");

				slideshow.current = 0;
				slideshow.amount=slideshow.querySelectorAll("figure").length;
				button[0].onclick = button[1].onclick = function(){
					var dir = Number(this.dataset.dir);
					if( (slideshow.current+dir) >= 0 && (slideshow.current+dir) < slideshow.amount){
						slideshow.current += dir;
						//slideshow.scrollLeft += dir;
						slideShot();
					}
				}
				swap[0].onclick = swap[1].onclick = function(){
					var figures = slideshow.querySelectorAll("figure");
					if(figures.length){
						figures[slideshow.current].swap(this.dataset.dir>0);
					}
				}
				function slideShot(){
					var offset = (slideshow.offsetWidth * slideshow.current);
					cancelAnimationFrame(moveslide);
					moveslide = requestAnimationFrame(function scrollSlide(){
						if(((offset - slideshow.scrollLeft) > 16) || ((slideshow.scrollLeft - offset) > 16)){
							slideshow.scrollLeft += (offset - slideshow.scrollLeft)/8;
							moveslide = requestAnimationFrame(scrollSlide);
						}else slideshow.scrollLeft = offset;
					});
					var figures = slideshow.querySelectorAll("figure")
					if(figures.length){
						card = figures[slideshow.current].querySelector("figcaption");
						valign.value = card.getCss("justify-content");
					}
					slide.textContent = slideshow.current + 1;
					field = null;
				}
				slideShot();
				slideshow.onscroll = function(){
					clearTimeout(tm);
					tm = setTimeout(function(){
						slideshow.current = Math.round(slideshow.scrollLeft/slideshow.offsetWidth);
						slideShot();
					},200);
				}
			})(document.currentScript.parentNode)
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
				<img class="portrait" src="<?=(empty($post['portrait']) ? "https://lifter.com.ua/images/portrait.jpg" : $post['portrait'])?>" height="268px">
				
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