<?php

$brancher->auth(array("staff")) or die("Access denied!");
	
switch(SUBPAGE){
	case "save":
		$p = JSON::load('php://input');
		if(PARAMETER){
			$mySQL->query("
			UPDATE `gb_staff` LEFT JOIN `gb_community` USING(`CommunityID`) SET 
			`Login`='".$p['login']."', 
			`Passwd`='".$p['passwd']."', 
			`Group`='".$p['group']."', 
			`Departament`='".$p['departament']."',
			`Name`='".$p['name']."',
			`Email`='".$p['email']."'
			WHERE `UserID` = ".PARAMETER."");
			print(PARAMETER);
		}else{
			$id = reset($mySQL->single_row("SELECT MAX(`CitizenID`) FROM `gb_community` WHERE `App` LIKE 'self'"));
			(INT)$id++;
			$id = $mySQL->single_row("INSERT INTO `gb_community` (`Email`, `CitizenID`, `Name`, `Visit`) VALUES ('".$p['email']."', ".$id.", '".$p['name']."', ".time().")");
			$mySQL->query("INSERT INTO `gb_staff` (`Login`, `Passwd`, `Group`, `Departament`, `CommunityID`) VALUES ('".$p['login']."', '".$p['passwd']."', '".$p['group']."', '".$p['departament']."', ".$id.")");
			print($id);
		}			
	break;
	case "delete":
		$mySQL->query("DELETE FROM `gb_staff` WHERE `UserID`=".PARAMETER." LIMIT 1");
	break;
	default:break;
}

?>