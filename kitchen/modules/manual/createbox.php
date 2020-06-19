<?php

	$handle = "b".time();
	
?>
<form id="<?=$handle?>" class="box" onreset="boxList[boxList.onFocus].drop()"  onmousedown="boxList.focus(this)" style="width:99%;max-width:360px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		Create material
	</div>
	<div id="prompt" class="box-body" style="resize:none">
		<fieldset style="float:left;height:64px"><legend>Name:</legend>
			<input name="material" required style="width:224px" placeholder="..." value=""/>
		</fieldset>
		<fieldset style="height:64px"><legend>Language:</legend>
			<select name="language">
			<?foreach(JSON::load("config.init")['general']['language']['valid'] as $lang) if($lang === PARAMETER):?>
				<option value="<?=$lang?>" selected><?=$lang?></option>
			<?else:?>
				<option value="<?=$lang?>"><?=$lang?></option>
			<?endif?>
			</select>
		</fieldset>
		<?if(SUBPAGE):
			$doc = $mySQL->single_row("SELECT id,title,language FROM gb_manuals WHERE id=".SUBPAGE." LIMIT 1")?>
		<fieldset><legend>Parent:</legend>
			<label><input name="pid" type="radio" required value="0">Root</label>
			<label><input name="pid" type="radio" required value="<?=$doc['id']?>" checked><?=($doc['title'].'.'.$doc['language'])?></label>
		</fieldset>
		<?else:?>
		<input name="pid" type="hidden" value="0">
		<?endif?>
	</div>
	<div class="box-footer">
		<button type="submit">Ok</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
	<script>
	(function(form){
		form.onsubmit = function(event){
			event.preventDefault();
			XHR.push({
				addressee:"/manual/actions/create/"+(location.pathname.split(/\//)[2] || 0),
				body:JSON.stringify({
					"name":form.material.value.trim(),
					"language":form.language.value,
					"pid":form.pid.value
				}),
				onsuccess:function(response){
					isNaN(response) ? alertBox(response) : location.pathname = "/manual/"+LANGUAGE+"/"+response;
				}
			});
		}
	})(document.currentScript.parentNode)
	</script>
</form>