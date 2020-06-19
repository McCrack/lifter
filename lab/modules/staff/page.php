<?php
	
	$brancher->auth() or die(include_once("modules/auth/page.php"));
	
	$tree = $mySQL->tree("SELECT * FROM `gb_staff` ORDER BY `Group`", "UserID", "Group");
	foreach($tree as $key=>$row){
		$users.="<label class='tree-root-item'>".$key."</label><div class='root' style='display:block;'>";
		foreach($row as $field=>$val){
			$users.="<a href='/staff/".$val['UserID']."' class='tree-item'>".$val['UserID']." - ".$val['Login']."</a>";
		}
		$users.="</div>";
	}
	if(PAGE){
		$user = $mySQL->single_row("SELECT * FROM `gb_staff` LEFT JOIN `gb_community` USING(`CommunityID`) WHERE `UserID`=".PAGE." LIMIT 1");
		$btn = "<button class='red-btn' onclick='return deleteUser(".$user['UserID'].")' data-translate='textContent'>remove</button>";
	}
	$groups = $mySQL->single_row("SHOW COLUMNS FROM `gb_staff` LIKE 'Group'");
	eval("\$groups = ".preg_replace("/^enum/", "array", $groups['Type']).";");
	foreach($groups as $group){
		$gList.="<option ".(($group===$user['Group'])?"selected":"").">".$group."</option>";
	}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.staff</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/staff/tpl/staff.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?php print($config->themes); ?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/staff/tpl/staff.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules" async defer charset="utf-8"></script>
		<script>
			window.onbeforeunload = reauth;
		</script>
	</head>
	<body>
		<aside id="leftbar">
			<a href="/" id="goolybeep"></a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list">&#xe5c3;</span>
						<span class="tool" data-tab="staff-list">&#xe972;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?php print($brancher->tree($brancher->register)); ?>
					</div>
				</div>
				<div class="tab left" id="staff-list">
					<div class="caption"><span data-translate="textContent">staff</span></div>
					<div class="root">
						<?php print($users); ?>
					</div>
				</div>
			</div>
		</aside>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<label class="tool">ID:</label>
				<input class="tool" name="uid" form="user-form" readonly value="<?php print($user['UserID']); ?>" size="2">
			</div>
			<div class="toolbar right">
				<label class="tool">Group:</label> <select class="tool" name="group" form="user-form"><?php print($gList); ?></select>
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('staff')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">
			<form id="user-form" onsubmit="return saveUser(this)">
				<fieldset>
					<div>
						Login:<br>
						<input name="login" value="<?php print($user['Login']); ?>" pattern="[a-zA-Z0-9_-]+" required>
						<br>Password:<br>
						<input name="password" value="<?php print($user['Passwd']); ?>" onchange="this.value=md5(this.value)" required placeholder="MD5">
						<br>Name:<br>
						<input name="user" value="<?php print($user['Name']); ?>">
					</div>
					<div>
						Email:<br>
						<input name="email" value="<?php print($user['Email']); ?>" required placeholder="@">
						<br>Departament:<br>
						<input name="departament" value="<?php print($user['Departament']); ?>">
					</div>
				</fieldset>
				<div align="right">
					<button type="submit" data-translate="textContent">save</button>
					<?php print($btn); ?>
				</div>
			</form>
		</div>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span class="tool" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<div id="manual" class="tab" style="display:block;">
<?php
	
	include_once("modules/manual/embed.php");

?>

			</div>
		</div>
	</body>
</html>