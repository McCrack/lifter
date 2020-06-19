<?php

	$brancher->auth("developer") or die(include_once("modules/auth/alert.html"));
	$handle = "b".time();

?>
<form id="<?php print($handle); ?>" onsubmit="return createTheme(this.theme.value)" class="box" onreset="boxList[handle].drop()"  onmousedown="boxList.focus(this)" style="max-width:550px">
<script>
	var handle = "<?php print $handle; ?>";
	boxList[handle].previewTheme = function(src){
		boxList[handle].body.querySelector("img").src = src;
	}
</script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList[handle].drop()"></span>
		<span data-translate="textContent">template</span>
	</div>
	<div class="box-body" style="height:260px">
		<div class="leftbar" style="height:100%;overflow-y:scroll;padding:15px;box-sizing:border-box;background-color:white">
<?php
	foreach(scandir("modules/themes/templates") as $dir){
		if(is_dir("modules/themes/templates/".$dir) && $dir!="." && $dir!=".."){
			$img = file_exists("modules/themes/templates/".$dir."/".$dir.".png") ? "modules/themes/templates/".$dir."/".$dir.".png" : "/images/NIA.jpg";
			$themes .= "<label class='tree-item' onclick='previewTheme(`".$img."`)'><input name='theme' value='".$dir."' type='radio'> ".$dir."</label>";
		}
	}
	print($themes);
?>
		</div>
		<img src="/images/NIA.jpg" style="width:calc(100% - 250px);height:100%;object-fit:cover;" align="left">
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">create</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>