<?php
	
	function patterns_tree($path="patterns/html"){
		$items = $dirs = "";
		foreach(scandir($path) as $file){
			if(is_file($path."/".$file)){
				$items .= "<a class='pattern-file' data-path='".$path."'>".$file."</a>";
			}elseif(is_dir($path."/".$file) && ($file!="." && $file!="..")){
				$dirs .= "<label class='pattern-folder'>".$file."</label><div class='root'>".patterns_tree($path."/".$file)."</div>";
			}
		}
		return $dirs."".$items;
	}

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
	$cng = JSON::load("../".BASE_FOLDER."/config.init");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.front-end</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/front-end-app/tpl/front-app.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
			
		<script type="text/javascript" src="/js/md5.js"></script>
		<script type="text/javascript" src="/js/gbAPI.js"></script>
		<script type="text/javascript" src="/tpls/main.js"></script>
		
		<script src="/modules/front-end-app/tpl/front-app.js"></script>
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
	
	$cng = JSON::load("../".BASE_FOLDER."/config.init");
	$laguageSet = $cng['general']['language']['valid'];
	$language = PAGE ? PAGE : $cng['general']['language']['value'];
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
		if(is_array($items[$offset])):?>
			<div class="root">
			<?foreach($items[$offset] as $key=>$val):?>
				<a 
					href="/front-end-app/<?=($val['language'].'/'.$val['name'])?>" 
					class="<?=(($val['Published']==='Published')?'green ':'')?>tree-<?=(($val['name']===SUBPAGE) ? 'root-' : '')?>item"><?=(empty($val['header']) ? $val['name'] : $val['header'])?></a>
				<?staticTree($items, $key);
			endforeach?>
			</div>
		<?endif;
	}

	$rows = $mySQL->tree("SELECT parent,name,header,language,Published FROM gb_sitemap CROSS JOIN gb_static USING(PageID) WHERE language LIKE '".$language."' ORDER BY PageID ASC", "name", "parent");
	staticTree($rows);
?>
						</div>
						<script>
						(function(explorer){
							explorer.onscroll=function(){
								standby.mapScrollTop = explorer.scrollTop;
							}
						})(document.currentScript.parentNode)
						</script>
					</div>
				</div>
			</div>
		</aside>
		<form id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="save" onclick="saveContent(<?=$post['PageID']?>)">&#xe962;</span>
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('front-end-app')">&#xf013;</span>
			</div>
		</form>
		<xmp id="environment"><?=$content?></xmp>
		<script>
			var editor = (function(node){ return ace.edit(node); })(doc.scripts[doc.scripts.length-1].previous());
			editor.setTheme("ace/theme/twilight");
			editor.getSession().setMode("ace/mode/html");
			editor.setShowInvisibles(false);
			editor.setShowPrintMargin(false);
			editor.resize();
		</script>
		<div id="rightbar">
			<aside class="tabbar right">
				
			</aside>
			<div id="patterns" class="tab" style="display:block">
				<div class="caption">
					Patterns
					<div class="toolbar right">
						<span class="tool" data-translate="title" title="patterns" onclick="showPatern('html')">&#xe8ab;</span>
					</div>
				</div>
				<div class="root">
				<?=patterns_tree()?>
				<script>
				(function(root){
					root.ondblclick = function(event){
						event.preventDefault();
						XHR.push({
							"protect":false,
							"addressee":"/patterns/actions/get-pattern?path="+event.target.dataset.path+"/"+event.target.textContent, 
							"onsuccess":function(response){
								editor.session.insert(editor.selection.getCursor(), response);
							}
						});
					}
				})(document.currentScript.parentNode)
				</script>
				</div>
			</div>
		</div>
	</body>
	<script>
	doc.onkeydown=function(event){
		if((event.ctrlKey || event.metaKey) && (event.keyCode===83)){
			event.preventDefault();
			saveContent(<?=$post['PageID']?>);
		}
	}
	</script>
</html>