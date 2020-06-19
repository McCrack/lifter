<?php

	$handle = "b".time();
		
?>
<form id="<?=$handle?>" onsubmit="return false" class="box" onreset="boxList[handle].drop()" onmousedown="boxList.focus(this)" style="width:99%;max-width:868px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
var handle = "<?=$handle?>";
boxList[handle].getModuleContent = function(form){
	var urls = form.querySelector(".uploader-frame").contentWindow.document.getImages();
	return urls[0];
}
boxList[handle].openFile = function(src){
	parent.window.editor.doc.insertImage(src);
}
boxList[handle].onopen = function(){
	reauth();
	var frame = doc.create("iframe","",{ "src":"/uploader/videobox", "class":"uploader-frame", "height":"460px"});
	boxList[handle].body.appendChild(frame);
	boxList[handle].align();
}
</script>
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList[handle].drop()"></span>
		<span data-translate="textContent">Select video</span>
	</div>
	<div class="box-body">
		
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">insert</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>