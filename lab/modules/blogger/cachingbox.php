<?php
	
	$brancher->auth("blogger") or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();

?>

<form id="<?php print($handle); ?>" autocomplete="on" onsubmit="return false" onreset="boxList.drop(this.id)" class="box"  onmousedown="boxList.focus(this)" style="max-width:540px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList[boxList.onFocus].drop()"></span>
		Caching page
	</div>
	<div class="box-body">
		<div class="log">
			<table cellspacing="8">
				<tbody>
<?php

	$domain = explode(".", $_SERVER['SERVER_NAME']);
	foreach(scandir("../") as $dir){
		if($dir!="." && $dir!=".."){
			if(file_exists("../".$dir."/modules/render/index.php")){
				$cng = JSON::load("../".$dir."/config.init");
				$theme = $cng['general']['theme']['value'];
				$domain[0] = "<b class='red'>".$dir."</b>";
				$rows .= "<tr><td><label><input name='subdomain' value='".$dir."' type='checkbox' style='vertical-align:middle'> ".implode(".", $domain)."</label></td><td><select name='".$dir."' style='padding:4px 15px'>";
				
				foreach($cng['general']['default template']['valid'] as $tpl){
					if(file_exists("../".$dir."/themes/".$theme."/".$tpl.".html")){
						if($tpl===$cng['general']['default template']['value']){
							$rows .= "<option selected value='".$tpl."'>".$tpl."</option>";
						}else $rows .= "<option value='".$tpl."'>".$tpl."</option>";
					}
				}
				$rows .= "</select> ".( ($dir=="ina")?"<label class='tool' onmousedown='imgBox(this)'>&#xf07c;</label>":"" )."</td></tr>";
			}
		}
	}
	print($rows);
?>
				<script>
				(function(lst){
					
				})(document.currentScript.parentNode)
				</script>
				</tbody>
			</table>
		</div>
	</div>
	<div class="box-footer">
		<button type="submit">Ok</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>