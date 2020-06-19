<?php 
$CommunityID = SUBPAGE ? $mySQL->single_row("SELECT CommunityID FROM gb_staff WHERE Login LIKE '".SUBPAGE."' LIMIT 1")['CommunityID'] : COMMUNITY_ID;
?>
<main id="columns">
<section  style="background-color:#FFF">
	<div class="caption">
		<div class="toolbar right">
			<span class="tool" title="create card" data-translate="title" onclick="new Box('{}', 'taskboard/createbox')">&#xe901;</span>
		</div>
		<small data-translate="textContent">stream</small>
	</div>
	<div id="stream" class="section" data-perfomer="<?=$CommunityID?>">
		<?php
		$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE CommunityID IS NULL AND type LIKE 'article' ORDER BY value DESC,SortedID DESC LIMIT 10");
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
		<?endforeach;
		$tasks = $mySQL->query("SELECT * FROM gb_stream LEFT JOIN gb_community USING(CommunityID) WHERE CommunityID=".$CommunityID." ORDER BY SortedID DESC");
		foreach($tasks as $task):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot" draggable="true">
			<div class="card"> 
				<img src="<?=$task['image']?>" alt="No Image">
				<div class="source"><?=$task['source']?></div>
				<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
				<div class="task <?=$task['type']?>"><?=$task['task']?></div>
				<div class="performer"><?=$task['Name']?></div>
				<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			</div>
		</div>
		<?endforeach?>
	</div>
</section>
<?php
$tasks = $mySQL->query("SELECT * FROM gb_stream WHERE CommunityID IS NOT NULL AND (status NOT LIKE 'waste' AND status NOT LIKE 'done') ORDER BY SortedID DESC");
$authors = $mySQL->query("SELECT * FROM gb_staff CROSS JOIN gb_community USING(CommunityID) WHERE `Group` LIKE 'author' ORDER BY UserID");
foreach($authors as $author):?>
<section>
	<div class="caption"><small><?=$author['Name']?></small></div>
	<div class="section" data-perfomer="<?=$author['CommunityID']?>">
		<?foreach($tasks as $task) if($task['CommunityID']==$author['CommunityID']):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot" draggable="true">
			<div class="card"> 
				<img src="<?=$task['image']?>" alt="No Image">
				<div class="source"><?=$task['source']?></div>
				<div class="header"><a href="<?=$task['link']?>" target="_black"><?=$task['header']?></a></div>
				<div class="task <?=$task['type']?>"><?=$task['task']?></div>
				<div class="date"><?=date("d M, H:i", $task['created'])?></div>
			</div>
		</div>
		<?endif;
		$wastes = $mySQL->query("SELECT * FROM gb_stream WHERE CommunityID=".$author['CommunityID']." AND (status LIKE 'waste' OR status LIKE 'done') ORDER BY modified DESC LIMIT 10");
		foreach($wastes as $task):?>
		<div id="task-<?=$task['CardID']?>" data-id="<?=$task['CardID']?>" data-sorted="<?=$task['SortedID']?>" class="slot waste">
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
<?endforeach?>
<script>
(function(desk){
	desk.querySelectorAll("section>div.section").forEach(function(section){
		section.ondrop=function(event){
			event.preventDefault();
			var task = document.getElementById( event.dataTransfer.getData("text") );
			var cards = section.querySelectorAll("div.slot");
			task.dataset.sorted = (cards.length) ? (cards[cards.length-1].dataset.sorted - 1) : 0;
			event.currentTarget.appendChild(task);
			
			changePerformer(section.dataset.perfomer, task.dataset.id, task.dataset.sorted);
		}
		section.ondragover=function(event){
			event.preventDefault();
			
		}
		section.ondragleave=function(event){
			event.preventDefault();
		}

		section.init=function(){
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

						changePerformer(column.dataset.perfomer, task.dataset.id, task.dataset.sorted);
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
				
					changePerformer(card.parentNode.dataset.perfomer, task.dataset.id, task.dataset.sorted);
				}
			});
		}
		section.init();
	});
	function changePerformer(PerformerID, CardID, SortedID){
		XHR.push({
			addressee:"/taskboard/actions/change-performer",
			body:JSON.encode({
				CardID:CardID,
				CommunityID:PerformerID,
				SortedID:SortedID
			}),
		});
	}
})(document.currentScript.parentNode)
</script>
</main>