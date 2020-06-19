<?php

	$brancher->auth("taskboard") or die(include_once("modules/auth/alert.html"));

	$task = $mySQL->single_row("SELECT * FROM gb_stream WHERE CardID=".SUBPAGE." LIMIT 1");

	$handle = "b".time();
?>
<form id="<?=$handle?>" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="max-width:600px;background:#10B090;color:white">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		<sup>Task Card</sup>
	</div>
	<div class="taskbox box-body">
		<input type="hidden" name="cardid" value="<?=$task['CardID']?>">
		<div class="task" contenteditable="true"><?=$task['task']?></div>
		<input name="link" placeholder="link" data-translate="placeholder" value="<?=$task['link']?>">
		<div class="select">
			<select name="status">
				<?foreach(["new","in work","waste","done"] as $status) if($status==$task['status']):?>
					<option value="<?=$task['status']?>" selected><?=$task['status']?></option>
				<?else:?>
					<option value="<?=$status?>"><?=$status?></option>
				<?endif?>
			</select>
		</div>
		<div class="select">
			<select name="performer">
				<option value="NULL">Не розподілена</option>
				<?$staff = $mySQL->query("SELECT * FROM gb_staff CROSS JOIN gb_community USING(CommunityID) WHERE `Group` LIKE 'author'");
				foreach($staff as $author):?>
					<option value="<?=$author['CommunityID']?>" <?if($author['CommunityID']==$task['CommunityID']):?>selected<?endif?> ><?=$author['Name']?></option>
				<?endforeach?>
			</select>
		</div>
		<div class="select">
			<select name="tasktype" value="<?=$task['type']?>">
				<?foreach(["article"=>"📄","repost"=>"💬","story"=>"⚡","video"=>"🎦","images"=>"🌅"] as $type=>$icon) if($type==$task['type']):?>
					<option value="<?=$type?>" selected><?=$icon?> <?=$type?></option>
				<?else:?>
					<option value="<?=$type?>"><?=$icon?> <?=$type?></option>
				<?endif?>
			</select>
		</div>
		<input name="imglink" value="<?=$task['image']?>" placeholder="Image">
		<div class="snippet">
			<input name="source" placeholder="source" data-translate="placeholder" value="<?=$task['source']?>">
			<img class="src-img" src="<?=$task['image']?>">
			<input name="val" placeholder="Value" value="<?=$task['value']?>" <?if(USER_GROUP!="admin"):?>hidden<?endif?> size="4">
			<textarea name="header" placeholder="header" data-translate="placeholder"><?=$task['header']?></textarea>
		</div>
	</div>
	<div class="box-footer">
		<button class="left" name="remove" type="reset" data-translate="textContent">remove</button>
		<button type="submit" data-translate="textContent">save</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
	<script>
	(function(form){
		var timeout;
		form.link.oninput=function(event){
			XHR.push({
				"addressee":"/taskboard/actions/parse",
				"body":form.link.value,
				"onsuccess":function(response){
					try{
						response = JSON.parse(response);
						form.source.value = response['og:site_name'];
						/*
						form.header.value = response['og:title'];
						form.imglink.value = 
						form.querySelector(".src-img").src = response['og:image'];
						*/
					}catch(e){alert(response)}
				}
			});
		}
		form.imglink.oninput=function(event){
			clearTimeout(timeout);
			timeout = setTimeout(function(){
				form.querySelector(".src-img").src = form.imglink.value;
			},800);
		}
		form.remove.onclick=function(event){
			XHR.push({
				addressee:"/taskboard/actions/remove/<?=$task['CardID']?>",
				onsuccess:function(response){
					if(parseInt(response)){
						var task = document.querySelector("#task-<?=$task['CardID']?>");
						task.parentNode.removeChild(task);
					}
				}
			});
		}
		form.onsubmit=function(event){
			event.preventDefault();
			XHR.push({
				addressee:"/taskboard/actions/save/"+form.cardid.value,
				body:JSON.encode({
					"CommunityID":form.performer.value,
					"task":form.querySelector(".task").innerHTML.trim().replace(/"/g,"″"),
					"type":form.tasktype.value,
					"status":form.status.value,
					"link":form.link.value,
					"source":form.source.value.trim().replace(/"/g,"″"),
					"image":form.imglink.value,
					"value":form.val.value,
					"header":form.header.value.trim().replace(/"/g,"″")
				}),
				onsuccess:function(response){
					form.reset();
					var stream = document.querySelector("#task-<?=SUBPAGE?>");
					stream.innerHTML += response;
					if(stream.init) stream.init();
				}
			});
		}
	})(document.currentScript.parentNode);
	</script>
</form>