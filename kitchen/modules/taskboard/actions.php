<?php

$brancher->auth() or die("Access denied!");

switch(SUBPAGE){
	case "parse":
		$url = file_get_contents('php://input');
		$data = [
			"og:title"=>"n/a",
			"og:image"=>"/images/NIA.jpg",
			"og:site_name"=>"n/a"
		];
		$html = file_get_contents($url);
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$meta = $dom->getElementsByTagName("meta");
		foreach($meta as $tag){
			$property = $tag->getAttribute("property");
			if($property=="og:title" || $property=="og:image" || $property=="og:site_name"){
				$data[$property]  = $tag->getAttribute("content");
			}
		}
		print JSON::encode($data);
	break;
	case "save":
		$p = JSON::load('php://input');
		$id = $mySQL->query("
		UPDATE gb_stream SET
			CommunityID=".$p['CommunityID'].",
			task='".$mySQL->escape_string($p['task'])."',
			type='".$p['type']."',
			status='".$p['status']."',
			value=".$p['value'].",
			image='".$p['image']."',
			header='".$mySQL->escape_string($p['header'])."',
			source='".$p['source']."',
			link='".$p['link']."'
		WHERE 
			CardID = ".PARAMETER."
		LIMIT 1"
		);
	break;
	case "clear-all-waste":
		print $mySQL->query("DELETE FROM gb_stream WHERE status LIKE 'waste' OR status LIKE 'done'");
	break;
	case "create":
	$p = JSON::load('php://input');
	
	$id = $mySQL->single_row("INSERT INTO gb_stream SET
		UserID=".USER_ID.",
		CommunityID=".$p['CommunityID'].",
		value=".$p['value'].",
		created=".time().",
		task='".$mySQL->escape_string($p['task'])."',
		type='".$p['type']."',
		image='".$p['image']."',
		header='".$mySQL->escape_string($p['header'])."',
		source='".$p['source']."',
		link='".$p['link']."'"
	);
	if($id):?>
	<div id="task-<?=$id?>" data-id="<?=$id?>" data-sorted="0" class="slot" draggable="true">
		<div class="card"> 
			<img src="<?=$p['image']?>" alt="No Image">
			<div class="source"><?=$p['source']?></div>
			<div class="header"><a href="<?=$p['link']?>"><?=$p['header']?></a></div>
			<div class="task"><?=$p['task']?></div>
			<div class="date"><?=date("d M, H:i", time())?></div>
		</div>
	</div>
	<?else:?>
	<div data-sorted="0" class="slot">
		<div class="card"> 
			<div class="task"><b>ERROR</b></div>
			<div class="date"><?=date("d M, H:i", time())?></div>
		</div>
	</div>
	<?endif;
	break;
	case "change-performer":
		$p = JSON::load('php://input');
		$mySQL->query("UPDATE gb_stream SET CommunityID=".$p['CommunityID'].", SortedID=".$p['SortedID']." WHERE CardID=".$p['CardID']." LIMIT 1");
	break;
	case "change-status":
		$p = JSON::load('php://input');
		$mySQL->query("UPDATE gb_stream SET CommunityID=".$p['CommunityID'].", status='".$p['status']."', SortedID=".$p['SortedID']." WHERE CardID=".$p['CardID']." LIMIT 1");
	break;
	case "change-type":
		$p = JSON::load('php://input');
		$mySQL->query("UPDATE gb_stream SET type='".$p['type']."', SortedID=".$p['SortedID']." WHERE CardID=".$p['CardID']." LIMIT 1");
	break;
	case "drop-performer":
		$mySQL->query("UPDATE gb_stream SET CommunityID=NULL WHERE CardID=".PARAMETER." LIMIT 1");
	break;
	case "remove";
		$res = $mySQL->query("DELETE FROM gb_stream WHERE CardID = ".PARAMETER." LIMIT 1");
		print $res;
	break;
	case "reload-stream";
		$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE CommunityID IS NULL AND type LIKE 'article' ORDER BY value DESC LIMIT 10");
		foreach($tasks as $task):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot" draggable="true">
			<div class="card"> 
				<img src="<?=$task['image']?>" alt="No Image">
				<div class="source"><?=$task['source']?></div>
				<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
				<div class="task <?=$task['type']?>"><?=$task['task']?></div>
				<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			</div>
		</div>
		<?endforeach;
	break;
	default:break;
}
?>