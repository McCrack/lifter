<?php

$brancher->auth("keywords") or die("Access denied!");

switch(SUBPAGE){
	case "add-section":
		
		$field = count(reset($mySQL->group_rows("SHOW COLUMNS FROM `gb_tagination`")))-1;
		
		$mySQL->query("ALTER TABLE `gb_tagination` ADD `".$field."` INT UNSIGNED DEFAULT 0");
		$mySQL->query("UPDATE `gb_tagination` SET `".$field."`=4294967295  LIMIT 1");
		
		$keywords = reset($mySQL->single_row("SELECT COUNT(*) FROM `gb_keywords`"));
		$fields = reset($mySQL->group_rows("SHOW COLUMNS FROM `gb_tagination`"));
		
		$cells = (count($fields)-1) * 32;
		
		print("Used: ".$keywords."/".$cells);
	break;
	default: break;
}

?>