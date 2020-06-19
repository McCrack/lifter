<?php
	$handle = "b".time();
?>
<form id="<?=$handle?>" onsubmit="return false" class="box" onreset="boxList[handle].drop()" onmousedown="boxList.focus(this)" style="max-width:780px;background:#234;color:white">
	<link rel="stylesheet" type="text/css" href="/modules/taskboard/tpl/taskboard.css">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="right" style="width:36px;height:36px;font-size:28px;color:white;cursor:pointer" title="close" data-translate="title" onclick="boxList[handle].drop()">╳</span>
		<small>TaskBOARD</small>
	</div>
	<div class="box-body" style="height:480px;background-color:white;color:#555">
	<? include_once("modules/taskboard/".USER_GROUP."/index.php") ?>
	</div>
</form>