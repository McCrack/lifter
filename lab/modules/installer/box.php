<?php
	$handle = "b".time();
?>
<form id="<?php print($handle); ?>" class="box" onsubmit="return installModule(this)" onchange="checkInstallModule(event.target.value)" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="width:99%;max-width:640px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script src="/modules/installer/tpl/installer.js"></script>
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent.(2).id)"></span>
		Module installer
	</div>
	<div class="box-body" style="resize:none">
		<div class="leftbar" style="height:300px;overflow:auto">
			<div align="left">
				<label class="tool" title="upload" data-translate="title" onclick="onuploadInstaller()">U</label>
				<label class="tool" title="remove" data-translate="title" onclick="removeInstaller()">D</label>
			</div>
			<div class="install-root">
<?php

	foreach(scandir("installs") as $file){
		if(is_file("installs/".$file)){
			$file = explode(".", $file);
			if(end($file)=="zip"){
				$file = reset($file);
				$items .= "<div class='tree-item'><label><input type='radio' name='filename' value='".$file."' required>".$file."</label></div>";
			}
		}
	}

	print($items);
	
?>
			</div>
		</div>
		<div class="environment">
			<div class="install-log" style="padding:10px;height:298px;overflow:auto;background-color:white;border:1px inset white">
			
			</div>
		</div>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">install</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>