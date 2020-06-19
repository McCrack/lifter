<?php
	$handle = "b".time();
?>
<form id="<?=$handle?>" onsubmit="return false" class="box" onreset="boxList[handle].drop()"  onmousedown="boxList.focus(this)" style="width:99%;max-width:680px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
var handle = "<?=$handle?>";
boxList[handle].getModuleContent = function(form){
	var video = form.querySelector(".uploader-frame").contentWindow.document.getVideo();
	var figure = doc.create("figure",doc.create("video","<source src='"+video.src+"' type='"+video.type+"' />",{
		controls:"controls",
	}),{class:"video"});
	return figure.outerHTML;
}
boxList[handle].onopen = function(){
	reauth();
	var frame = doc.create("iframe","",{ "src":"/uploader/video-frame", "class":"uploader-frame", "height":"320px", "style":"border:1px solid #AAA;box-sizing:border-box;"});
	boxList[handle].body.insertToBegin(frame);
	boxList[handle].align();
}
</script>
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList[handle].drop()"></span>
		Insert Video
	</div>
	<div class="box-body">
		
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">insert</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>