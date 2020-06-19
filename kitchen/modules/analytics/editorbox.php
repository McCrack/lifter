<?php

	$brancher->auth("analytics") or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();

?>
<form id="<?php print($handle); ?>" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="width:99%;max-width:450px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		<small data-translate="textContent"><?php print SUBPAGE; ?></small>
	</div>
	<div id="prompt" class="box-body" style="resize:none">
		<p align="center">
			<textarea pattern=".*" name="field" placeholder="..." style="color:#555;font:16px main;min-height:140px;border:1px solid #AAA;border-radius:3px;padding:10px;width:90%;resize:vertical;"></textarea>
		</p>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">save</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>