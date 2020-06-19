<?php
	$handle = "b".time();
?>
<form id="<?php print($handle); ?>" onsubmit="return false" class="box" onreset="boxList[handle].drop()" onmousedown="boxList.focus(this)" style="width:99%;max-width:680px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
var handle = "<?php print $handle; ?>";
boxList[handle].getModuleContent = function(form){
	if(form.align.value==="none"){
		var block = doc.create("figure", "", {"class":"hidden-image"});
	}else var block = doc.create("span", "", {"class":"hidden-image", "style":"float:"+form.align.value});
	block.appendChild(doc.create("img", "", {"src":form.querySelector(".uploader-frame").contentWindow.document.getImage()}));
	return block.outerHTML;
}
boxList[handle].onopen = function(){
	reauth();
	var frame = doc.create("iframe","",{ "src":"/uploader/image-frame", "class":"uploader-frame", "height":"320px", "style":"border:1px solid #AAA;box-sizing:border-box;"});
		frame.onload = function(){
			this.contentWindow.document.setImage('/images/NIA.jpg');
		}
	boxList[handle].body.appendChild(frame);
	boxList[handle].align();
}
</script>
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList[handle].drop()"></span>
		Figure
	</div>
	<div class="box-body">
		
	</div>
	<div class="box-footer">
		<div class="left">
			Align: <select name="align"><option>none</option><option>left</option><option>right</option></select>
		</div>
		<button type="submit" data-translate="textContent">insert</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>