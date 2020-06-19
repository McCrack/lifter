<?php

	$handle = "b".time();
	
?>
<form id="<?php print($handle); ?>" class="box"  onmousedown="boxList.focus(this)" style="width:99%;max-width:340px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title" data-translate="textContent">create link</div>
	<div class="box-body" style="resize:none">
		<br>
		<fieldset style="float:left;height:64px"><legend>HREF:</legend>
			<input name="url" style="width:180px" placeholder="URL" value=""/>
		</fieldset>
		<fieldset style="height:64px"><legend>TARGET:</legend>
			<select name="target">
				<option value="_blank">blank</option>
				<option value="_self">self</option>
				<option value="_parent">parent</option>
				<option value="_top">top</option>
			</select>
		</fieldset>
	</div>
	<div class="box-footer">
		<button type="submit">Ok</button>
		<button type="reset" onclick="boxList[boxList.onFocus].drop()" data-translate="textContent">cancel</button>
	</div>
</form>