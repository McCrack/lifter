<?php

	$brancher->auth() or die(include_once("modules/auth/page.php"));

	$cng = new config("../".$config->{"base folder"}."/".$config->{"config file"});

?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.messenger</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/messenger/tpl/messenger.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/messenger/tpl/messenger.js"></script>
		<script src="/js/ace/src-min/ace.js" type="text/javascript" charset="utf-8"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=wordlist" onload="translate.fragment()" defer charset="utf-8"></script>
		<script>
			window.onbeforeunload = reauth;
		</script>
		<script defer id="facebook-jssdk" src="//connect.facebook.net/ru_RU/sdk.js" onload="
		window.fbAsyncInit = FB.init({
			appId:<?=$cng->{'fb:app_id'}?>,
			xfbml:true,
			cookie:true,
			status:true,
			version:'v2.9'
		})"></script>
	</head>
	<body class="<?=$standby[SECTION]['bodymode']?>">
		<div id="fb-root"></div>
		<aside id="leftbar">
			<a href="/" id="goolybeep"></a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list">&#xe5c3;</span>
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
		<div id="topbar" class="panel">
			<div class="toolbar">
				<!--<span class="tool" data-translate="title" title="" onclick="">&#xf0f6;</span>-->
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('messenger')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">
			<button>
			Go
			<script>
			(function(btn){
				btn.onclick=function(){
					FB.ui({
  						"method":"send",
  						"messaging_type":"RESPONSE",
  						"recipient":{
    						"id":"997893133576622"
  						},
  						"message":{
    						"text": "Hello Master!"
  						}
  					}, function(response){
						alert("Hello Master");
					});
				}
			})(document.currentScript.parentNode)
			</script>
			</button>
		</div>
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