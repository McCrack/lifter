<?php
	$handle = "b".time();
?>
<form id="<?=$handle?>" onsubmit="return false" class="box" onreset="boxList[handle].drop()" onmousedown="boxList.focus(this)" style="width:99%;max-width:680px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
	var handle = "<?=$handle?>";
	function ParseVideoURL(event){
		var url = event.clipboardData.getData('text');
		var link = doc.create("a", "", {"href":url});
		var mode = link.pathname.split(/\//);
			mode = mode[mode.length-1];
		if(mode==="watch"){
			var items = link.search.split(/\?|&/);
			for(var i=0;  i<items.length; i++){
				items[i]=items[i].split(/=/);
				if(items[i][0]==="v"){
					url = "//www.youtube.com/embed/"+items[i][1];
					break;
				}
			}
		}else url = "//www.youtube.com/embed/"+mode;
		event.target.value = url;
		event.target.form.querySelector("iframe").src = url;
		return false;
	}	
	boxList[handle].getModuleContent = function(form){
		var figure = doc.create("figure", "<iframe src='"+form.querySelector("iframe").src+"' frameborder='0' allowfullscreen><\/iframe>", { "class":"video", "contenteditable":"false" });
		return figure.outerHTML;
	}
</script>
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList[handle].drop()"></span>
		YouTube
	</div>
	<div class="box-body">
		<input name="url" onpaste="return ParseVideoURL(event)" placeholder="URL:" style="width:100%;padding:5px 10px;box-sizing:border-box;border-radius:4px;border:1px solid #BBB;" pattern=".*">
		<div align="center">
			<iframe src="https://www.youtube.com/embed" frameborder="0" allowfullscreen width="99%" height="300px"></iframe>
		<div>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">insert</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>