<?php
	
	$brancher->auth("sitemap") or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();
	
?>

<form id="<?php print($handle); ?>" onsubmit="return saveSettingsBox(this)" class="box"  onmousedown="boxList.focus(this)" style="max-width:580px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		Save log		
	</div>
	<div class="box-body" style="resize:none">
		<div id="upload-log">
		
		</div>
	</div>
	<div class="box-footer">
		<button disabled onclick="boxList.drop(this.parent(2).id)">Ok</button>
	</div>
</form>