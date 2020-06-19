<?php

	$brancher->auth() or die(include_once("modules/auth/page.php"));

?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.wordlist</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/wordlist/tpl/wordlist.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/wordlist/tpl/wordlist.js"></script>
		<script src="/js/ace/src-min/ace.js" type="text/javascript" charset="utf-8"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=wordlist" onload="translate.fragment()" defer charset="utf-8"></script>
		<script>
			window.onbeforeunload = reauth;
		</script>
	</head>
	<body class="<?=$standby[SECTION]['bodymode']?>">
		<aside id="leftbar">
			<a href="/" id="goolybeep"></a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list">&#xe5c3;</span>
						<span class="tool" data-tab="word-list">&#xe431;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?=$brancher->tree($brancher->register)?>
					</div>
				</div>
				<div class="tab left" id="word-list">
					<div class="caption"><span data-translate="textContent">wordlist</span></div>
					<div class="root">
					<?foreach(scandir("../") as $subdomain) if($subdomain!="." && $subdomain!=".."):?>
						<a href="/wordlist/<?=$subdomain?>" class="tree-root-item"><?=$subdomain?></a>
						<?if(is_dir("../".$subdomain."/localization")):?>
						<div class="root">
						<?foreach(scandir("../".$subdomain."/localization") as $file) if(is_file("../".$subdomain."/localization/".$file)):
							$file = reset(explode(".",$file))?>
							<a href="/wordlist/<?=$subdomain?>/<?=$file?>" class="tree-item"><?=$file?></a>
						<?endif?>
						</div>
						<?endif?>
					<?endif?>
					</div>
				</div>
			</div>
		</aside>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="create wordlist" onclick="createWordlist()">&#xf0f6;</span>
				<span class="tool" data-translate="title" title="save wordlist" onclick="saveWordlist()">&#xe962;</span>
				<span class="tool" data-translate="title" title="remove wordlist" onclick="removeWordlist()">&#xe9ac;</span>
				<span class="tool" data-translate="title" title="add language" onclick="addLanguage()">&#xf0b6;</span>
				<span class="tool" data-translate="title" title="show pattern" onclick="showPattern(wordlistToJson(), 'jsontowordlist')">&#xe8ab;</span>
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('wordlist')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">
			<table style="display:<?=$display?>" id="wordlist" width="100%" rules="cols" cellpadding="4" cellspacing="0" bordercolor="#AAA">
				<thead>
					<tr>
						<td width="26px"></td>
						<th>Keys</th>
						<?php
						$wl = JSON::load("../".PAGE."/localization/".SUBPAGE.".json");
						$keys = [];
						foreach($wl as $lang=>$list):
							$keys=array_merge($keys,$list)?>
							<th><?=$lang?></th>
						<?endforeach?>
						<td width="26px"></td>
					</tr>
				</thead>
				<tbody>
				<?if(PAGE) if(SUBPAGE) foreach($keys as $key=>$val):?>
					<tr>
						<th bgcolor="white"><span title="add row" data-translate="title" class="tool" onclick="addRow(this)">&#xe908;</span></th>
						<td align="center" contenteditable="true"><?=$key?></td>
						<?foreach($wl as $lang=>$list):?>
						<td contenteditable="true"><?=$wl[$lang][$key]?></td>
						<?endforeach?>
						<th bgcolor="white"><span title="delete row" data-translate="title" class="tool" onclick="deleteRow(this)">&#xe907;</span></th>
					</tr>
				<?endforeach?>
				</tbody>
			</table>
		</div>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span class="tool" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<div id="manual" class="tab" style="display:block;">
			<? include_once("modules/manual/embed.php") ?>
			</div>
		</div>
	</body>
</html>