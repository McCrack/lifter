<?php

$brancher->auth(array("sitemap")) or die("Access denied!");

switch(SUBPAGE){
	case "create-element_id":
		$name = file_get_contents('php://input');
		$id = $mySQL->insert("gb_pages", ["type"=>"component"]);
		$mySQL->insert("gb_components", ["PageID"=>$id,"name"=>$name]);

		$rows = $mySQL->query("SELECT PageID,name,event,views FROM gb_components CROSS JOIN gb_pages USING(PageID)");
		foreach($rows as $row):?>
		<tr align="center">
			<td><input type="checkbox"></td>
			<td><?=$row['PageID']?></td>
			<td align="left"><?=$row['name']?></td>
			<td><?=$row['event']?></td>
			<td><?=$row['views']?></td>
		</tr>
		<?endforeach;
	break;
	case "remove-elements":
		$IDs = JSON::load('php://input');
		$mySQL->query("DELETE FROM gb_pages WHERE PageID IN (".implode(",", $IDs).")");
		$rows = $mySQL->query("SELECT PageID,name,event,views FROM gb_components CROSS JOIN gb_pages USING(PageID)");
		foreach($rows as $row):?>
		<tr align="center">
			<td><input type="checkbox"></td>
			<td><?=$row['PageID']?></td>
			<td align="left"><?=$row['name']?></td>
			<td><?=$row['event']?></td>
			<td><?=$row['views']?></td>
		</tr>
		<?endforeach;
	break;
	case "reset-counters":
		$mySQL->query("UPDATE gb_components CROSS JOIN gb_pages USING(PageID) SET views=0");
		$rows = $mySQL->query("SELECT PageID,name,event,views FROM gb_components CROSS JOIN gb_pages USING(PageID)");
		foreach($rows as $row):?>
		<tr align="center">
			<td><input type="checkbox"></td>
			<td><?=$row['PageID']?></td>
			<td align="left"><?=$row['name']?></td>
			<td><?=$row['event']?></td>
			<td><?=$row['views']?></td>
		</tr>
		<?endforeach;
	break;
	default:break;
}


?>