<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));
	
?>

<!DOCTYPE html>
<html>
	<head id="head">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.lab</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/welcome/tpl/welcome.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?php print($config->themes); ?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules" async charset="utf-8"></script>
		<script>
			window.onbeforeunload = reauth;
			window.onload=function(){
				translate.fragment();
				standby.leftbar = "modules-list";
				doc.body.className = standby.bodymode;
				doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
				doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
			}
		</script>
	</head>
	<body class="welcome">
		<aside id="leftbar">
			<a href="/" id="goolybeep"></a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list" data-translate="title" title="modules">&#xe5c3;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?php print($brancher->tree($brancher->register)); ?>
					</div>
				</div>
			</div>
		</aside>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<!--<span class="tool" onclick="new Box('{}', 'installer/box', true)">&#xe905;</span>-->
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('welcome')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">
			
<?php include_once("modules/ads/embed.php"); ?>
			
		</div>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span class="tool" data-tab="manual" data-translate="title" title="manual">&#xf05a;</span>
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