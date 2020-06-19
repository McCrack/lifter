<?php


	//$brancher->auth() or die(include_once("modules/auth/alert.html"));
	
	$standby = JSON::load("core/standby.json");
	$path = $standby['uploader']['path'];

	$path = explode("/", $standby['uploader']['path']);
	$subdomain = array_shift($path);
	$path = implode("/", $path);
	
	$fullpath = "../".$subdomain."/data".(empty($path) ? "" : "/".$path);
	
	define("DOMAIN", $config->{"../".$subdomain});
	foreach(scandir($fullpath) as $file){
		$realpath = empty($path) ? $file : $path."/".$file;
		if(is_file($fullpath."/".$file)){
			if(reset(explode("/", mime_content_type($fullpath."/".$file)))==="image"){
				$files.="
				<label class='sticker'>
					<img class='preview' src='".DOMAIN."/data/".$realpath."'><br>
					".$file."
				</label>";
			}
		}elseif(is_dir($fullpath."/".$file) && $file!="." && $file!=".."){
			$dirs .= "
			<label class='sticker'>
				<img class='preview' data-path='".$subdomain."/".$realpath."' src='/images/mime/folder.png'><br>
				".$file."
			</label>";
		}
	}

	$handle = "b".time();
	ob_start();

?>

<form id="<?php print($handle); ?>" class="box"  onmousedown="boxList.focus(this)" style="max-width:700px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop('<?php print($handle); ?>')"></span>
		<span data-translate="nodeValue">insert image</span>
	</div>
	<div class="box-body" style="border:1px inset white;background-color:#F5F5F5;">
		<div class="panel">
			<input name="path" class="tool" value="<?php print($standby['uploader']['path']); ?>" readonly style="text-align:left;width:510px">
			<div class="toolbar">
				<span title="out folder" onclick="boxList[boxList.onFocus].outFolder()" data-translate="title" class="tool">&#xf0b1;</span>
				<span title="create folder" onclick="boxList[boxList.onFocus].createFolder()" data-translate="title" class="tool">&#xe2cc;</span>
				<span title="upload" onclick="boxList[boxList.onFocus].uploadImg()" data-translate="title" class="tool">&#xf07c;</span>
			</div>
		</div>
		<div class="environment" ondblclick="boxList[boxList.onFocus].open(event.target)" style="height:420px;width:100%;">
			<?php print($dirs."".$files); ?>
		</div>
	</div>
</form>

<?php

	$tpl = ob_get_contents();
	ob_end_clean();
	
	$tpl = DOMDocument::loadHTML($tpl);
	
	$wordlist = new Wordlist(array("base","uploader"));
	$wordlist->translateDocument($tpl);
	
	print($tpl->saveHTML());

?>