<?php
	header ("Content-type: application/font");
	$font = file_get_contents("../".$config->{"base folder"}."/fonts/".$_GET['file']);
	print($font);
?>