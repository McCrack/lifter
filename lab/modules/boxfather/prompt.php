<?php

	$msg = file_get_contents('php://input');

	$handle = "b".time();
	ob_start();
?>
<form id="<?php print($handle); ?>" class="box"  onmousedown="boxList.focus(this)" style="width:99%;max-width:360px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">

	</div>
	<div id="prompt" class="box-body" align="center">
		<h3 data-translate="nodeValue"><?php print($msg); ?></h3>
		<input name="field" placeholder="..." required>
	</div>
	<div class="box-footer">
		<button type="submit">Ok</button>
		<button type="reset" onclick="boxList[boxList.onFocus].drop()" data-translate="nodeValue">cancel</button>
	</div>
</form>

<?php

	$tpl = ob_get_contents();
	ob_end_clean();
	
	$tpl = DOMDocument::loadHTML($tpl);
	
	$wordlist = new Wordlist(array("alerts","base"));
	$wordlist->translateDocument($tpl);
	
	print($tpl->saveHTML());
?>