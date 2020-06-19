<?php

    $brancher->auth() or die(include_once("modules/auth/page.php"));
	
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.developer</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/developer/tpl/developer.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?php print($config->themes); ?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/developer/tpl/developer.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=uploader" async></script>
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
						<span class="tool" data-tab="explorer">&#xf07b;</span>
				    </div>
                </div>
                <div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
<?php
	print($brancher->tree($brancher->register));
?>
					</div>
				</div>
				<div class="tab left" id="explorer">
					<div class="caption"><span>Explorer</span></div>
<?php

	$tree = "";
	foreach(scandir("../") as $subdomain){
		if(($subdomain!=".") && ($subdomain!="..") && is_dir("../".$subdomain)){
			$tree .= "<a class='domain' href='/developer/".$subdomain."'>".$subdomain."</a>";
			if($subdomain===PAGE){
				$tree .= "<div class='dom-root' oncontextmenu='return fs_Actions(event)' onclick='Open(event.target)'> </div>";
			}
		}
	}
	print($tree);
?>
				</div>
            </div>
        </aside>
		<div id="topbar" class="caption" style="padding:0px">
            
        </div>
		<div id="environment">
		
		</div>
    </body>
</html>