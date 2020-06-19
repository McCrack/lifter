<?php
	
	$brancher->auth("settings") or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();
	
?>

<form id="<?php print($handle); ?>" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="max-width:780px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		<span data-translate="textContent">settings</span>
	</div>
	<div class="box-body" style="border:1px inset white">
		<table class="settings" width="100%" rules="cols" cellpadding="4" cellspacing="0" bordercolor="#999">
			<thead>
				<tr bgcolor="#444" style="color:#EEE"><td class="section" data-section="<?php print(SUBPAGE); ?>" align="center" colspan="5"><big data-translate="textContent"><?php print(SUBPAGE); ?></big></td></tr>
				<tr bgcolor="#38B" style="color:#FFF">
					<th width="26"></th>
					<th width="150">Key</th>
					<th>Value</th>
					<th>Valid values</th>
					<th width="26"></th>
				</tr>
			</thead>
			<tbody>
<?php

	$map = $brancher->createMap(SUBPAGE);
	$module = &$brancher->getModule($brancher->register, $map);
	
	$color=16777215;
	foreach($module['options'] as $key=>$val){
		if(is_array($val['value'])) $val['value'] = implode(", ", $val['value']);
		$rows.="
		<tr align='center' bgcolor='#".dechex($color^=1381653)."' data-type='".$val['type']."'>
			<th bgcolor='white'><span title='add row' data-translate='title' class='tool' onclick='addRow(this)'>&#xe908;</span></th>
			<td contenteditable='true' data-key='".$key."' data-translate='textContent' ".(empty($key) ? "contenteditable='true'" : "").">".$key."</td>
			<td contenteditable='true'>".$val['value']."</td>
			<td contenteditable='true'>".implode(", ", $val['valid'])."</td>
			<th bgcolor='white'><span title='delete row' data-translate='title' class='tool' onclick='deleteRow(this)'>&#xe907;</span></th>
		</tr>";
	}
	print($rows)

?>
			</tbody>
		</table>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">save</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>