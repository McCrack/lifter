<?php

	$msg = file_get_contents('php://input');

	$handle = "b".time();
?>
<div id="<?php print($handle); ?>" class="box"  onmousedown="boxList.focus(this)" style="width:99%;max-width:320px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">Upload...</div>
	<div class="box-body" style="resize:none">
		<progress id="progress" style="width:100%;box-sizing:border-box;" value="0"></progress>
		<div id="upload-log" style="padding:10px;height:220px;background-color:white;overflow-y:auto">
		
		</div>
	</div>
</div>