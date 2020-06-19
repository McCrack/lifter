<?php 
$CommunityID = SUBPAGE ? $mySQL->single_row("SELECT CommunityID FROM gb_staff WHERE Login LIKE '".SUBPAGE."' LIMIT 1")['CommunityID'] : COMMUNITY_ID;
?>
<style>
.slot.waste{
	opacity:1.0;
}
.slot.waste>.card{
	color:white;
	background-color:#802;
}
.slot.new{
	filter:invert(1);
}
.slot.new:hover{
	filter:invert(0);
}
</style>
<main id="columns">
<section>
	<div class="caption">
		<small data-translate="textContent">stories</small>
		<div class="toolbar right">
			<span class="tool" title="create card" data-translate="title" onclick="new Box('{}', 'taskboard/createbox')">&#xe901;</span>
		</div>
	</div>
	<div class="section" data-type="story">
		<?$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE type LIKE 'story' AND status NOT LIKE 'done' ORDER BY status DESC, SortedID DESC LIMIT 20");
		foreach($tasks as $task):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot <?=$task['status']?>" draggable="true">
			<div class="card"> 
				<img src="<?=$task['image']?>" alt="No Image">
				<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
				<div class="task <?=$task['type']?>"><?=$task['task']?></div>
				<div class="performer"><?=$task['Name']?></div>
				<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			</div>
		</div>
		<?endforeach?>
	</div>
</section>
<section  style="background-color:#FFF">
	<div class="caption"><small data-translate="textContent">videos</small></div>
	<div class="section" data-type="video">
		<?$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE type LIKE 'video' AND status NOT LIKE 'done' ORDER BY status DESC, SortedID DESC");
		foreach($tasks as $task):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot <?=$task['status']?>" draggable="true">
			<div class="card"> 
				<img src="<?=$task['image']?>" alt="No Image">
				<div class="source"><?=$task['source']?></div>
				<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
				<div class="task <?=$task['type']?>"><?=$task['task']?></div>
				<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			</div>
		</div>
		<?endforeach?>
	</div>
</section>
<section  style="background-color:#FFF">
	<div class="caption"><small data-translate="textContent">images</small></div>
	<div class="section" data-type="images">
		<?$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE type LIKE 'images' AND status NOT LIKE 'done' ORDER BY status DESC,SortedID DESC");
		foreach($tasks as $task):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot" draggable="true">
			<div class="card"> 
				<img src="<?=$task['image']?>" alt="No Image">
				<div class="source"><?=$task['source']?></div>
				<div class="header"><a href="<?=$task['link']?>" target="_black"><?=(empty($task['header']) ? $task['link'] : $task['header'])?></a></div>
				<div class="task <?=$task['type']?>"><?=$task['task']?></div>
				<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			</div>
		</div>
		<?endforeach?>
	</div>
</section>
<section>
	<div class="caption"><small data-translate="textContent">articles</small></div>
	<div class="section" data-type="article">
		<?$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE type LIKE 'article' AND status NOT LIKE 'done' ORDER BY status DESC, SortedID DESC");
		foreach($tasks as $task):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot <?=$task['status']?>" draggable="true">
			<div class="card"> 
				<img src="<?=$task['image']?>" alt="No Image">
				<div class="source"><?=$task['source']?></div>
				<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
				<div class="task <?=$task['type']?>"><?=$task['task']?></div>
				<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			</div>
		</div>
		<?endforeach?>
	</div>
</section>
<section  style="background-color:#FFF">
	<div class="caption"><small data-translate="textContent">repost</small></div>
	<div class="section" data-type="images">
		<?$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE type LIKE 'repost' AND status NOT LIKE 'done' ORDER BY status DESC,SortedID DESC");
		foreach($tasks as $task):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot" draggable="true">
			<div class="card"> 
				<img src="<?=$task['image']?>" alt="No Image">
				<div class="source"><?=$task['source']?></div>
				<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
				<div class="task <?=$task['type']?>"><?=$task['task']?></div>
				<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			</div>
		</div>
		<?endforeach?>
	</div>
</section>

<script>
(function(desk){
	desk.querySelectorAll("section>div.section").forEach(function(section){
		section.ondrop=function(event){
			event.preventDefault();
			var task = document.getElementById( event.dataTransfer.getData("text") );
			var cards = section.querySelectorAll("div.slot");
			task.dataset.sorted = (cards.length) ? (cards[cards.length-1].dataset.sorted - 1) : 0;
			event.currentTarget.appendChild(task);
			
			changeType(section.dataset.type, task.dataset.id, task.dataset.sorted);
		}
		section.ondragover=function(event){
			event.preventDefault();
			
		}
		section.ondragleave=function(event){
			event.preventDefault();
		}
		section.querySelectorAll(".slot").forEach(function(card){
			card.ondblclick=function(event){
				event.preventDefault();
				new Box('{}', "taskboard/taskbox/"+card.dataset.id);
			}
			card.ondragstart=function(event){
				card.classList.toggle("active", true);
				event.dataTransfer.effectAllowed="move";
				event.dataTransfer.setData("text", event.currentTarget.id);
			}
			card.ondragend=function(event){
				card.classList.toggle("active", false);
			}
			card.ondragover=function(event){
				event.preventDefault();
				card.style.padding = "40px 0";
				card.parentNode.ondrop = null;
			}
			card.ondragleave=function(event){
				event.preventDefault();
				card.removeAttribute("style");
				card.parentNode.ondrop=function(event){
					event.preventDefault();
					var column = event.currentTarget;
					var task = document.getElementById( event.dataTransfer.getData("text") );
					var cards = column.querySelectorAll("div.slot");
					task.dataset.sorted = (cards.length) ? (cards[cards.length-1].dataset.sorted - 1) : 0;
					column.appendChild(task);

					changeType(column.dataset.type, task.dataset.id, task.dataset.sorted);
				}
			}
			card.ondrop=function(event){
				event.preventDefault();
				card.removeAttribute("style");
				var task = document.getElementById( event.dataTransfer.getData("text") );
				if( (event.clientY-card.offsetTop) < (card.offsetHeight/2) ){
					task.dataset.sorted = Number(card.dataset.sorted) + 1;
					card.insertAdjacentElement("beforeBegin", task);
				}else{
					task.dataset.sorted = Number(card.dataset.sorted) - 1;
					card.insertAdjacentElement("afterEnd", task);
				}
				
				changeType(card.parentNode.dataset.type, task.dataset.id, task.dataset.sorted);
			}
		});
	});
	function changeType(type, CardID, SortedID){
		
		XHR.push({
			addressee:"/taskboard/actions/change-type",
			body:JSON.encode({
				CardID:CardID,
				type:type,
				SortedID:SortedID
			}),
			onsuccess:function(response){

			}
		});
	}
})(document.currentScript.parentNode)
</script>
</main>