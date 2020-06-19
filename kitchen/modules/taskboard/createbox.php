<?php

	$brancher->auth("taskboard") or die(include_once("modules/auth/alert.html"));

	$handle = "b".time();

?>
<form id="<?=$handle?>" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="max-width:560px;background:#1090B0;color:white">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		<sup data-translate="textContent">create card</sup>
	</div>
	<? include_once("modules/taskboard/".USER_GROUP."/createtask.php") ?>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">create</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>