<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" media="all" type="text/css" href="/tpls/skins/<?php print($config->skin); ?>/skin.css">
		
		<!--<link rel="stylesheet" type="text/css" href="/modules/[ this module name ]/tpl/[ this module name ].css">-->
			
		<script type="text/javascript" src="/js/md5.js"></script>
		<script type="text/javascript" src="/js/gbAPI.js"></script>
		<script type="text/javascript" src="/tpls/main.js"></script>
		
		<!--<script type="text/javascript" src="/modules/[ this module name ]/tpl/[ this module name ].js"></script>-->
		<!--<script src="/js/ace/src-min/ace.js" type="text/javascript" charset="utf-8"></script>-->
		
		<script> window.onbeforeunload=function(){ reauth(); } </script>
	</head>
	<body>
<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));
	
	$doc = $mySQL->single_row("SELECT `content` FROM `gb_documentation` WHERE `title` LIKE '".SECTION."' ORDER BY (POW(2,`language`-1) & ".LANG_MASK.") LIMIT 1");
	
	ob_start();
	
?>
		<aside id="leftbar">
			<div id="tabbar" class="h-panel" onclick="openTab(event.target, 'leftbar')">
				<span class="tab-btn" data-tab="modules-list">M</span>
			</div>
			<div class="tab" id="modules-list" onclick="executeModule(event.target)">
				<div class="caption"><span data-translate="nodeValue">modules</span><span title="register" class="tool right" onclick="regBox()" data-translate="title">_</span></div>
				<div class="root">
					<?php print($brancher->tree($brancher->register)); ?>
				</div>
			</div>
		</aside>
		<div id="topbar" class="h-panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="" onclick="">T</span>
			</div>
		</div>
		<div id="environment">
			
		</div>
		<div id="rightbar">
			<aside class="v-panel right" onclick="openTab(event.target, 'rightbar')">
				<span class="tab-btn" data-tab="manual">M</span>
			</aside>
			<div id="manual" class="tab blue-bg" style="display:block;">
				<div class="caption">Manual</div>
				<div class="content">
					<?php print($doc['content']); ?>
				</div>
			</div>
		</div>

<?php

	$tpl = ob_get_contents();
	ob_end_clean();
	
	$tpl = new HTMLDocument($tpl);
	
	$wordlist = new Wordlist(array("base","modules"));
	$wordlist->translateDocument($tpl);
	
	print($tpl);

?>
		<script>
			var SECTION = "<?php print(SECTION); ?>";
			getStandby();
		</script>
	</body>
</html>