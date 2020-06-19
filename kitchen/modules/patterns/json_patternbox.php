<?php

$handle = "b".time();

?>
<form id="<?php print($handle); ?>" class="box"  onmousedown="boxList.focus(this)" style="max-width:820px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
var handle = "<?php print $handle; ?>";
boxList[handle].getJSONPattern=function(obj){
	if(obj.className==="pattern-file"){
		XHR.push({
			"protect":false,
			"addressee":"/patterns/actions/get-pattern?path="+obj.dataset.path+"/"+obj.textContent, 
			"onsuccess":function(response){
				var box = boxList[handle];
					box.window.path.value = obj.dataset.path;
					box.window.pname.value = obj.textContent.split(/\./)[0];
				var edt = ace.edit(box.body.querySelector(".environment"));
					edt.setValue(response);
			}
		});
	}else if(obj.className==="pattern-folder"){
		var box = boxList[handle];
		box.window.path.value = obj.dataset.path;
		box.window.pname.value = "";
	}
	return false;
}
boxList[handle].createPatternsFolder=function(){
	var box = boxList[handle];
	var path = box.window.path.value.trim();
	promptBox("new folder name", function(folderName){
		XHR.push({
			"Content-Type":"text/plain",
			"addressee":"/patterns/actions/create-folder/json",
			"body":(path+"/"+folderName.trim().toLowerCase()),
			"onsuccess":function(response){
				box.body.querySelector(".leftbar").innerHTML = response;
			}
		});
	}, "^[a-zA-Z0-9_-]+$");
}
boxList[handle].removePatterns=function(){
	var box = boxList[handle];
	if(box.window.pname.value){
		var parameter = box.window.pname.value;
		var alrt = "Remove pattern?";
	}else{
		var parameter = "folder";
		var alrt = "Delete directory with files in it?";
	}
	confirmBox(alrt, function(){
		XHR.push({
			"addressee":"/patterns/actions/remove/"+parameter+"?path="+box.window.path.value,
			"onsuccess":function(response){
				box.body.querySelector(".leftbar").innerHTML = response;
			}
		});
	});
}
</script>
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		<span style="color:#E84;">JSON</span>Pattern
	</div>
	<div class="box-body" 
		style="
		height:400px;
		resize:none;
		outline:1px solid #BBB;
		background-color:white;">
		<div class="panel" align="left">
			<div class="toolbar right">
				<span class="tool" title="create folder" data-translate="title" onclick="boxList[handle].createPatternsFolder()">&#xe2cc;</span>
				<span class="tool" title="remove" data-translate="title" onclick="boxList[handle].removePatterns()">&#xe9ac;</span>
			</div>
			<input name="path" value="patterns/json" placeholder="path" data-translate="placeholder" required readonly style="
				width:454px;
				padding:4px 10px;
				border-radius:3px;
				border:1px solid #BBB;"> 
			<input name="pname" placeholder="pattern name" data-translate="placeholder" pattern="[a-zA-Z0-9_-]+" required style="
				margin:4px;
				padding:4px 10px;
				border-radius:3px;
				border:1px solid #BBB;
				width:calc(100% - 600px);">
		</div>
		<div class="leftbar" onclick="boxList[handle].getJSONPattern(event.target)" style="height:calc(100% - 36px);overflow-y:scroll;padding:15px;box-sizing:border-box;">
<?php

	print( patterns_tree() );
	
?>
		</div>
		<div class="environment" style="height:calc(100% - 36px);"></div>
	</div>
	<div class="box-footer">
		<button type="reset" data-translate="textContent" onclick="boxList[handle].add()">apply</button>
		<button type="submit" data-translate="textContent">save</button>
		<button type="reset" data-translate="textContent" onclick="boxList[handle].drop()">cancel</button>
	</div>
</form>

<?php

function patterns_tree($path="patterns/json"){
	$items = $dirs = "";
	foreach(scandir($path) as $file){
		if(is_file($path."/".$file)){
			$items .= "<a class='pattern-file' data-path='".$path."'>".$file."</a>";
		}elseif(is_dir($path."/".$file) && ($file!="." && $file!="..")){
			$dirs .= "<label data-translate='textContent' class='pattern-folder' data-path='".$path."/".$file."'>".$file."</label><div class='root'>".patterns_tree($path."/".$file)."</div>";
		}
	}
	return $dirs."".$items;
}
?>