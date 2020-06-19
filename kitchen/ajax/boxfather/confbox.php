<?php
	
	$handle = "b".time();
	
?>
<form onsubmit="return this.onApply()" id="<?=$handle?>" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="width:99%;max-width:360px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">

	</div>
	<div id="confirm" class="box-body" style="resize:none" align="center">
		<h3></h3>
	</div>
	<div class="box-footer">
		<button type="submit">Ok</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>