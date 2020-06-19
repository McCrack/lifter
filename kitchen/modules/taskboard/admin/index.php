<main id="grid">
<?php
$tasks = $mySQL->query("SELECT * FROM gb_stream LEFT JOIN gb_community USING(CommunityID) WHERE status NOT LIKE 'waste' ORDER BY CommunityID,value DESC");
foreach($tasks as $task):?>
<div id="task-<?=$task['CardID']?>" class="slot <?=(empty($task['CommunityID'])?'':'disabled')?>" data-id="<?=$task['CardID']?>">
	<div class="card"> 
		<img src="<?=$task['image']?>" alt="No Image">
		<div class="source"><?=$task['source']?></div>
		<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
		<div class="task <?=$task['type']?>"><?=$task['task']?></div>
		<div class="performer"><?=$task['Name']?></div>
		<div class="date"><?=date("d M, H:i", $task['created'])?></div>
		<div class="value"><?=$task['value']?></div>
	</div>
</div>
<?endforeach?>
<div class="slot" onclick="new Box('{}', 'taskboard/createbox')">
	<div class="card"> 
		<span class="tool">&#xe901;</span>
		<div class="task" data-translate="textContent" align="center">create card</div>
		<br>
	</div>
</div>
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
</main>
<section id="waste">
	<div class="caption">
		<span data-translate="textContent">waste</span>
		<div class="toolbar right">
			<label class="tool" title="Clear waste posts" onclick="clearAllWaste()">&#xe9ac;</label>
		</div>
	</div>
	<?php
	$tasks = $mySQL->query("SELECT * FROM gb_stream LEFT JOIN gb_community USING(CommunityID) WHERE status LIKE 'waste' OR status LIKE 'done' ORDER BY created DESC");
	foreach($tasks as $task):?>
	<div id="task-<?=$task['CardID']?>" class="slot" data-id="<?=$task['CardID']?>">
		<div class="card"> 
			<img src="<?=$task['image']?>" alt="No Image">
			<div class="source"><?=$task['source']?></div>
			<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
			<div class="task <?=$task['type']?>"><?=$task['task']?></div>
			<div class="performer"><?=$task['Name']?></div>
			<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			<div class="value"><?=$task['value']?></div>
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
	var clearAllWaste = function(){
		XHR.push({
			addressee:"/taskboard/actions/clear-all-waste",
			onsuccess:function(response){
				if(parseInt(response)){
					document.querySelectorAll("section#waste>div.slot").forEach(function(card){
						card.parentNode.removeChild(card);
					});
				}
			}
		});
	}
	</script>
</section>