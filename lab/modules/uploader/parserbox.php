<?php

	$brancher->auth() or die(include_once("modules/auth/alert.html"));
		
	$p = JSON::load('php://input');
	$html = file_get_contents($p['url']);
	$dom = new DOMDocument();
	$dom->loadHTML($html);
	$images=$dom->getElementsByTagName("img");
	
	$page_url=parse_url($p['url']);
	
	for($i=0; $i<$images->length; $i++){
		$img_url = parse_url($images->item($i)->getAttribute("src"));
		if(empty($img_url['scheme'])){
			$img_url['scheme']=$page_url['scheme'];
			$img_url['host']=$page_url['host'];
		}
		$path=$img_url['scheme']."://".$img_url['host']."".$img_url['path'];
		$imgs.="
		<label class='file-sticker'>
			<input type='checkbox' value='".$path."'>
			<figure data-type='file'>
				<img src='".$path."'>
				".basename($img_url['path'])."
			</figure>
		</label>";
	}
	
	$handle = "b".time();

?>

<form id="<?php print($handle); ?>" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="max-width:872px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop('<?php print($handle); ?>')"></span>
		<span data-translate="textContent">import images</span>
	</div>
	<div class="box-body" style="height:460px;background-color:#F5F5F5;border:1px inset white">
		<?php print($imgs); ?>
	</div>
	<div class="box-footer">
		<div class="left">
			Filter: <select name="rule" onchange="imgSizeFilter()" style="padding:4px 8px;"><option value="naturalHeight">Height</option><option value="naturalWidth">Width</option></select> > <input name="size" value="100" oninput="imgSizeFilter()" size="6" style="padding:4px 8px;"> px
		</div>
		<button type="submit" data-translate="textContent">import</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>