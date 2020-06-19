<?php

$brancher->auth() or die("Access denied!");

switch(SUBPAGE){
	case "mailing":
		$mailing = sendmail($_POST['to'], $_POST['message'], "html", $_POST['theme'], $_POST['from']);
		print($mailing);
	break;
	case "save":
		$p = JSON::load('php://input');
		if($p){
			$upd = $mySQL->query("UPDATE `gb_community` SET `options`='".JSON::stringify($p)."' WHERE `CommunityID` = ".PARAMETER." LIMIT 1");
		}else $upd = "Unknow format.";
		print($upd);
	break;
	case "add-to-staff":
		//$id = reset($mySQL->single_row("SELECT MAX(`CitizenID`) FROM `gb_community` WHERE `App` LIKE 'self'"));
		//(INT)$id++;
		//$mySQL->single_row("INSERT INTO `gb_community` SET `CitizenID`=".$id);
		$id = $mySQL->single_row("INSERT INTO `gb_staff` SET `CommunityID`=".PARAMETER);
		print($id);
	break;
	default: break;
}

?>