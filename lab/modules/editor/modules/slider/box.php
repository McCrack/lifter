<?php
	$handle = "b".time();
?>
<form id="<?php print($handle); ?>" onsubmit="return false" class="box" onreset="boxList[handle].drop()" onmousedown="boxList.focus(this)" style="width:99%;max-width:868px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
var handle = "<?php print $handle; ?>";
boxList[handle].getModuleContent = function(form){
	var urls = form.querySelector(".uploader-frame").contentWindow.document.getImages();
	var script = "(function(s){var ss=s.first(),img=ss.first();img.onload=function(){img.style.maxWidth=img.offsetWidth+'px';s.style.width=img.offsetWidth+'px';ss.style.width='50%';s.onmousemove=function(e){ss.style.width=(e.clientX-s.offsetLeft)+'px';};s.ontouchstart=function(){s.ontouchmove=function(e){ss.style.width=(e.targetTouches[0].clientX-ss.offsetLeft)+'px';};};}})(doc.scripts[doc.scripts.length-1].parentNode);";
	var embed = doc.create("div");
	for(var i=0; i<urls.length; i+=2){
		var slider = doc.create("span", "", { "class":"slider-substrate", "style":"background-image:url("+urls[i]+")", "contenteditable":"false" });
		var subslide = doc.create("span", "", { "class":"subslide" });
			subslide.appendChild(doc.create("img", "", { "src":urls[i+1] }));
			slider.appendChild(subslide);
			slider.appendChild(doc.create("script", script));
			embed.appendChild(slider);
	}
	return embed.innerHTML;
}
boxList[handle].openFile = function(src){
	return false;
}
boxList[handle].onopen = function(){
	reauth();
	var frame = doc.create("iframe","",{ "src":"/uploader/embed", "class":"uploader-frame", "height":"460px"});
	boxList[handle].body.appendChild(frame);
	boxList[handle].align();
}
</script>
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList[handle].drop()"></span>
		Slider
	</div>
	<div class="box-body">
		
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">insert</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>