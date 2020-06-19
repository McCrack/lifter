<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));

?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.manuals</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?php print($config->themes); ?>/theme.css">
		
		<link rel="stylesheet" type="text/css" href="/modules/manual/tpl/manual.css">
			
		<script type="text/javascript" src="/js/md5.js"></script>
		<script type="text/javascript" src="/js/gbAPI.js"></script>
		<script type="text/javascript" src="/tpls/main.js"></script>
		
		<script type="text/javascript" src="/modules/manual/tpl/manual.js"></script>
		<script src="/modules/editor/tpl/editor.js"></script>
		<script src="/js/ace/src-min/ace.js" type="text/javascript" charset="utf-8"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules" async></script>
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
						<span class="tool" data-tab="materials-list">&#xe9bc;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?php print($brancher->tree($brancher->register)); ?>
					</div>
				</div>
				<div class="tab left" id="materials-list">
					<div class="caption" data-translate="textContent">materials</div>
<?php
	
	$mySQL->real_query("SELECT * FROM `gb_documentation`");
	$list=array();
	if($result=$mySQL->store_result()){
		while($row = $result->fetch_assoc()){
		    foreach($row as $key=>$val){
				$list[$row['pid']][$row['id']][$key]=$val;
			}
		}
		$result->free();
	}
	print(materialsTree($list));
	
?>
				</div>
			</div>
		</aside>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="create material" onclick="createMaterial()">&#xf0f6;</span>
				<span class="tool" data-translate="title" title="save" onclick="saveMaterial()">&#xe962;</span>
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('manual')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">
			<textarea id="content">
			<?php

if(PAGE){
	$doc = $mySQL->single_row("SELECT `content` FROM `gb_documentation` WHERE `id` = '".PAGE."' LIMIT 1");
}
print($doc['content']);

			?></textarea>
		</div>
		<div id="rightbar">
			<aside class="tabbar right">
				<div class="toolbar">
					<span class="tool" data-tab="editor">&#xeae4;</span>
				</div>
			</aside>
			<div class="tab" style="display:block">
				<div class="caption">HTML code</div>
				<div id="editor"></div>
			</div>
		</div>
		<script>
			var	html = ace.edit(doc.querySelector("#rightbar #editor"));
				html.setTheme("ace/theme/twilight");
				html.getSession().setMode("ace/mode/html");
				html.setShowInvisibles(false);
				html.setShowPrintMargin(false);
				html.resize();
			(function(obj, e){
				var frame = doc.create("iframe","",{ src:"/editor/embed","class":"HTMLDesigner", style:"height:100%;with:100%"});
				frame.onload = function(){
					e = new HTMLDesigner(frame.contentWindow.document);
					e.addCSSFile("/modules/manual/tpl/manual.css");
					e.body.style.cssText = "background-color:#169;color:white";
					e.setValue(obj.value);
					html.session.setValue(e.getValue().trim());
					e.onRefresh = function(){
						html.session.setValue(e.getValue().trim());
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

<?php

function materialsTree(&$items, $offset=0){
	if(is_array($items[$offset])){
		$result.="<div class='root'>";
		foreach($items[$offset] as $key=>$val){
			$result.="<a href='/manual/".$val['id']."' class='tree-item'><span data-translate='textContent'>".$val['title']."</span>.".$val['language']."</a>".materialsTree($items, $key);
		}
		$result.="</div>";
		return $result;
	}
}
	
?>