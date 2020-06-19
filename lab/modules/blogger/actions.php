<?php

//$brancher->auth(array("blogger")) or die("Access denied!");

switch(SUBPAGE){
	case "defrost":
		$mySQL->query("UPDATE `gb_blogfeed` SET `published`=(`published`&3) WHERE `PageID`=".PARAMETER." LIMIT 1");
	break;
	case "reload":
		$limit = 20;
		$page = SUBPARAMETER;
		$rows = $mySQL->query("SELECT SQL_CALC_FOUND_ROWS * FROM `gb_blogfeed` LEFT JOIN `gb_pages` USING(`PageID`) WHERE `language` LIKE '".PARAMETER."' GROUP BY `ID` ORDER BY `PageID` DESC LIMIT ".(($page-1)*$limit).", ".$limit);
		$count = reset($mySQL->single_row("SELECT FOUND_ROWS()"));
		foreach($rows as $row){
			$stikers.="
			<a class='sticker' href='/blogger/".PARAMETER."/".$page."/".$row['ID']."/".$row['language']."'>
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
		$PageID = $mySQL->insert("gb_pages", ["created"=>time()] );
		$ID = $mySQL->single_row("SELECT MAX(`ID`) FROM `gb_blogfeed` LIMIT 1");
		$ID = reset($ID);
		(INT)$ID++;
		if(SUBPARAMETER){
			$language = SUBPARAMETER;
		}else{
			$cng = JSON::load("../".BASE_FOLDER."/config.init");
			$language = $cng['general']['language']['value'];
		}
		$mySQL->insert("gb_blogfeed", ["PageID"=>$PageID, "ID"=>$ID, "language"=>$language, "tid"=>2, "UserID"=>PARAMETER]);
		$mySQL->insert("gb_blogcontent", ["PageID"=>$PageID]);
		print("/blogger/".$language."/1/".$ID."/".$language);
	break;
	case "save-heading":
		$p = JSON::load('php://input');
		$keywords = preg_split("/,+\s*/", mb_strtolower($p['keywords'], "utf-8"), -1, PREG_SPLIT_NO_EMPTY);
		$p['tid'] = keywords($keywords, $p['tid']);
		
		if(empty($p['PageID'])){
			$p['PageID'] = $mySQL->insert("gb_pages", ["created"=>$p['created']] );
			$oldID = $p['ID'];
			$p['ID'] = reset($mySQL->single_row("SELECT MAX(`ID`) FROM `gb_blogfeed` LIMIT 1"));
			(INT)$p['ID']++;
	
			$mySQL->insert("gb_blogfeed", array(
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
			));
		}else{
			$mySQL->update("gb_blogfeed", array(
				"language"=>$p['language'],
				"header"=>$mySQL->escape_string($p['header']),
				"subheader"=>$mySQL->escape_string($p['subheader']),
				"preview"=>$p['preview'],
				"alt_prw"=>$p['preview'],
				"tid"=>$p['tid'],
				"UserID"=>$p['UserID'],
				"published"=>$p['published']
			), "`PageID`=".$p['PageID']);
			$mySQL->update("gb_pages", ["created"=>$p['created']], "`PageID`=".$p['PageID']);
			$mySQL->update("gb_blogcontent", ["template"=>$p['template']], "`PageID`=".$p['PageID']);
		}
		
		$answer = array();
		$info = $mySQL->single_row("SELECT * FROM `gb_blogfeed` LEFT JOIN `gb_pages` USING(`PageID`) WHERE `PageID` = ".$p['PageID']." LIMIT 1");
		unset($p['keywords']);
		foreach($p as $key=>$val){
			if($info[$key]==$val){
				$answer[$key] = sprintf("%'.".(62 - strlen($key))."s - <span class='green'>Ok</span>", $val);
			}else $answer[$key] = sprintf("%'.".(58 - strlen($key))."s - <span class='red'>Failed</span>", $val);
		}
		$answer['PageID'] = $p['PageID'];
		$answer['ID'] = $p['ID'];
		print(JSON::stringify($answer));
	break;
	case "save-content":
		$data = gzencode(file_get_contents('php://input'));
		$mySQL->query("
			INSERT INTO `gb_blogcontent` SET
				`PageID` = ".PARAMETER.",
				`content` = '".$mySQL->escape_string($data)."'
			ON DUPLICATE KEY UPDATE
				`content`='".$mySQL->escape_string($data)."'
			");
		$saved = $mySQL->single_row("SELECT `content` FROM `gb_blogcontent` WHERE `PageID`=".PARAMETER." LIMIT 1");
		if(strcmp($data, $saved['content'])){
			print("Failed save");
		}else print(PARAMETER);
	break;
	case "save-amp":
		$data = gzencode(file_get_contents('php://input'));
		$mySQL->query("
			INSERT INTO gb_amp SET
				PageID = ".PARAMETER.",
				content = '".$mySQL->escape_string($data)."'
			ON DUPLICATE KEY UPDATE
				content='".$mySQL->escape_string($data)."'
			");
		$saved = $mySQL->single_row("SELECT content FROM gb_amp WHERE PageID=".PARAMETER." LIMIT 1");
		if(strcmp($data, $saved['content'])){
			print("Failed save");
		}else print(PARAMETER);
	break;
	case "drop-amp":
		$mySQL->query("DELETE FROM gb_amp WHERE PageID = ".PARAMETER." LIMIT 1");
	break;
	case "render":
		
		$p = JSON::load('php://input');
		$pg = $mySQL->single_row("SELECT * FROM `gb_pages` LEFT JOIN `gb_blogfeed` USING(`PageID`) LEFT JOIN `gb_blogcontent` USING(`PageID`) WHERE `PageID`=".PARAMETER." LIMIT 1");
		$post_preview = $p['preview'];
		foreach($p['folders'] as $subdomain=>$template){
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
			$key = PROTOCOL."://".$subdomain.".".$config->domain."/".$pg['ID']."/".Wordlist::translite($pg['header'], "-", true);
			printf("<p><tt><b>%s</b> %'.".(90 - strlen($key))."s</tt></p>", $key, ": <span class='".$color."'>".$result."</span>");
		}
	break;
/*
	case "rerender":
		$config = new config("../m/config.init");
		$rows = $mySQL->query("SELECT * FROM `gb_pages` CROSS JOIN `gb_blogfeed` USING(`PageID`) CROSS JOIN `gb_blogcontent` USING(`PageID`) ORDER BY `PageID` DESC LIMIT 1200, 50");
		
		$wl = new Wordlist("", "m", "ru");	// Load all vocabularies of subdomain
		
		$keywords = $mySQL->query("SELECT `id`,`tag` FROM `gb_keywords` ORDER BY `rating` DESC LIMIT 32");
		
		foreach($rows as $row){
			$tpl = new HTMLDocument("../m/themes/blog/post.html");
			$created = date("c", $row['created']);
			$preview = getimagesize($row['preview']);
			$canonical = "http://lifter.com.ua/ru/".$row['ID'];
			$author = $mySQL->single_row("SELECT * FROM `gb_staff` LEFT JOIN `gb_community` USING(`CommunityID`) WHERE `UserID`=".$row['UserID']." LIMIT 1");
		
			$tagination = $mySQL->single_row("SELECT * FROM `gb_tagination` WHERE `tid` = ".$row['tid']." LIMIT 1");
			$cnt = count($tagination)-1;
			$IDs = [];
			for($j=0; $j<$cnt; $j++){
				for($i=32; $i--;){
					if($tagination[$j] & pow(2, $i)){ $IDs[] = (32*$j) + ($i+1); }
				}
			}
			
		//=======================
			
			$tagscloud = $static = $words = [];
			foreach($keywords as $key){
				if(in_array($key['id'], $IDs)){
					$words[] = $key;
				}
				$tagscloud[] = "<a class='tag' href='/theme/".$key['tag']."'>".$wl->{$key['tag']}."</a>";
			}
			$section_name = $wl->{$words[0]['tag']};
			$section = pow(2, ($words[0]['id'] % 32)-1);
			
			foreach($words as &$key){
				$theme = $wl->{$key['tag']};
				$static[] = "<a class='tag' href='/theme/".$key['tag']."'>".$theme."</a>";
				$hashtags .= "<meta property='article:tag' content='".$theme."'>";
				$key = $theme;
			}
			$tpl->{"static"}->appendHTML(implode(" ", $static));	
			$tpl->{"tags-cloud"}->appendHTML(implode(" ", $tagscloud));
			
		//=======================
			ob_start(); 
			include("../m/modules/render/render.php");
			$tpl->xpath("//head[1]/*[1]")->item(0)->insertAfter( $tpl->createFragment( ob_get_contents() ) );
			ob_end_clean();
			
			$tpl->{"fb-like"}->setAttribute("data-href", "https://facebook.com/248223498667848");
			$content = $tpl->content;
			$content->appendHTML( gzdecode($row['content']) );
			
			$imgs = $content->xpath("id('content')//img");
			for($i=$imgs->length; $i--;){
				$path = parse_url($imgs->item($i)->src, PHP_URL_PATH);
				$imgs->item($i)->src = "http://m.lifter.com.ua".$path;
				
			}
			
			$ads = $tpl->xpath("//div[@class='adsense']/script[1]");
			for($i=$ads->length; $i--;){
				$ads->item($i)->parentNode->removeAttribute("style");
				$ads->item($i)->nodeValue = 'google_ad_client="ca-pub-9935373660576595";google_ad_slot="1880145263";google_ad_width=300;google_ad_height=250;';
			}
			
			$tpl->xpath("id('content')/header[1]/h1[1]")->item(0)->nodeValue = $row['header'];
			$tpl->xpath("id('content')/header[1]/h2[1]")->item(0)->nodeValue = $row['subheader'];
			$pub = $tpl->xpath("id('publication-by')")->item(0);
			if($pub){
				$pub->appendHTML("
				<address class='left'>".$author['Name']."</address> 
				<time datetime='".$created."'>".date("d F, Y", $row['created'])."</time> 
				");
			}
			$tpl->xpath("id('content')/header[1]//img[1]")->item(0)->src = $row['alt_prw'];
			//$tpl->xpath("id('content')/header[1]//img[1]")->item(0)->src = $row['preview'];
			
			$wl->translateDocument($tpl);	// Translate elements
			$mySQL->single_row("UPDATE `gb_m` SET `content`='".$mySQL->escape_string(gzencode($tpl))."' WHERE `PageID`=".$row['PageID']." LIMIT 1");
			
			print $row['PageID']."-".$row['header']."<br>";
		}
	break;
*/
	case "remove":
		$mySQL->single_row("DELETE FROM `gb_pages` WHERE `PageID`=".PARAMETER."");
	break;
	default:break;
}
?>