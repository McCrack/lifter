<div class="toolbar right">
	<span title="settings" data-translate="title" class="tool" onclick="settingsBox('welcome')">&#xf013;</span>
	<select class="tool" autocomplete="off">
	<?php
	$groups = $mySQL->group_rows("SELECT `Group` FROM gb_staff GROUP BY `Group`")['Group'];
	foreach($groups as $group) if($group==$ugroup):?>
		<option selected><?=$group?></option>
	<?else:?>
		<option><?=$group?></option>
	<?endif?>
		<script>
		(function(select){
			select.onchange=function(){
				location.pathname = "/welcome/"+select.value;
			}
		})(document.currentScript.parentNode)
		</script>
	</select>
	<select class="tool" autocomplete="off">
	<?php
	$users = $mySQL->group_rows("SELECT Login FROM gb_staff WHERE `Group` LIKE '".$ugroup."'")['Login'];
	foreach($users as $user) if($user==SUBPAGE):?>
		<option selected><?=$user?></option>
	<?else:?>
		<option><?=$user?></option>
	<?endif?>
		<script>
		(function(select){
			select.onchange=function(){
				var path = location.pathname.split(/\//);
				path[2] = path[2] || "<?=USER_GROUP?>";
				path[3] = select.value;
				location.pathname = path.join("/");
			}
		})(document.currentScript.parentNode)
		</script>
		<?if(!SUBPAGE):?>
		<option disabled selected>Pleace select user</option>
		<?endif?>
	</select>
</div>