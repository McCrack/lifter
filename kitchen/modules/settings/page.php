<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));

?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.settings</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/settings/tpl/settings.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?php print($config->themes); ?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/settings/tpl/settings.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=settings" onload="translate.fragment()" defer  charset="utf-8"></script>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script>
			window.onbeforeunload = reauth;
		</script>
	</head>
	<body>
		<aside id="leftbar">
			<a href="/" id="goolybeep"></a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list">&#xe5c3;</span>
						<span class="tool" data-tab="domains-list">&#xe9bc;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?php print($brancher->tree($brancher->register)); ?>
					</div>
				</div>
				<div class="tab left" id="domains-list">
					<div class="caption"><span data-translate="textContent">subdomains</span></div>
					<div class="root">
<?php 

foreach(scandir("../") as $subdomain){
	if(file_exists("../".$subdomain."/config.init")){
		if($subdomain===PAGE){
			$items .= "<a href='/settings/".$subdomain."' class='tree-root-item'>".$subdomain."</a><div class='root'>";
			foreach(scandir("../".$subdomain."/modules") as $module){
				if(is_dir("../".$subdomain."/modules/".$module) && ($module!=".") && ($module!="..")){
					if($module===SUBPAGE){
						$items .= "<a href='/settings/".$subdomain."/".$module."' class='tree-root-item'>".$module."</a>";
					}else $items .= "<a href='/settings/".$subdomain."/".$module."' class='tree-item'>".$module."</a>";
				}
			}
			$items .= "</div>";
		}else $items .= "<a href='/settings/".$subdomain."' class='tree-item'>".$subdomain."</a>";
	}
}
print($items);
	
?>
					</div>
				</div>
			</div>
		</aside>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="save" onclick="saveSettings()">&#xe962;</span>
				<span class="tool" data-translate="title" title="show pattern" onclick="showPattern(settingsToJson(), 'JsonToSettings')">&#xe8ab;</span>
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
			</div>
		</div>
		<div id="environment">
			<table spellcheck="false" class="settings" width="100%" rules="cols" cellpadding="6" cellspacing="0" bordercolor="#999">
				<col width="26"><col width="154"><col><col><col width="26">
				<thead>
					<tr bgcolor="#CCC" style="color:#FFF">
						<th colspan="2">Key</th>
						<th>Value</th>
						<th colspan="2">Valid</th>
					</tr>
				</thead>
				<tbody>
<?php
	
	if(SUBPAGE){
		if(file_exists("../".PAGE."/brancher.json")){
			$brancher = new Brancher("../".PAGE."/brancher.json");
			$map = $brancher->createMap(SUBPAGE);
			if(empty($map)){
				$map[] = SUBPAGE;
				$brancher->register[SUBPAGE] = [
					"options"=>[
						"status"=>[
							"type"=>"enum",
							"value"=>"disabled",
							"valid"=>["enabled", "disabled"]
						],
						"access"=>[
							"type"=>"set",
							"value"=>"admin, developer",
							"valid"=>["admin", "author", "client", "courier", "developer", "manager", "mediator", "partner", "performer", "topmanager"]
						],
						"mode"=>[
							"type"=>"enum",
							"value"=>"page",
							"valid"=>["page", "box", "embed"]
						]
					],
					"branch"=>[]
				];
			}
			$branch = brancherTree($brancher->register, $map);
			
			reset($map);
			$settings = &$brancher->getModule($brancher->register, $map);
			$settings = [SUBPAGE=>$settings['options']];
		}
	}elseif(PAGE){
		$settings = JSON::load("../".PAGE."/config.init");
	}
	$subdomains = $modules = $themes = [];
	foreach(scandir("..") as $folder){
		if(($folder!="." && $folder!="..") && is_dir("../".$folder)) $subdomains[] = $folder;
	}
	foreach(scandir("../".PAGE."/modules") as $module){
		if(is_dir("../".PAGE."/modules/".$module) && $module!="." && $module!="..") $modules[] = $module;
	}
	foreach(scandir("../".PAGE."/themes") as $dir){
		if(is_dir("../".PAGE."/themes/".$dir) && $dir!="." && $dir!="..") $themes[] = $dir;
	}
	$color=16777215;
	foreach($settings as $sname=>$section){
		$rows.="<tr style='color:#EEE;background-color:#444' align='center'><td class='section' data-section='".$sname."' colspan='5'><b data-translate='textContent'>".$sname."</b></td></tr>";
		foreach($section as $key=>$val){
			$rows.="
			<tr bgcolor='#".dechex($color^=1052688)."' data-type='".$val['type']."'>
			<th bgcolor='white'><span title='add row' data-translate='title' class='tool' onclick='addRow(this)'>&#xe908;</span></th>
			<td align='center' data-translate='textContent' data-key='".$key."' ".(empty($key) ? "contenteditable='true'" : "").">".$key."</td>
			<td contenteditable='true'>".$val['value']."</td>
			<td contenteditable='true'>";
			switch($key){
				case "subdomain":
				case "desktop subdomain":
				case "mobile subdomain":
				case "base folder":				
					$rows .= implode(", ",$subdomains);
					break;
				break;
				case "default module":
					$rows .= implode(", ", $modules);
				break;
				case "theme":
				case "mobile theme":
					$rows .= implode(", ", $themes);
				break;
				default:
					$rows .= implode(", ", $val['valid']);
				break;
			}
			$rows .= "
			</td>
			<th bgcolor='white'><span title='delete row' data-translate='title' class='tool red' onclick='deleteRow(this)'>&#xe907;</span></th>
			</tr>";
		}
	}
	
	print($rows);
?>
				</tbody>
			</table>
		</div>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span class="tool" title="Brancher" data-tab="brancher">&#xe902;</span>
					<span class="tool" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<div id="manual" class="tab">
<?php
	
	include_once("modules/manual/embed.php");

?>

			</div>
			<form id="brancher" onchange="changeBranch(this)" onsubmit="return false" class="tab subwhite-bg" autocomplete="off">
				<div class="caption">Brancher</div>
				<div class="content root">
<?php

	print($branch);
	
?>
				</div>
			</form>
		</div>
	</body>
</html>


<?php

function brancherTree(&$branch, &$map){
	foreach($branch as $key=>$item){
		if($key===current($map)){
			$checked = (next($map)) ? "checked" : "disabled";
		}else $checked = "item";
		
		$list.="<label class='".$checked."' data-href='".$key."'><input type='radio' name='branch' value='".$key."' ".$checked."> ".$key."</label>";
		
		if(count($item['branch'])>0){
			$list .= "<div class='root'>".brancherTree($item['branch'], $map)."</div>";
		}
	}
	return $list;
}	

?>