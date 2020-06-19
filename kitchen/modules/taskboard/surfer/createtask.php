<div class="taskbox box-body">
	<div class="task" contenteditable="true"> </div>
	<input name="link" placeholder="link" data-translate="placeholder">
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
		<select name="tasktype">
			<?foreach(["article"=>"📄","repost"=>"💬","story"=>"⚡","video"=>"🎦","images"=>"🌅"] as $type=>$icon):?>
			<option value="<?=$type?>"><?=$icon?> <?=$type?></option>
			<?endforeach?>
		</select>
	</div>
	<input name="imglink" value="<?=$task['image']?>" placeholder="Image">
	<div class="snippet">
		<input name="source" placeholder="source" data-translate="placeholder">
		<img class="src-img" src="/images/NIA.jpg">
		<textarea name="header" placeholder="header" data-translate="placeholder"></textarea>
	</div>
	<div class="questions">
		<br>
		<?php
		$questions = JSON::load("patterns/json/questions.json");
		foreach($questions as $question=>$answers):?>
		<fieldset><legend><?=$question?></legend>
			<?foreach($answers as $answer):?>
			<label class="answer"><input name="<?=$answer['variable']?>" value="<?=$answer['value']?>" <?=$answer['default']?> type="radio"> <span><?=$answer['answer']?></span></label>
			<?endforeach?>		
		</fieldset>
		<br>
		<?endforeach?>
	</div>
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
	form.onsubmit=function(event){
		event.preventDefault();


		var q = (
			parseInt(form.a.value)+
			parseInt(form.b.value)+
			parseInt(form.c.value)
		).toString();

		var w = (
			parseInt(form.s.value)+
			parseInt(form.x.value)
		) * parseFloat(form.z.value);

		var rank = q + Math.round(w).toString() + form.l.value;
		
		XHR.push({
			addressee:"/taskboard/actions/create",
			body:JSON.encode({
				"CommunityID":form.performer.value,
				"task":form.querySelector(".task").innerHTML.trim().replace(/"/g,"″").replace(/'/g, ""),
				"type":form.tasktype.value,
				"link":form.link.value,
				"source":form.source.value.trim().replace(/"/g,"″").replace(/'/g, ""),
				"image":form.imglink.value,
				"header":form.header.value.trim().replace(/"/g,"″").replace(/'/g, "") || form.link.value,
				"value":rank
			}),
			onsuccess:function(response){
				form.reset();
				document.querySelector("#grid").insertAdjacentHTML("afterbegin", response);
				//document.querySelector("#grid").innerHTML += response;
			}
		});
	}
})(document.currentScript.parentNode);
</script>