<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));
	
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?php print($config->themes); ?>/theme.css">
		
		<!--<link rel="stylesheet" type="text/css" href="/modules/[ this module name ]/tpl/[ this module name ].css">-->
			
		<script type="text/javascript" src="/js/md5.js"></script>
		<script type="text/javascript" src="/js/gbAPI.js"></script>
		<script type="text/javascript" src="/tpls/main.js"></script>
		
		<!--<script type="text/javascript" src="/modules/[ this module name ]/tpl/[ this module name ].js"></script>-->
		<!--<script src="/js/ace/src-min/ace.js" type="text/javascript" charset="utf-8"></script>-->
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules" async charset="utf-8"></script>
		<script>
			window.onbeforeunload = reauth;
			window.onload = function(){
				translate.fragment();
			}
		</script>
	</head>
	<body>
		<aside id="leftbar">
			<a href="/" id="goolybeep">	</a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list">M</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption"><span data-translate="textContent">modules</span><span title="register" class="tool right" onclick="regBox()" data-translate="title">_</span></div>
					<div class="root">
						<?php print($brancher->tree($brancher->register)); ?>
					</div>
				</div>
			</div>
		</aside>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="" onclick="">T</span>
			</div>
		</div>
		<div id="environment">
			
		</div>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span class="tool" data-tab="manual">M</span>
				</div>
			</aside>
			<div id="manual" class="tab" style="display:block;">
<?php
	
	include_once("modules/manual/embed.php");

?>
			</div>
		</div>
	</body>
</html>