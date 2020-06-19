<main id="grid">
<?php
$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE CommunityID IS NULL AND type LIKE 'article' ORDER BY value DESC");
foreach($tasks as $task):?>
<div id="task-<?=$task['CardID']?>" class="slot" data-id="<?=$task['CardID']?>">
	<div class="card"> 
		<img src="<?=$task['image']?>" alt="No Image">
		<div class="source"><?=$task['source']?></div>
		<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
		<div class="task"><?=$task['task']?></div>
		<div class="date"><?=date("d M, H:i", $task['created'])?></div>
	</div>
</div>
<?endforeach?>
<script>
(function(desk){
	desk.querySelectorAll(".slot").forEach(function(card){
		card.ondblclick=function(event){
			event.preventDefault();
			new Box('{}', "taskboard/taskbox/"+card.dataset.id);
		}
	});
})(document.currentScript.parentNode)
</script>
<div class="slot" onclick="new Box('{}', 'taskboard/createbox')">
	<div class="card"> 
		<span class="tool">&#xe901;</span>
		<div class="task" data-translate="textContent" align="center">create card</div>
		<br>
	</div>
</div>
</main>