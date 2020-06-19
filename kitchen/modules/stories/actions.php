<?php

$brancher->auth(array("stories")) or die("Access denied!");

switch(SUBPAGE){
	case "reload":
		$limit = 20;
		$page = SUBPARAMETER;
		$rows = $mySQL->query("
		SELECT SQL_CALC_FOUND_ROWS * FROM gb_blogfeed 
		CROSS JOIN gb_pages USING(PageID) 
		WHERE category LIKE 'stories' AND language LIKE '".PARAMETER."'
		GROUP BY ID 
		ORDER BY PageID DESC LIMIT ".(($page-1)*$limit).", ".$limit);
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
		$PageID = $mySQL->insert("gb_pages", ["type"=>"story","created"=>time(),"modified"=>time() ]);
		$PostID = $mySQL->single_row("SELECT MAX(ID) FROM gb_blogfeed LIMIT 1");
		$PostID = reset($PostID);
		(INT)$PostID++;
		if(SUBPARAMETER){
			$language = SUBPARAMETER;
		}else{
			$cng = JSON::load("../".BASE_FOLDER."/config.init");
			$language = $cng['general']['language']['value'];
		}
		$mySQL->insert("gb_blogfeed", ["PageID"=>$PageID, "ID"=>$PostID, "language"=>$language, "tid"=>2, "category"=>"stories", "UserID"=>PARAMETER]);
		$mySQL->insert("gb_blogcontent", ["PageID"=>$PageID]);
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
			$p['PageID'] = $mySQL->insert("gb_pages", array("created"=>$p['created']));
			$oldID = $p['ID'];
			$p['ID'] = reset($mySQL->single_row("SELECT MAX(`ID`) FROM gb_blogfeed LIMIT 1"));
			(INT)$p['ID']++;
	
			$mySQL->insert("gb_blogfeed", array(
				"PageID"=>$p['PageID'],
				"ID"=>$p['ID'],
				"language"=>$p['language'],
				"header"=>$mySQL->escape_string($p['header']),
				"subheader"=>$mySQL->escape_string($p['subheader']),
				"preview"=>$p['preview'],
				"portrait"=>$p['portrait'],
				"tid"=>$p['tid'],
				"UserID"=>$p['UserID'],
				"published"=>$p['published'],
				"subtemplate"=>$p['subtemplate']
			));
		}else{
			$mySQL->update("gb_blogfeed", array(
				"language"=>$p['language'],
				"header"=>$mySQL->escape_string($p['header']),
				"subheader"=>$mySQL->escape_string($p['subheader']),
				"preview"=>$p['preview'],
				"portrait"=>$p['portrait'],
				"tid"=>$p['tid'],
				"UserID"=>$p['UserID'],
				"published"=>$p['published'],
				"subtemplate"=>$p['subtemplate']
			), "PageID=".$p['PageID']);
			$mySQL->update("gb_pages",
				["created"=>$p['created']],
				"PageID=".$p['PageID']
			);
		}

		$answer = ["log"=>[],"url"=>""];
		$info = $mySQL->single_row("SELECT PageID,ID,header,subheader,UserID,created,tid,published FROM gb_blogfeed LEFT JOIN gb_pages USING(PageID) WHERE PageID = ".$p['PageID']." LIMIT 1");
		unset($p['keywords']);

		$host = explode(".",$_SERVER['HTTP_HOST']);
		$host = array_slice($host, 1);
		$answer['url'] = PROTOCOL."://".implode(".", $host)."/".translite($info['header'], false)."-".$info['ID'];

		foreach($info as $key=>$val){
			if($p[$key]==$val){
				$answer['log'][$key] = sprintf("%'.".(82 - strlen($key))."s - <span class='green'>Ok</span>", $val);
			}else $answer['log'][$key] = sprintf("%'.".(78 - strlen($key))."s - <span class='red'>Failed</span>", $val);
		}

		$answer['log']['PageID'] = $p['PageID'];
		$answer['log']['ID'] = $p['ID'];
		print(JSON::encode($answer));
	break;
	case "save-amp":
		$p = JSON::load('php://input');
		
		$poster = $mySQL->single_row("SELECT portrait FROM gb_blogfeed WHERE PageID = ".PARAMETER." LIMIT 1")['portrait'];

		ob_start();
		foreach($p as $i=>$card):?>
		<amp-story-page id="page-<?=($i+1)?>" auto-advance-after="15s">
			<?if($card['type']=="video"):?>
			<amp-story-grid-layer template="fill">
				<amp-video src="<?=$card['src']?>" autoplay loop width="<?=$card['width']?>" height="<?=$card['height']?>" poster="<?=$poster?>" preload="auto" layout="responsive"></amp-video>
			</amp-story-grid-layer>
			<?elseif($card['type']=="image"):?>
			<amp-story-grid-layer template="fill">
				<amp-img src="<?=$card['src']?>" width="<?=$card['width']?>" height="<?=$card['height']?>" layout="responsive"></amp-img>
			</amp-story-grid-layer>
			<?endif?>
			<amp-story-grid-layer template="fill">
				<section>
					<div class="<?=($card['justify'].' '.$field['background'])?>">
					<?foreach($card['fields'] as $field):?>
						<p animate-in="<?=$field['animate']?>" class="<?=($field['align'].' '.$field['background'])?>">
						<?foreach($field['words'] as $word):?>
							<span class="font-<?=($word['font'].' '.$word['color'].' '.$word['background'])?>"><?=$word['content']?></span>
						<?endforeach?>
						</p>
					<?endforeach?>
					</div>
				</section>
			</amp-story-grid-layer>
			<amp-story-cta-layer>
    			<a class="copyright"><?=$card['copyright']?></a>
  			</amp-story-cta-layer>
		</amp-story-page>
		<?endforeach?>
  		<?php
		$content = gzencode( ob_get_contents() );
		ob_end_clean();

		$mySQL->query("
			INSERT INTO gb_amp SET
				PageID = ".PARAMETER.",
				content = '".$mySQL->escape_string($content)."'
			ON DUPLICATE KEY UPDATE
				content='".$mySQL->escape_string($content)."'
			");
		$saved = $mySQL->single_row("SELECT content FROM gb_amp WHERE PageID=".PARAMETER." LIMIT 1")['content'];
		if(strcmp($content, $saved)){
			print("Failed save");
		}else print(PARAMETER);
	break;
	default:break;
}
?>