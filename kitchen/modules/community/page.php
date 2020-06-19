<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));
	
	if(SUBPAGE) $citizen = $mySQL->single_row("SELECT * FROM `gb_community` LEFT JOIN `gb_staff` USING(`CommunityID`) WHERE `CommunityID` = ".SUBPAGE." LIMIT 1");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.community</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
		
		<link rel="stylesheet" type="text/css" href="/modules/community/tpl/community.css">
			
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/editor/tpl/editor.js"></script>
		<script src="/modules/community/tpl/community.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules" async></script>
		<script>
			window.onbeforeunload = reauth;
		</script>
	</head>
	<body>
		<aside id="leftbar">
			<a href="/" id="goolybeep">	</a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list">&#xe5c3;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?=$brancher->tree($brancher->register)?>
					</div>
				</div>
			</div>
		</aside>
		<div id="rightbar"> <!-- Rightbar -->
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span class="tool" data-tab="citizens">&#xe972;</span>
					<span class="tool" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<div id="manual" class="tab">
			<?php
			include_once("modules/manual/embed.php");
			?>
			</div>
			<div id="citizens" class="tab subwhite-bg"> <!-- Citizen list -->
				<?if(SUBPAGE):?>
				<div id="general">
					<span><small>App-ID:</small> <?=($citizen['App'].'-'.$citizen['CitizenID'])?></span>
					<span><small>Name:</small> <?=$citizen['Name']?></span>
					<span><small>Last visit:</small> <?=date("d F Y", $citizen['Visit'])?></span>
					<span><small>Reputation:</small> <?=$citizen['reputation']?></span>
					<span><small>Email:</small> <?=$citizen['Email']?></span>
		
					<?if(empty($citizen['UserID'])):?>
					<div align="right"><button class="toolbtn" onclick="addToStaff()">Add to staff</button></div>
					<?else:?>
					<span><small>Login:</small> <?=$citizen['Login']?></span>
					<span><small>Group:</small> <?=$citizen['Group']?></span>
					<span><small>Departament:</small> <?=$citizen['Departament']?></span>
					<?endif?>
				</div>
				<div class="caption" data-translate="textContent">options</div>
				<div id="options"> <!-- Options -->
					<table width="100%" rules="cols" cellspacing="0" cellpadding="4" bordercolor="#CCC">
						<col width="30"><col><col><col width="30">
						<thead>
							<tr bgcolor="#456">
								<th colspan="2">Key</th>
								<th colspan="2">Value</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$options = JSON::parse($citizen['options']);
						if(!empty($options)): foreach($options as $key=>$val):?>
							<tr>
								<th bgcolor="white"><span class="tool" onclick="addRow(this)">&#xe908;</span></th>
								<td contenteditable="true"><?=$key?></td>
								<td contenteditable="true"><?=$val?></td>
								<th bgcolor="white"><span class="tool" onclick="deleteRow(this)">&#xe907;</span></th>
							</tr>
							<?endforeach?>
						<?else:?>
							<tr>
								<th><span class="tool" onclick="addRow(this)">&#xe908;</span></th>
								<td contenteditable="true"></td>
								<td contenteditable="true"></td>
								<th><span class="tool" onclick="deleteRow(this)">&#xe907;</span></th>
							</tr>
						<?endif?>
						</tbody>
					</table>
					<div align="right">
						<button class="toolbtn" onclick="return saveCitizen()" data-translate="textContent">save</button>
					</div>
				</div>	<!-- End options -->
				<div class="caption" data-translate="textContent">interests</div>
				<div id="interests"> <!-- Interests -->
				<?php
				$tagination = $mySQL->single_row("SELECT * FROM `gb_tagination` WHERE `tid` = ".$citizen['tid']." LIMIT 1");
				$cnt = count($tagination)-1;
				for($j=0; $j<$cnt; $j++) for($i=32; $i--;) if($tagination[$j] &  pow(2, $i)){
					$IDs[]=(32*$j) + ($i+1);
				}
				$keywords = $mySQL->query("SELECT `tag` FROM `gb_keywords` WHERE `id` IN (".implode(",",$IDs).")");
				foreach($keywords as $row):?>
					<span onclick="showWordlistBox(this)"><?=$row['tag']?></span>
				<?endforeach?>
				</div> <!-- End interests -->
				<?endif?>
				<form id="mailing" class="subwhite-bg" onsubmit="return sendLetter(this)"> <!-- Mailing form -->
					<div class="caption" data-translate="textContent">send email</div>
					<p class="left">
						<span data-translate="textContent">sender</span>:
						<input name="from" value="<?=USER_EMAIL?>" size="17">
					</p>
					<p class="right">
						<span data-translate="textContent">recipient</span>:
						<input name="to" value="<?=$citizen['Email']?>" size="18">
					</p>
					<div>
						<input name="theme" placeholder="theme" data-translate="placeholder" style="width:100%;">
					</div>
					<script>
					(function(obj, e){
						var frame = doc.create("iframe","",{ src:"/editor/embed","class":"HTMLDesigner", style:"background-color:white;height:340px;"});
						frame.onload = function(){
							e = new HTMLDesigner(frame.contentWindow.document);
						}
						obj.appendChild(frame);
					})(doc.querySelector('#mailing'));
					</script>
					<button class="toolbtn right" data-translate="textContent">send</button>
				</form> <!-- End mailing form -->
			</div> <!-- End citizen list -->
		</div> <!-- End  rightbar -->
		<form id="topbar" class="panel" method="get"> <!-- Search form -->
			<div class="toolbar">
				<span class="tool" onclick="location.search=''" title="Reset search">&#xf021;</span>
			</div>
			<input class="tool" name="search" pattern="[a-zA-Zа-яА-Я0-9. @-_]{5,}" placeholder="&#xe8b6;" value="<?=$_GET['search']?>">
			<div class="toolbar">
			    <label class="tool">in</label>
			</div>
			<select name="in" class="tool">
				<option value="Name">Names</option>
				<option value="Email" <?=(($_GET['in']==="Email")?"selected":"")?>>Emails</option>
			</select>
			<button class="tool" data-translate="textContent">find</button>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('community')">&#xf013;</span>
			</div>
		</form> <!-- End search form -->
		<div id="environment"> <!-- Environment -->
			
			
				<table width="100%" cellspacing="0" cellpadding="6" class="page-analytics">
					<thead>
						<tr class="caption">
							<th width="36">ID</th>
							<th width="68">App</th>
							<th>Name</th>
							<th>Email</th>
							<th>Last visit</th>
						</tr>
					</thead>
					<tbody onclick="openCitizen(event.target)">
					<?php
					$limit = 50;
					$page = PAGE ? PAGE : 1;

					$where = empty($_GET['search']) ? "" : "WHERE `".$_GET['in']."` LIKE '%".trim($_GET['search'])."%'";
					$list = $mySQL->query("SELECT SQL_CALC_FOUND_ROWS * FROM `gb_community` ".$where." ORDER BY `Visit` DESC LIMIT ".(($page-1)*$limit).", ".$limit);
					$count = reset($mySQL->single_row("SELECT FOUND_ROWS()"));

					foreach($list as $row):?>
						<tr align="center" data-id="<?=$row['CommunityID']?>">
							<td><?=$row['CommunityID']?></td>
							<td><?=$row['App']?></td>
							<td><?=$row['Name']?></td>
							<td><?=$row['Email']?></td>
							<td><?=date("H:i, d.m.Y", $row['Visit'])?></td>
						</tr>
					<?endforeach?>
					</tbody>
					<tfoot>
						<tr bgcolor="#456" style="color:#DDD">
							<td colspan="5">Total: <b><?=$count?></b></td>
						</tr>
					</tfoot>
				</table>
				<div class="pagination" align="right"> <!-- Pagination -->
				<?php
				$total=ceil($count/$limit);	// Total pages
				$path="community";
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
				print $pagination;
				?>
				</div> <!-- End pagination -->
		</div> <!-- End  environment -->
	</body>
</html>