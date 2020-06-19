<?php
	
	$brancher->auth("themes") or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();
	ob_start();
	
?>
<script>
function previewTheme(src){
	var box = boxList[boxList.onFocus];
		box.body.querySelector("img").src = src;
}
</script>
<form id="<?php print($handle); ?>" onsubmit="return createTheme(this.theme.value)" class="box"  onmousedown="boxList.focus(this)" style="max-width:550px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop('<?php print($handle); ?>')"></span>
		<span data-translate="nodeValue">template</span>
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
		<button type="submit" data-translate="nodeValue">create</button>
		<button type="reset" data-translate="nodeValue" onclick="boxList.drop(boxList.onFocus)">cancel</button>
	</div>
</form>

<?php	
	$tpl = ob_get_contents();
	ob_end_clean();
	
	$tpl = DOMDocument::loadHTML($tpl);
	
	$wordlist = new Wordlist(array("base"));
	$wordlist->translateDocument($tpl);
	
	print($tpl->saveHTML());

?>