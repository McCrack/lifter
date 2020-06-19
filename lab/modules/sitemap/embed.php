<?php
	
	$brancher->auth() or die(include_once("modules/auth/alert.html"));
	
	ob_start();
	
?>

<?php	

	$tpl = ob_get_contents();
	ob_end_clean();
	
	$tpl = new HTMLDocument($tpl);
	
	$wordlist = new Wordlist(array("base"));
	$wordlist->translateDocument($tpl);
	
	print($tpl);

?>