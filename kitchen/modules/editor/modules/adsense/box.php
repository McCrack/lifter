<?php
	
	$handle = "b".time();
		
?>

<form id="<?php print($handle); ?>" class="box" onsubmit="return false" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="max-width:400px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
var handle = "<?php print $handle; ?>";
	boxList[handle].getModuleContent = function(form, edt){
	var inp = form.querySelectorAll("input[type='radio']");
	for(var i=inp.length; i--;){
		if(inp[i].checked){
			XHR.push({
				"Content-Type":"text/plain",
				"addressee":"/editor/actions/google_ad_client",
				"onsuccess":function(response){
					var embed = doc.create(
						"div",
						doc.create("script", "google_ad_client='"+response+"';google_ad_slot="+inp[i].value+";google_ad_width="+inp[i].dataset.width+";google_ad_height="+inp[i].dataset.height+";"),
						{
							"class":"adsense",
							"contenteditable":"false",
							"data-size":inp[i].dataset.width+"x"+inp[i].dataset.height
						}
					)
					embed.appendChild(doc.create("script", " ", { "src":"//pagead2.googlesyndication.com/pagead/show_ads.js" }));
					if(form['float'].value!="none"){ embed.style.float = form['float'].value; }
					edt.setSelection();
					setTimeout(function(){
						edt.execCommand("insertHTML",false, embed.outerHTML);
						edt.sync();
					}, 10);
				}
			});
			break;
		}
	}
	return false;
}
</script>
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		Google Adsense
	</div>
	<div class="box-body">
		<div class="panel">
			<div class="toolbar right">
				<label class="tool">Float:</label>
				<select class="tool" name="float">
					<option value="left">Left</option>
					<option value="right">Right</option>
					<option value="none">None</option>
				</select>
			</div>
		</div>
		<label class="modules"><input name="adsense" value="1880145263" data-width="300" data-height="250" type="radio"> 300x250</label>
		<label class="modules"><input name="adsense" value="7566812063" data-width="336" data-height="280" type="radio"> 336x280</label>
		<label class="modules"><input name="adsense" value="7969048468" data-width="300" data-height="600" type="radio"> 300x600</label>
		<label class="modules"><input name="adsense" value="9021696862" data-width="468" data-height="60" type="radio"> 468x60</label>
		<label class="modules"><input name="adsense" value="8012968460" data-width="728" data-height="90" type="radio"> 728x90</label>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">insert</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>