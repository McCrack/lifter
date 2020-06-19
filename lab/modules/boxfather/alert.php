<?php

	$msg = file_get_contents('php://input');

	$handle = "b".time();
	ob_start();
?>
<div id="<?php print($handle); ?>" class="box"  onmousedown="boxList.focus(this)" style="width:99%;max-width:360px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">

	</div>
	<div id="alert" class="box-body" style="resize:none">
		<h3 align="center" data-translate="nodeValue"><?php print($msg); ?></h3>
	</div>
	<div class="box-footer">
		<button onclick="boxList[boxList.onFocus].drop()">Ok</button>
	</div>
</div>

<?php

	$tpl = ob_get_contents();
	ob_end_clean();
	
	$tpl = DOMDocument::loadHTML($tpl);
	
	$wordlist = new Wordlist("alerts");
	$wordlist->translateDocument($tpl);
	
	print($tpl->saveHTML());
?>