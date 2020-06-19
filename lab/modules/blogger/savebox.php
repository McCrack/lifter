<?php
	
	$brancher->auth("blogger") or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();
	
?>

<form id="<?php print($handle); ?>" onsubmit="return saveSettingsBox(this)" onreset="return false" class="box"  onmousedown="boxList.focus(this)" style="max-width:580px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<!--<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.paren(2).id)"></span>-->
		Save log		
	</div>
	<div class="box-body" style="resize:none">
		<div class="log">
		
		</div>
	</div>
	<div class="box-footer">
		<button disabled type="reset">Refresh page</button>
		<button disabled data-translate="textContent">caching</button>
	</div>
</form>