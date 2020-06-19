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
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=taskboard&d[2]=modules" async charset="utf-8"></script>
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
						<?=$brancher->tree($brancher->register)?>
					</div>
				</div>
			</div>
		</aside>
		<? include_once("modules/taskboard/embed.php") ?>
	</body>
</html>