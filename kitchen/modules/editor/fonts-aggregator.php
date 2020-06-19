<?php
	if(file_exists("../".BASE_FOLDER."/fonts/".SUBPAGE.".ttf")){
		header ("Content-type: application/x-font-ttf");
		$font = file_get_contents("../".BASE_FOLDER."/fonts/".SUBPAGE.".ttf");
		print($font);
	}elseif(file_exists("../".BASE_FOLDER."/fonts/".SUBPAGE.".woff")){
		header ("Content-type: application/x-font-woff");
		$font = file_get_contents("../".BASE_FOLDER."/fonts/".SUBPAGE.".woff");
		print($font);
	}
?>