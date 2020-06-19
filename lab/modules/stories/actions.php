<?php

$brancher->auth(array("stories")) or die("Access denied!");

switch(SUBPAGE){
	case "reload":
		$limit = 20;
		$page = SUBPARAMETER;
		$rows = $mySQL->query("SELECT SQL_CALC_FOUND_ROWS * FROM `gb_blogfeed` LEFT JOIN `gb_pages` USING(`PageID`) WHERE `language` LIKE '".PARAMETER."' GROUP BY `ID` ORDER BY `PageID` DESC LIMIT ".(($page-1)*$limit).", ".$limit);
		$count = reset($mySQL->single_row("SELECT FOUND_ROWS()"));
		foreach($rows as $row){
			$stikers.="
			<a class='sticker' href='/stories/".PARAMETER."/".$page."/".$row['ID']."/".$row['language']."'>
				<img src='".$row['preview']."'>
				<div class='header'>".$row['header']."</div>
				<div class='options'>
					<span>".date("d M Y", $row['created'])."</span>
					<span>".$row['published']."</span>
				</div>
			</a>";
		}
		$total = ceil($count/$limit);	// Total pages
		if($total>1){
			if($page>4){
				$j=$page-2;
				$pagination="<a>1</a> ... ";
			}else $j=1;
			for(; $j<$page; $j++) $pagination.="<a>".$j."</a>";					
			$pagination.="<a class='selected'>".$j."</a>";
			if($j<$total){
				$pagination.="<a>".(++$j)."</a>";
				if(($total-$j)>1){
					$pagination.=" ... <a>".$total."</a>";
				}elseif($j<$total){
					$pagination.="<a>".$total."</a>";
				}
			}
		}
	print($stikers."<div onclick='reloadFeed(`".PARAMETER."`, event.target.textContent)' class='caption pagination' align='center'>".$pagination."</div>");
	break;
	case "create-post":
		$PageID = $mySQL->insert("gb_pages", ["created"=>time()]);
		$ID = $mySQL->single_row("SELECT MAX(ID) AS ID FROM gb_blogfeed LIMIT 1")['ID'];
		(INT)$ID++;
		if(SUBPARAMETER){
			$language = SUBPARAMETER;
		}else{
			$cng = JSON::load("../".BASE_FOLDER."/config.init");
			$language = $cng['general']['language']['value'];
		}
		$mySQL->insert("gb_blogfeed", ["PageID"=>$PageID, "ID"=>$ID, "language"=>$language, "tid"=>2, "UserID"=>PARAMETER]);
		$mySQL->insert("gb_blogcontent", ["PageID"=>$PageID]);
		print("/stories/".$language."/1/".$ID."/".$language);
	break;
	case "render":
		$p = JSON::load('php://input');
		$pg = $mySQL->single_row("SELECT * FROM `gb_pages` LEFT JOIN `gb_blogfeed` USING(`PageID`) LEFT JOIN `gb_blogcontent` USING(`PageID`) WHERE `PageID`=".PARAMETER." LIMIT 1");
		foreach($p as $subdomain=>$template){
			$page = $pg;
			$config = new config("../".$subdomain."/config.init");
			if(file_exists("../".$subdomain."/modules/render/".$template.".php")){
				include_once("../".$subdomain."/modules/render/".$template.".php");
			}else include_once("../".$subdomain."/modules/render/index.php");
			
			$test = $mySQL->single_row("SELECT `content` FROM `gb_".$subdomain."` WHERE `PageID`=".PARAMETER." LIMIT 1");
			if(strcmp($page, gzdecode($test['content']))){
				$result = "Failed";
				$color="red";
			}else{
				$result = "Cached";
				$color="green";
			}
			$key = PROTOCOL."://".$subdomain.".".$config->domain."/".$pg['ID']."/".translite($pg['header'], "-", true);
			printf("<p><tt><b>%s</b> %'.".(90 - strlen($key))."s</tt></p>", $key, ": <span class='".$color."'>".$result."</span>");
		}
	break;
	case "remove":
		$mySQL->single_row("DELETE FROM `gb_pages` WHERE `PageID`=".PARAMETER."");
	break;
	case "save-template":
		$template = file_get_contents('php://input');
		print file_put_contents("modules/stories/template.html", $template);
	break;
	case "save":
		$p = JSON::load('php://input');
		$keywords = preg_split("/,+\s*/", mb_strtolower($p['keywords'], "utf-8"), -1, PREG_SPLIT_NO_EMPTY);
		$p['tid'] = keywords($keywords, $p['tid']);
		if(empty($p['PageID'])){
			$p['PageID'] = $mySQL->insert("gb_pages", ["created"=>$p['created']]);
			$oldID = $p['ID'];
			$p['ID'] = reset($mySQL->single_row("SELECT MAX(`ID`) FROM gb_blogfeed LIMIT 1"));
			(INT)$p['ID']++;
	
			$mySQL->insert("gb_blogfeed", [
				"PageID"=>$p['PageID'],
				"ID"=>$p['ID'],
				"language"=>$p['language'],
				"header"=>$mySQL->escape_string($p['header']),
				"subheader"=>$mySQL->escape_string($p['subheader']),
				"preview"=>$p['preview'],
				"alt_prw"=>$p['preview'],
				"tid"=>$p['tid'],
				"UserID"=>$p['UserID'],
				"published"=>$p['published']
			]);
		}else{
			$mySQL->update("gb_blogfeed", [
				"language"=>$p['language'],
				"header"=>$mySQL->escape_string($p['header']),
				"subheader"=>$mySQL->escape_string($p['subheader']),
				"preview"=>$p['preview'],
				"alt_prw"=>$p['preview'],
				"tid"=>$p['tid'],
				"UserID"=>$p['UserID'],
				"published"=>$p['published']
			], "`PageID`=".$p['PageID']);
			$mySQL->update("gb_pages",
				["created"=>$p['created']],
				"PageID=".$p['PageID']
			);
		}
		
		//$mySQL->update("gb_blogcontent", ["content"=>JSON::encode($p['story'])], "PageID=".$p['PageID']);
		
		$data = gzencode(JSON::encode($p['story']));
		
		$answer = [];
		$info = $mySQL->single_row("SELECT PageID,ID,header,subheader,UserID,created,tid,published FROM gb_blogfeed LEFT JOIN gb_pages USING(PageID) WHERE PageID = ".$p['PageID']." LIMIT 1");
		unset($p['keywords']);

		foreach($info as $key=>$val){
			if($p[$key]==$val){
				$answer[$key] = sprintf("%'.".(62 - strlen($key))."s - <span class='green'>Ok</span>", $val);
			}else $answer[$key] = sprintf("%'.".(58 - strlen($key))."s - <span class='red'>Failed</span>", $val);
		}

		$answer['PageID'] = $p['PageID'];
		$answer['ID'] = $p['ID'];
		$answer['link'] = "http://lifter.com.ua/".$info['ID']."/".translite($info['header']);

		print(JSON::encode($answer));
		
		$cng = new config("../www/config.init");

		ob_start();
		include_once("modules/stories/template.html");
		$tpl = ob_get_contents();
		ob_end_clean();
		
		$mySQL->query("
		INSERT INTO gb_blogcontent SET
			PageID = ".$p['PageID'].",
			template='story',
			content = '".$mySQL->escape_string(gzencode($tpl))."'
		ON DUPLICATE KEY UPDATE
			template='story',
			content='".$mySQL->escape_string(gzencode($tpl))."'");

		//$mySQL->single_row("INSERT INTO gb_www SET PageID=".$p['PageID'].", content='".$mySQL->escape_string(gzencode($tpl))."' ON DUPLICATE KEY UPDATE content='".$mySQL->escape_string(gzencode($tpl))."'");
		//$mySQL->single_row("INSERT INTO gb_m SET PageID=".$p['PageID'].",content='".$mySQL->escape_string(gzencode($tpl))."' ON DUPLICATE KEY UPDATE content='".$mySQL->escape_string(gzencode($tpl))."'");
		default:break;
}
?>