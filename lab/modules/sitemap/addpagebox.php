<?php

	$brancher->auth("sitemap") or die(include_once("modules/auth/alert.html"));

	$handle = "b".time();

?>
<form id="<?php print($handle); ?>" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="width:99%;max-width:480px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title"><small data-translate="textContent">create page</small></div>
	<div id="prompt" class="box-body" style="resize:none">
		<br>
		<fieldset style="float:right;height:64px"><legend data-translate="textContent">language</legend>
			<select name="language">
				
<?php 
	
	$cng = JSON::load("../".BASE_FOLDER."/config.init");
	$laguageSet = $cng['general']['language']['valid'];
	foreach($laguageSet as $lang){
		if($lang === SUBPAGE){
			$languages .= "<option value='".$lang."' selected>".$lang."</option>";
		}else $languages .= "<option value='".$lang."'>".$lang."</option>";
	}
	print($languages);

?>
				
			</select>
		</fieldset>
		<fieldset style="float:right;height:64px"><legend data-translate="textContent">type</legend>
			<select name="entity">
				<option value="category">Category</option>
				<option value="material">Material</option>
			</select>
		</fieldset>
		<fieldset style="height:64px"><legend data-translate="textContent">page name</legend>
			<input name="url" pattern="[а-яА-Я0-9a-zA-Z_ -]+" required value="" placeholder="..." style="width:100%;box-sizing:border-box;">
		</fieldset>
	</div>
	<div class="box-footer">
		<button type="submit">Ok</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>