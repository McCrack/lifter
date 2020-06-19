<?php
if(USER_GROUP=="admin" || USER_GROUP=="developer" || USER_GROUP=="video editor"){
	$ugroup = PAGE ? PAGE : USER_GROUP;
}else $ugroup = USER_GROUP;
?>
<link rel="stylesheet" type="text/css" href="/modules/taskboard/tpl/taskboard.css">
<script src="/modules/taskboard/tpl/taskboard.js" async charset="utf-8"></script>
<div id="topbar" class="panel">
	<? include_once("modules/taskboard/".USER_GROUP."/toolbar.php") ?>
</div>
<div id="environment">
<? include_once("modules/taskboard/".$ugroup."/index.php") ?>
</div>