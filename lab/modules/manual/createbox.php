<?php

	$handle = "b".time();
	
?>
<form id="<?php print($handle); ?>" class="box"  onmousedown="boxList.focus(this)" style="width:99%;max-width:360px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		Create material
	</div>
	<div id="prompt" class="box-body" style="resize:none">
		<fieldset style="float:left;height:64px"><legend>Name:</legend>
			<input name="material" required style="width:224px" placeholder="..." value=""/>
		</fieldset>
		<fieldset style="height:64px"><legend>Language:</legend>
			<select name="language">
<?php 
	
	$cnf = JSON::load("config.init");
	$laguageSet = $cnf['general']['language']['valid'];
	foreach($laguageSet as $lang){
		if($lang === USER_LANG){
			print("<option value='".$lang."' selected>".$lang."</option>");
		}else print("<option value='".$lang."'>".$lang."</option>");
	}
	
?>
			</select>
		</fieldset>
<?php

	if(SUBPAGE){
		$doc = $mySQL->single_row("SELECT `id`,`title`,`language` FROM `gb_documentation` WHERE `id`=".SUBPAGE." LIMIT 1");
?>
		<fieldset><legend>Parent:</legend>
			<label><input name="pid" type="radio" required value="0">Root</label>
			<label><input name="pid" type="radio" required value="<?php print($doc['id']); ?>"><?php print($doc['title'].".".$doc['language']); ?></label>
		</fieldset>
<?php
		
	}else print "<input name='pid' type='hidden' value='0'>";

?>			
	</div>
	<div class="box-footer">
		<button type="submit">Ok</button>
		<button type="reset" onclick="boxList[boxList.onFocus].drop()" data-translate="textContent">cancel</button>
	</div>
</form>