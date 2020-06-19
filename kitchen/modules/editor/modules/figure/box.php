<?php
	$handle = "b".time();
?>
<form id="<?php print($handle); ?>" onsubmit="return false" class="box" onreset="boxList[handle].drop()"  onmousedown="boxList.focus(this)" style="width:99%;max-width:680px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
var handle = "<?=$handle?>";
boxList[handle].getModuleContent = function(form){
	var figure = doc.create("figure", "<img src='"+form.querySelector(".uploader-frame").contentWindow.document.getImage()+"' alt='"+form.alt.value+"'>");
	var caption = form.caption.value || null;
	if(caption){
		var figcaption = doc.create("figcaption", "", {"class":"op-center"});
		var link = form.link.value || null;
		if(link){
			figcaption.appendChild(doc.create("a", caption, { "href":link, "target":"_blank", "rel":"nofolow" }));
		}else figcaption.textContent = caption;
		figure.appendChild(figcaption);
	}
	return figure.outerHTML;
}
boxList[handle].onopen = function(){
	reauth();
	var frame = doc.create("iframe","",{ "src":"/uploader/image-frame", "class":"uploader-frame", "height":"320px", "style":"border:1px solid #AAA;box-sizing:border-box;"});
		frame.onload = function(){
			this.contentWindow.document.setImage('/images/NIA.jpg');
		}
	boxList[handle].body.insertToBegin(frame);
	boxList[handle].align();
}
</script>
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList[handle].drop()"></span>
		Figure
	</div>
	<div class="box-body">
		
		<fieldset><legend>Caption:</legend>
			<input name="caption" placeholder="©" style="width:100%;padding:5px 10px;box-sizing:border-box;border-radius:4px;border:1px solid #BBB;" pattern=".*">
		</fieldset>
		<fieldset class="left" style="min-width:280px;width:60%"><legend>Link:</legend>
			<input name="link" placeholder="href" style="width:100%;padding:5px 10px;box-sizing:border-box;border-radius:4px;border:1px solid #BBB;" pattern=".*">
		</fieldset>
		<fieldset align="right"><legend>Alternative text:</legend>
			 <input name="alt" placeholder="alt" style="width:100%;padding:5px 10px;box-sizing:border-box;border-radius:4px;border:1px solid #BBB;background-color:#EEE;" pattern=".*">
		</fieldset>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">insert</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>