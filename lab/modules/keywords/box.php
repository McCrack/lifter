<?php
	
	$brancher->auth() or die(include_once("modules/auth/alert.html"));
	
	$rows = $mySQL->query("SELECT SQL_CALC_FOUND_ROWS * FROM `gb_keywords`");
	
	$color=16777215;
	foreach($rows as $row){
		$keywords .= "
		<tr bgcolor='#".dechex($color^=1052688)."'>
			<th onclick='showWordlistBox(this.next())' bgcolor='white' title='wordlist' dtat-translate='title'><span class='tool'>&#xe431;</span></th>
			<td>".$row['tag']."</td>
			<td>".$row['id']."</td>
			<td>".$row['rating']."</td>
		</tr>";
	}	
	$used = reset($mySQL->single_row("SELECT FOUND_ROWS()"));
	
	$fields = reset($mySQL->group_rows("SHOW COLUMNS FROM `gb_tagination`"));
	
	$handle = "b".time();

?>
<form id="<?php print($handle); ?>" onsubmit="return boxList[handle].addSection()" class="box" onreset="boxList[boxList.onFocus].drop()" onmousedown="boxList.focus(this)" style="max-width:720px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
var handle = "<?php print $handle; ?>";
boxList[handle].addSection = function(){
	XHR.push({
		"Content-Type":"text/plain",
		"addressee":"/keywords/actions/add-section",
		"onsuccess":function(response){
			boxList[boxList.onFocus].window.querySelector("#keycount").innerHTML = response;
		}
	});
	return false;
}
</script>
	<div class="box-title">
		<span class="close-box" title="clase" data-translate="title" onclick="boxList.drop('<?php print($handle); ?>')"></span>
		Keywords:
	</div>
	<div class="box-body">
		<table width="100%" rules="cols" cellpadding="4" cellspacing="0" bordercolor="#CCC">
			<thead>
				<tr bgcolor="#555" style="color:white">
					<th width="22"></th>
					<th>Keyword</th>
					<th width="80">ID</th>
					<th width="110">Used count</th>
				</tr>
			</thead>
			<tbody align="center">
<?php
	print($keywords);
?>
			</tbody>
		</table>
	</div>
	<div class="box-footer">
		<span id="keycount"><?php print("Used: ".$used."/".((count($fields)-1) * 32)); ?></span>
		<button type="submit">Add 32 cells</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>