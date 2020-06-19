<?php

	$handle = "b".time();
	
?>

<div id="<?php print($handle); ?>" class="box"  onmousedown="boxList.focus(this)" style="max-width:450px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop('<?php print($handle); ?>')"></span>
		<span data-translate="textContent">all modules</span>
	</div>
	<form class="box-body">
<?php
	foreach(scandir("modules/editor/modules") as $dir){
		if(is_dir("modules/editor/modules/".$dir) && $dir!="." && $dir!=".."){
			$modules .= "<label class='modules'><input name='module' value='".$dir."' type='radio'> ".$dir."</label>";
		}
	}
	print($modules);
?>
	</form>
</div>