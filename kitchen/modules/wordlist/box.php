<?php
	
	$brancher->auth("wordlist") or die(include_once("modules/auth/alert.html"));
	
	$p = JSON::load('php://input');
	$key = $p['key'];
	foreach(scandir("../") as $subdomain){
		if($subdomain!="." && $subdomain!=".."){
		$items .= "<label class='tree-root-item'>".$subdomain."</label>";
			if(is_dir("../".$subdomain."/localization")){
				$items .= "<div class='root'>";
				foreach(scandir("../".$subdomain."/localization") as $file){
					if(is_file("../".$subdomain."/localization/".$file)){
						$file = reset(explode(".",$file));
						$items .= "<a onclick='return openShortWordlist(this)' href='/".$subdomain."/".$file."/".$key."' class='tree-item'>".$file."</a>";
					}
				}
				$items .= "</div>";
			}
		}
	}
	
	$p = JSON::load('php://input');
	
	$handle = "b".time();
	
?>

<form id="<?=$handle?>" onsubmit="return saveShortWordlist(this)" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="max-width:680px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script src="/modules/wordlist/tpl/wordlist.js"></script>
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList['<?=$handle?>'].drop()"></span>
		<span data-translate="textContent">wordlist</span>
	</div>
	<div class="box-body">
		<div class="leftbar">
			<div class="root"><?php print($items); ?></div>
		</div>
		<div class="environment">
			<table width="100%" rules="cols" cellpadding="4" cellspacing="0" bordercolor="#999">
				<thead>
					<tr bgcolor="#555" style="color:#EEE">
						<th width="60">Key</th>
						<td align="center"><?php print($key); ?></td>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">save</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>