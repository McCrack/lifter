<?php

	$handle = "b".time();

?>
<div id="<?php print($handle); ?>" class="box"  onmousedown="boxList.focus(this)" style="width:99%;max-width:360px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">

	</div>
	<div id="alert" class="box-body" style="resize:none" align="center">
		<h3></h3>
	</div>
	<div class="box-footer">
		<button onclick="boxList.drop(this.parent(2).id)">Ok</button>
	</div>
</div>