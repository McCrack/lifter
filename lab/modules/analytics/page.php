<?php
	
	//phpinfo();
	//exit();
	//session.gc_probability и session.gc_divisor
	$brancher->auth() or die(include_once("modules/auth/page.php"));
	
	ob_start();
	
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.analytics</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/analytics/tpl/analytics.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
		
		<script type="text/javascript" src="/js/md5.js"></script>
		<script type="text/javascript" src="/js/gbAPI.js"></script>
		<script type="text/javascript" src="/tpls/main.js"></script>
		<script type="text/javascript" src="/modules/analytics/tpl/analytics.js"></script>
		<script>
			window.onbeforeunload = reauth;
			var SECTION = "analytics";
			window.onload=function(){
				getStandby(function(){
					doc.body.className = standby.bodymode || "leftmode";
				});
			}
		</script>
	</head>
	<body class="analytics">
	
<?php
	
	define("FH", 800);
	define("FW", 1400);
	define("HSPACE", 50);
	define("VSPACE", 50);
	
	if(PAGE){
		$from = explode(".", PAGE);
		$from = mktime(0,0,0, (INT)$from[1], (INT)$from[0], (INT)$from[2]);
	}else $from = mktime(0,0,0)-1209600;
	
	if(SUBPAGE){
		$to = explode(".", SUBPAGE);
		$to = mktime(0,0,0, (INT)$to[1], (INT)$to[0], (INT)$to[2]);
	}else $to = mktime(0,0,0);
	
	define("PERIOD", floor(($to - $from) / 86400) * 86400);
	$mySQL->real_query("SELECT `day`,`views`,`reviews` FROM `gb_user-analytics` WHERE `day` BETWEEN ".$from." AND ".$to);
	$list=array();
	if($result=$mySQL->store_result()){
		while($row = $result->fetch_assoc()){
		    $list[$row['day']] = array("views"=>$row['views'], "views"=>$row['views'], "reviews"=>$row['reviews']);
			$unique += $row['views'];
			$total += $row['reviews'];
		}
		$result->free();
	}
	$mySQL->real_query("SELECT `day`,`views` FROM `gb_user-analytics` WHERE `day` BETWEEN ".($from - PERIOD)." AND ".($to - PERIOD));
	if($result=$mySQL->store_result()){
		while($row = $result->fetch_assoc()){
		    $list[$row['day'] + PERIOD]['last'] = $row['views'];
		}
		$result->free();
	}
	
	$date = new DateTime(date("Y-m-d", $from));
	for($i=$from; $i<=$to; $i+=86400){
		$timestamp = $date->format("U");
		$list['days'][] = $date->format("d F, Y");
		$list['views'][] = (INT)$list[$timestamp]['views'];
		$list['reviews'][] = (INT)$list[$timestamp]['reviews'];
		$list['last'][] = (INT)$list[$timestamp]['last'];
		unset($list[$timestamp]);
		$date->add(new DateInterval('P1D'));
	}
	$uniquemax = max($list['views']);
	$min = min(array(min($list['views']), min($list['last'])));
	$max = max(array(max($list['reviews']), max($list['last'])));
	
	
	$xf = floor(FW / (PERIOD / 86400));
	$yf = round(FH / ($max - $min), 2);
	
	
	for($i=count($list['views']); $i--;){
		$x = $i * $xf + HSPACE;

		$view = (FH + VSPACE - (($list['views'][$i] - $min) * $yf));
		$review = (FH + VSPACE - (($list['reviews'][$i] - $min) * $yf));
		$last = (FH + VSPACE - (($list['last'][$i] - $min) * $yf));
	
		$points .= "<g onmouseover='showInfo(this, evt)' data-day='".$list['days'][$i]."'>";
		$points .= "<line x1='".$x."' x2='".$x."' y1='".VSPACE."' y2='".(FH+VSPACE)."' class='subaxis'/>";
		$points .= "<circle cx='".$x."' cy='".$last."' data-title='Last period' data-value='".$list['last'][$i]."' r='4' stroke='#BAC' stroke-width='0' fill='#BAC'/>";
		$points .= "<circle cx='".$x."' cy='".$review."' data-title='Total views' data-value='".$list['reviews'][$i]."' r='5' stroke='#2DB' stroke-width='0' fill='#2DB'/>";
		$points .= "<circle cx='".$x."' cy='".$view."' data-title='Unique views' data-value='".$list['views'][$i]."' r='6' stroke='#F68' stroke-width='0' fill='#F68'/>";
		$points .= "</g>";
		
		$list['views'][$i] = $x.",".$view;
		$list['reviews'][$i] = $x.",".$review;
		$list['last'][$i] = $x.",".$last;
	}
	
	$paddingLeft = HSPACE.",".(FH+VSPACE);
	$paddingRight = (FW+HSPACE).",".(FH+VSPACE);
	
	//$from = date("d.m.Y", $from);
	//$to = date("d.m.Y", $to);
	
?>
		<aside id="leftbar">
			<a href="/" id="goolybeep">
				<img src="/tpls/skins/subway/imgs/goolybeep.png">
			</a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list">&#xe5c3;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption"><span data-translate="nodeValue">modules</span></div>
					<div class="root">
						<?php print($brancher->tree($brancher->register)); ?>
					</div>
				</div>
			</div>
		</aside>
		<form id="topbar" class="panel" onsubmit="return setPeriod(this)">
			<input class="tool" required name="from" placeholder="From" value="<?php print(date("d.m.Y", $from)); ?>" onfocus="datepicker(event, 'blue')" size="9">
			<input class="tool" required name="to" placeholder="To" value="<?php print(date("d.m.Y", $to)); ?>" onfocus="datepicker(event, 'red')" size="9"> 
			<button class="tool">&#xf021;</button>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('analytics')">&#xf013;</span>
			</div>
		</form>
		<div id="environment">
			<br>
			<svg width="100%" viewBox="0 0 <?php print((FW+HSPACE*2)." ".(FH+VSPACE*2)); ?>" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				<style>
					line.axis{
						stroke:#000;
						stroke-width:0.6;
						stroke-dasharray:1,2;
					}
					g:hover>line.subaxis{
						 stroke:#555;
						 stroke-width:2;
						 stroke-dasharray:2,4;
					}
					g:hover>circle{
						stroke-width:15;
					}
				</style>
				<polygon points="<?php print($paddingLeft." ".implode(" ", $list['reviews'])." ".$paddingRight); ?>" stroke-width="0" fill="#3EC"/>
				<polygon points="<?php print($paddingLeft." ".implode(" ", $list['views'])." ".$paddingRight); ?>" stroke-width="0" stroke="#F06" fill="#FAA"/>
				<polygon points="<?php print($paddingLeft." ".implode(" ", $list['last'])." ".$paddingRight); ?>" stroke-width="0" fill="#536" fill-opacity="0.2"/>
<?php

	print($points);
	for($i=VSPACE; $i<=(FH+VSPACE); $i+=50) $axis .= "<line x1='".HSPACE."' x2='".(FW+HSPACE)."' y1='".$i."' y2='".$i."' class='axis'/>";
	for($i=HSPACE; $i<=(FW+HSPACE); $i+=50) $axis .= "<line x1='".$i."' x2='".$i."' y1='".VSPACE."' y2='".(FH+VSPACE)."' class='axis'/>";
	print($axis);

?>		
				<circle cx="100"  cy="<?php print(FH+85); ?>" r='10' fill='#3EC'/>
				<text x="120" y="<?php print(FH+90); ?>" fill='grey' data-translate="nodeValue">total views</text>
				<circle cx="270"  cy="<?php print(FH+85); ?>" r='10' fill='#F06'/>
				<text x="290"  y="<?php print(FH+90); ?>" fill='grey' data-translate="nodeValue">unique views</text>
				<circle cx="480"  cy="<?php print(FH+85); ?>" r='10' fill='#BAC'/>
				<text x="500"  y="<?php print(FH+90); ?>" fill='grey' data-translate="nodeValue">unique of last</text>
			</svg>
			<div id="total">
				<span><label data-translate="nodeValue">max unique</label>: <big><?php print($uniquemax); ?></big></span>
				<span><label data-translate="nodeValue">total unique</label>: <big><?php print($unique); ?></big></span>
				<span><label data-translate="nodeValue">total views</label>: <big><?php print($total); ?></big></span>
			</div>
<?php
	
/* Pages view ******************************************************/
	
	$limit = 30;
	$page = PARAMETER ? PARAMETER : 1;
	
	$list = $mySQL->query("SELECT SQL_CALC_FOUND_ROWS * FROM `gb_pages` LEFT JOIN `gb_blogfeed` USING(`PageID`) LEFT JOIN `gb_sitemap` USING(`PageID`) WHERE `created` BETWEEN ".$from." AND ".($to+86400)." ORDER BY `views` DESC LIMIT ".(($page-1)*$limit).", ".$limit);
	$count = reset($mySQL->single_row("SELECT FOUND_ROWS()"));
	
	$color=16777215;
	foreach($list as $row){
		$id = empty($row['title']) ? $row['ID'] : $row['title'];
		$time = ($row['time'] / $row['views'])>>0;
		$pages .= "
		<tr align='center' bgcolor='#".dechex($color^=1052688)."'>
			<td><a target='_blank' href='".BASE_DOMAIN."/".$id."'>".$row['PageID']." | ".$id."</a></td>
			<td>".$row['views']."</td>
			<td>".sprintf("%02d:%02d", ($time / 60)>>0, ($time % 60))." <progress value='".$time."' max='600' style='width:100px'></progress></td>
			<td>".$row['shares']."</td>
			<td>".$row['comments']."</td>
		</tr>";
	}
?>
			<table width="100%" class="page-analytics">
				<thead>
					<tr>
						<th>PageID / ID</th>
						<th data-translate="nodeValue">views</th>
						<th data-translate="nodeValue" width="160px">average</th>
						<th>shares</th>
						<th>comments</th>
					</tr>
				</thead>
				<tbody>
				<?php print($pages); ?>
				</tbody>
			</table>
			<div class="pagination" align="right">
<?php
	
	$total=ceil($count/$limit);	// Total pages
	$path="analytics/".date("d.m.Y",$from)."/".date("d.m.Y",$to);
	if($total>1){
		if($page>4){
			$j=$page-2;
			$pagination="<a href='/".$path."/1'>1</a> ... ";
		}else $j=1;
		for(; $j<$page; $j++) $pagination.="<a href='/".$path."/".$j."'>".$j."</a>";					
		$pagination.="<a class='selected'>".$j."</a>";
		if($j<$total){
			$pagination.="<a href='/".$path."/".(++$j)."'>".$j."</a>";
			if(($total-$j)>1){
				$pagination.=" ... <a href='/".$path."/".$total."'>".$total."</a>";
			}elseif($j<$total){
				$pagination.="<a href='/".$path."/".$total."'>".$total."</a>";
			}
		}
	}
	print($pagination);

?>
			</div>
		</div>
		<div id="rightbar">
			<aside class="tabbar right">
				<div class="toolbar">
					<span class="tool" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<div id="manual" class="tab" style="display:block">
<?php
	
	include_once("modules/manual/embed.php");

?>

			</div>
		</div>

<?php

	$tpl = ob_get_contents();
	ob_end_clean();
	
	$tpl = DOMDocument::loadHTML($tpl);
	
//	$wordlist = new Wordlist(array("modules","analytics","base"));
//	$wordlist->translateDocument($tpl);
	
	print($tpl->saveHTML());

?>
	</body>
</html>