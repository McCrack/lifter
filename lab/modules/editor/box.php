<?php
	
	$brancher->auth() or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();
	ob_start();
	
?>

<form id="<?php print($handle); ?>" onsubmit="return saveSettingsBox(this)" class="box"  onmousedown="boxList.focus(this)" style="max-width:780px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop('<?php print($handle); ?>')"></span>
		
	</div>
	<div class="box-body">
		
	</div>
	<div class="box-footer">
		
	</div>
</form>

<?php	

	$tpl = ob_get_contents();
	ob_end_clean();
	
	$tpl = DOMDocument::loadHTML($tpl);
	
	$wordlist = new Wordlist(array("base"));
	$wordlist->translateDocument($tpl);
	
	print($tpl->saveHTML());

?>