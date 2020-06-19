<?php

$brancher->auth(array("blogger")) or die("Access denied!");

switch(SUBPAGE){
	case "reload":
		$limit = 20;
		$page = SUBPARAMETER;
		$rows = $mySQL->query("
		SELECT SQL_CALC_FOUND_ROWS * FROM gb_blogfeed CROSS JOIN gb_pages USING(PageID)
		WHERE category LIKE 'articles' AND language LIKE '".PARAMETER."'
		GROUP BY ID
		ORDER BY PageID DESC LIMIT ".(($page-1)*$limit).", ".$limit);
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
		$time = time();
		$PageID = $mySQL->insert("gb_pages", ["type"=>"article","created"=>$time,"modified"=>$time ]);
		
		$PostID = $mySQL->single_row("SELECT MAX(`ID`) FROM `gb_blogfeed` LIMIT 1");
		$PostID = reset($PostID);
		(INT)$PostID++;
		if(SUBPARAMETER){
			$language = SUBPARAMETER;
		}else{
			$cng = JSON::load("../".BASE_FOLDER."/config.init");
			$language = $cng['general']['language']['value'];
		}
		$mySQL->insert("gb_blogfeed", ["PageID"=>$PageID, "ID"=>$PostID, "language"=>$language, "tid"=>2, "category"=>"articles", "UserID"=>PARAMETER]);
		$mySQL->insert("gb_blogcontent", ["PageID"=>$PageID, "log"=>"create{".USER_ID.",".$time."}\n"]);
		$mySQL->insert("gb_amp", ["PageID"=>$PageID]);

		$year = date("Y");
		$month = strtolower(date("F"));

		mkpath("../img/data/".$year."/".$month."/".$PostID);

		print JSON::encode([
			"id"=>$PostID,
			"year"=>$year,
			"month"=>$month,
			"language"=>$language
		]);
	break;
	case "save-heading":
		$time = time();
		$p = JSON::load('php://input');
		$keywords = preg_split("/,+\s*/", mb_strtolower($p['keywords'], "utf-8"), -1, PREG_SPLIT_NO_EMPTY);
		$p['tid'] = keywords($keywords, $p['tid']);
		
		if(empty($p['PageID'])){
			$p['PageID'] = $mySQL->insert("gb_pages", array("type"=>"post","created"=>$p['created'],"modified"=>time()));
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
				"video"=>$p['video'],
				"category"=>$p['category'],
				"subtemplate"=>$p['subtemplate'],
				"tid"=>$p['tid'],
				"UserID"=>$p['UserID'],
				"published"=>$p['published']
			));
		}else{
			$readiness = ["developer"=>0,"surfer"=>0,"author"=>1,"editor"=>2,"admin"=>4,"video editor"=>8][USER_GROUP];
			$post = $mySQL->single_row("SELECT ID,language,readiness FROM gb_blogfeed WHERE PageID=".$p['PageID']." LIMIT 1");
			if($post['ID']===$p['ID'] && $post['language']===$p['language']){
				$mySQL->update("gb_blogfeed", [
					"language"=>$p['language'],
					"header"=>$mySQL->escape_string($p['header']),
					"subheader"=>$mySQL->escape_string($p['subheader']),
					"preview"=>$p['preview'],
					"video"=>$p['video'],
					"category"=>$p['category'],
					"subtemplate"=>$p['subtemplate'],
					"tid"=>$p['tid'],
					"UserID"=>$p['UserID'],
					"published"=>$p['published'],
					"readiness"=>((INT)$post['readiness'] | (INT)$readiness)
				], "`PageID`=".$p['PageID']);
				$mySQL->update("gb_pages", ["created"=>$p['created'],"modified"=>$time], "PageID=".$p['PageID']);
			}else{
				$p['PageID'] = $mySQL->insert( "gb_pages", ["type"=>"post","created"=>$p['created'],"modified"=>$time] );
				$mySQL->insert("gb_blogfeed", array(
					"PageID"=>$p['PageID'],
					"ID"=>$p['ID'],
					"language"=>$p['language'],
					"header"=>$mySQL->escape_string($p['header']),
					"subheader"=>$mySQL->escape_string($p['subheader']),
					"preview"=>$p['preview'],
					"video"=>$p['video'],
					"category"=>$p['categort'],
					"subtemplate"=>$p['subtemplate'],
					"tid"=>$p['tid'],
					"UserID"=>$p['UserID'],
					"published"=>$p['published']
				));
			}
		}
		//"log"=>"CONCAT('save{".USER_ID.",".$time."}\n',log)"
		$mySQL->update("gb_blogcontent", [
			"ads"=>$p['ads']
		], "PageID=".$p['PageID']);
		
		$answer = ["log"=>[],"url"=>""];
		$info = $mySQL->single_row("SELECT * FROM gb_blogfeed LEFT JOIN gb_pages USING(PageID) WHERE PageID = ".$p['PageID']." LIMIT 1");
		unset($p['keywords'],$p['header'],$p['subheader']);
		$info['ads'] = $p['ads'];

		$host = explode(".",$_SERVER['HTTP_HOST']);
		$host = array_slice($host, 1);
		$answer['url'] = PROTOCOL."://".implode(".", $host)."/".translite($info['header'], false)."-".$info['ID'];
		foreach($p as $key=>$val){
			if($info[$key]==$val){
				$answer['log'][$key] = sprintf("%'.".(82 - strlen($key))."s - <span class='green'>Ok</span>", $val);
			}else $answer['log'][$key] = sprintf("%'.".(78 - strlen($key))."s - <span class='red'>Failed</span>", $val);
		}
		$answer['log']['PageID'] = $p['PageID'];
		$answer['log']['ID'] = $p['ID'];
		print(JSON::encode($answer));
	break;
	case "save-content":
		$time = time();
		$mySQL->update("gb_pages", ["modified"=>$time], "PageID=".PARAMETER);

		$data = gzencode(file_get_contents('php://input'));
		//log=CONCAT('save content{".USER_ID.",".$time."}\n',log)
		$mySQL->query("
			INSERT INTO gb_blogcontent SET
				PageID = ".PARAMETER.",
				content = '".$mySQL->escape_string($data)."'
			ON DUPLICATE KEY UPDATE
				content='".$mySQL->escape_string($data)."'
			");
		$saved = $mySQL->single_row("SELECT content FROM gb_blogcontent WHERE PageID=".PARAMETER." LIMIT 1");
		if(strcmp($data, $saved['content'])){
			print("Failed save");
		}else print(PARAMETER);
	break;

	case "save-ina":
		$data = gzencode(file_get_contents('php://input'));
		$mySQL->query("
			INSERT INTO gb_ina SET
				PageID = ".PARAMETER.",
				content = '".$mySQL->escape_string($data)."',
				cover = '".SUBPARAMETER."'
			ON DUPLICATE KEY UPDATE
				content='".$mySQL->escape_string($data)."',
				cover = '".SUBPARAMETER."'
			");
		$saved = $mySQL->single_row("SELECT content FROM gb_ina WHERE PageID=".PARAMETER." LIMIT 1");
		if(strcmp($data, $saved['content'])){
			print("Failed save");
		}else print(PARAMETER);
	break;
	case "drop-ina":
		$mySQL->single_row("DELETE FROM gb_ina WHERE PageID=".PARAMETER."");
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
		$mySQL->single_row("DELETE FROM gb_amp WHERE PageID=".PARAMETER."");
	break;
	case "remove":
		$mySQL->single_row("DELETE FROM `gb_pages` WHERE `PageID`=".PARAMETER."");
	break;
	default:break;
}
?>