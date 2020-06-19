<?php
	$handle = "b".time();
?>
<form id="<?php print($handle); ?>" onsubmit="return false" class="box" onreset="boxList[handle].drop()" onmousedown="boxList.focus(this)" style="width:99%;max-width:868px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
var handle = "<?php print $handle; ?>";
boxList[handle].getModuleContent = function(form, edt){
	var urls = form.querySelector(".uploader-frame").contentWindow.document.getImages();
	var imglist = doc.create("div", "", {"class":"imagelist", "align":"right"});
	var slideshow = doc.create("div", "", {"class":"slideshow front-screen", "data-current":"0"});
	for(var i=0; i<urls.length; i++){
		var slide = doc.create("img", "", { "class":"slide", "src":urls[i], "data-slide":i });
		imglist.appendChild(slide);
		slideshow.appendChild(slide.cloneNode());
	}
	var back = doc.create("div", "<div class='leftpoint front-screen' data-dir='-1'></div><div class='rightpoint front-screen' data-dir='1'></div>", {"class":"back-screen"});
		back.appendChild(slideshow);
	
	var embed = doc.create("figure", back, {"class":"embed", "contenteditable":"false"});
		embed.appendChild(imglist);
		XHR.push({
			"Content-Type":"text/javascript",
			"addressee":"/patterns/actions/get-pattern?path=patterns/js/sliders/slideshow.js",
			"onsuccess":function(response){
				embed.appendChild(doc.create("script", response));
				edt.setSelection();
				setTimeout(function(){
					edt.execCommand("insertHTML",false, embed.outerHTML);
					edt.sync();
				}, 10);
			}
		});
	return false;
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
		SlideShow
	</div>
	<div class="box-body">
		
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">insert</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>