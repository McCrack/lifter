<?php 
$CommunityID = SUBPAGE ? $mySQL->single_row("SELECT CommunityID FROM gb_staff WHERE Login LIKE '".SUBPAGE."' LIMIT 1")['CommunityID'] : COMMUNITY_ID;
?>
<main id="columns">
<section  style="background-color:#FFF">
	<div class="caption">
		<small data-translate="textContent">new</small>
	</div>
	<div class="section" data-status="new">
		<?php
		$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE CommunityID=".$CommunityID." AND status LIKE 'new' ORDER BY SortedID DESC");
		foreach($tasks as $task):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot" draggable="true">
			<div class="card"> 
				<img src="<?=$task['image']?>" alt="No Image">
				<div class="source"><?=$task['source']?></div>
				<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
				<div class="task"><?=$task['task']?></div>
				<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			</div>
		</div>
		<?endforeach?>
	</div>
</section>
<section>
	<div class="caption"><small data-translate="textContent">in work</small></div>
	<div class="section" data-status="in work">
		<?
		$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE CommunityID=".$CommunityID." AND status LIKE 'in work' ORDER BY SortedID DESC");
		foreach($tasks as $task):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot" draggable="true">
			<div class="card"> 
				<img src="<?=$task['image']?>" alt="No Image">
				<div class="source"><?=$task['source']?></div>
				<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
				<div class="task"><?=$task['task']?></div>
				<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			</div>
		</div>
		<?endforeach?>
	</div>
</section>
<section>
	<div class="caption">
		<small data-translate="textContent">waste</small>
	</div>
	<div class="section" data-status="waste" style="background-color:#222">
		<?php
		$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE CommunityID=".$CommunityID." AND (status LIKE 'waste' OR status LIKE 'done') ORDER BY modified DESC LIMIT 20");
		foreach($tasks as $task):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot" draggable="true">
			<div class="card"> 
				<img src="<?=$task['image']?>" alt="No Image">
				<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
				<div class="task"><?=$task['task']?></div>
				<div class="performer"><?=$task['Name']?></div>
				<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			</div>
		</div>
		<?endforeach?>
	</div>
</section>
<script>
(function(desk){
	desk.querySelectorAll("section>div.section").forEach(function(section){
		init(section);
	});
})(document.currentScript.parentNode)

function init(section){
	section.ondrop=function(event){
		event.preventDefault();
		var task = document.getElementById( event.dataTransfer.getData("text") );
		var cards = section.querySelectorAll("div.slot");
		task.dataset.sorted = (cards.length) ? (cards[cards.length-1].dataset.sorted - 1) : 0;
		event.currentTarget.appendChild(task);
		changeStatus(section.dataset.status, task.dataset.id, task.dataset.sorted);
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

				changeStatus(column.dataset.status, task.dataset.id, task.dataset.sorted);
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
			if(card.parentNode.dataset.status){
				changeStatus(card.parentNode.dataset.status, task.dataset.id, task.dataset.sorted);
			}else dropPerformer(task.dataset.id);
		}
	});
}
function changeStatus(status, CardID, SortedID){
	XHR.push({
		addressee:"/taskboard/actions/change-status",
		body:JSON.encode({
			CardID:CardID,
			status:status,
			CommunityID:<?=$CommunityID?>,
			SortedID:SortedID
		}),
		onsuccess:function(response){

		}
	});
}
function dropPerformer(CardID){
	XHR.push({
		addressee:"/taskboard/actions/drop-performer/"+CardID,
		onsuccess:function(response){

		}
	});
}
</script>
</main>