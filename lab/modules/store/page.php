<?php

	$brancher->auth() or die(include_once("modules/auth/page.php"));
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.store</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/store/tpl/store.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?php print($config->themes); ?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/store/tpl/store.js"></script>
		<script src="/modules/editor/tpl/editor.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=store" async charset="utf-8"></script>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		
		<script>
			window.onbeforeunload = reauth;
		</script>
	</head>
	<body class="<?php print($standby[SECTION]['bodymode']); ?>">
		<aside id="leftbar">
			<a href="/" id="goolybeep">	</a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" title="modules" data-translate="title" data-tab="modules-list">&#xe5c3;</span>
						<span class="tool" title="categories" data-translate="title" data-tab="categories">&#xe9bc;</span>
						<span class="tool" title="filters" data-translate="title" data-tab="filters">&#xea52;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?php print($brancher->tree($brancher->register)); ?>
					</div>
				</div>
				<form class="tab left" id="filters" autocomplete="off">
					<div class="caption" data-translate="textContent">filters</div>
					<fieldset onchange="satNativeFilter(event.target.value)">
<?php
	
	$where = [];
	$subfilter = (INT)SUBPAGE;
	$dumpingChecked = $discountChecked = "";
	if($subfilter&1){
		$where[] = "DiscountID IS NOT NULL";
		$discountChecked = "checked";
	}
	if($subfilter&2){
		$where[] = "dumping > 0";
		$dumpingChecked = "checked";
	}
	print("
	<label><input type='checkbox' ".$discountChecked." value='1'> <span data-translate='textContent'>discount</span></label>
	<label><input type='checkbox' ".$dumpingChecked." value='2'> <span data-translate='textContent'>dumping</span></label>
	");	
?>
					</fieldset>
<?php

	define("CATEGORY", PAGE ? PAGE : "store");
	$category = $mySQL->single_row("SELECT `PageID`,`filterset`,`optionset` FROM `gb_sitemap` CROSS JOIN `gb_static` USING(`PageID`) WHERE `title` LIKE '".CATEGORY."' LIMIT 1");

	$filterset = JSON::parse($category['filterset']);
	
	$IDs = [];
	$section = explode("-", (INT)PARAMETER);
	foreach($section as $key=>$val){
		for($i=32; ($val>0),$i--;){
			if($val & pow(2, $i)) $IDs[] = (32 * $key) + ($i + 1);
		}
	}
		
	$filters="";
	foreach($filterset as $name=>$set){
		$filters .= "<fieldset onchange='satFilter(event.target.value)'><legend>".$name."</legend>";
		foreach($set as $key=>$vals){
			$filters .= "<label><input type='checkbox' ".(in_array($vals['id'], $IDs) ? "checked" : "")." value='".$vals['id']."'> ".$vals['value']."</label>";
		}
		$filters .= "</fieldset>";
	}
	print($filters);

?>
				</form>
				<div class="tab left" id="categories">
					<div class="caption" data-translate="textContent">categories</div>
<?php
	
	$categories = $mySQL->tree("SELECT `parent`,`title`,`PageID` FROM `gb_sitemap`", "title", "parent");
	print( categoryTree($categories, "store") );
	
?>
				</div>
			</div>
		</aside>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="create model" onclick="createModel()">&#xf15b;</span>
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('store')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">
<?php

	$limit = 24;
	$page = SUBPARAMETER ? SUBPARAMETER : 1;
	$cat_list = getNestedCategoriesID($categories, CATEGORY);
	$cat_list[] = $category['PageID'];
	$where[] = "CategoryID IN (".implode(", ", $cat_list).")";
	
	if(PARAMETER){
		foreach($section as $key=>$val){
			if($val>0){
				$where[] = "(`".$key."` & ".$val.")";
			}
		}
		$models = $mySQL->query("
		SELECT SQL_CALC_FOUND_ROWS * FROM `gb_tagination`
		CROSS JOIN `gb_models` USING(`tid`)
		LEFT JOIN `gb_items` USING(`ItemID`)
		WHERE ".implode(" AND ", $where)."
		ORDER BY gb_models.PageID DESC
		LIMIT ".(($page-1)*$limit).", ".$limit);
		
		$path = "store/".PAGE."/".$subfilter."/".PARAMETER;
	}else{
		$models = $mySQL->query("
		SELECT SQL_CALC_FOUND_ROWS * FROM `gb_models`
		WHERE ".implode(" AND ", $where)."
		ORDER BY PageID DESC
		LIMIT ".(($page-1)*$limit).", ".$limit);
		
		$path = "store/".PAGE."/".$subfilter."/0";
	}
	
	$stikers = "";
	foreach($models as $model){
		$stikers .= "
		<a class='sticker' href='/store/showcase/".$model['PageID']."'>
			<img src='".$model['preview']."'>
			<div class='name'>".$model['name']."</div>
			<div class='options'>
				<span>".$model['status']."</span>
			</div>
		</a>";
	}
	print($stikers);
	$count = $mySQL->single_row("SELECT FOUND_ROWS()");
	$count = reset($count);
	$pagination = "";
	$total = ceil($count/$limit);	// Total pages
	
	if($total > 1){
		if($page > 4){
			$j = $page - 2;
			$pagination="<a href='/".$path."/1'>1</a> ... ";
		}else $j = 1;
		for(; $j < $page; $j++) $pagination.="<a href='/".$path."/".$j."'>".$j."</a>";					
		$pagination.="<a class='selected'>".$j."</a>";
		if($j<$total){
			$pagination.="<a href='/".$path."/".(++$j)."'>".(++$j)."</a>";
			if(($total - $j) > 1){
				$pagination.=" ... <a href='/".$path."/".$total."'>".$total."</a>";
			}elseif($j < $total){
				$pagination.="<a href='/".$path."/".$total."'>".$total."</a>";
			}
		}
	}
	print("<div id='pagination' align='right'>".$pagination."</div>");
?>
		</div>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span data-translate="title" title="filters" class="tool" data-tab="filterset">&#xe908;</span>
					<span data-translate="title" title="options" class="tool" data-tab="optionset">&#xf05e;</span>
					<span data-translate="title" title="manual" class="tool" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<div id="filterset" class="tab">
				<div class="caption">
					<span data-translate="textContent">filters</span>
					<div class="toolbar">
						<span title="save" data-translate="title" class="tool" onclick="saveFilterset()">&#xe962;</span>
					</div>
					<div class="toolbar right">
						<span title="add set" data-translate="title" class="tool" onclick="addFilterSet()">&#xe901;</span>
						<span title="Pattern" class="tool" onclick="showPattern(filtersToJSON(doc.querySelector('#filterset')), 'jsontofilters')">&#xe8ab;</span>
					</div>
				</div>
				<form>
<?php
	$color=16777215;
	foreach($filterset as $name=>$set){
?>
				<table class="set" rules="cols" width="100%" cellpadding="4" cellspacing="0" bordercolor="#CCC">
					<colgroup><col width="30"><col><col width="30"></colgroup>
					<thead>
						<tr class="panel">
							<th title="Raise set" onclick="raiseSet(this.parent(3))"><div class="raise">&#xe045;</div></th>
							<td align="center" contenteditable="true"><?php print($name); ?></td>
							<th title="Remove set" onclick="removeSet(this.parent(3))"><span class="tool">&#xf05f;</span></th>
						</tr>
					</thead>
					<tbody>
<?php
		$rows = "";
		foreach($set as $key=>$val){
			$rows .= "
			<tr bgcolor='#".dechex($color^=1052688)."'>
				<th title='Add value' onclick='addFilterValue(this.parentNode)' bgcolor='white'><span class='tool'>&#xe908;</span></th>
				<td onfocus='focusCell(this)' contenteditable='true'>".$val['value']."</td>
				<th bgcolor='white' title='Delete row' onclick='deleteRow(this)'><span class='tool red'>&#xe907;</span></th>
			</tr>";
		}
		print($rows);
?>
					</tbody>
				</table>
<?php
	}
	$options = "";
	$filters = $mySQL->query("SELECT `value` FROM `gb_filters` LIMIT 100");
	foreach($filters as $value){
		$options .= "<option>".$value['value']."</option>";
	}
	print("<datalist id='filters-list'>".$options."</datalist>");
?>
				</form>
			</div>
			<div id="optionset" class="tab">
				<div class="caption">
					<span data-translate="textContent">properties</span>
					<div class="toolbar">
						<span title="save" data-translate="title" class="tool" onclick="saveOptions()">&#xe962;</span>
					</div>
					<div class="toolbar right">
						<span title="Pattern" class="tool" onclick="showPattern(patternWithoutValidate(doc.querySelector('#optionset')), 'JsonToOptions')">&#xe8ab;</span>
					</div>
				</div>
				<table rules="cols" width="100%" cellpadding="5" cellspacing="0" bordercolor="#CCC">
					<colgroup><col width="30"><col><col><col width="30"></colgroup>
					<thead>
						<tr style="color:#EEE;background-color:#444"><th></th><th>Name</th><th>Value</th><th></th></tr>
					</thead>
					<tbody>
<?php

	$rows = "";
	$color=16777215;
	$optionset = JSON::parse($category['optionset']);
	if(empty($optionset)){
		$rows .= "
		<tr bgcolor='#EFEFEF'>
		<th bgcolor='white'><span title='add row' data-translate='title' class='tool' onclick='addRow(this)'>&#xe908;</span></th>
		<td contenteditable='true'> </td>
		<td contenteditable='true'> </td>
		<th bgcolor='white'><span title='delete row' data-translate='title' class='tool red' onclick='deleteRow(this)'>&#xe907;</span></th>
		</tr>";
	}else foreach($optionset as $key=>$val){
		$rows .= "
		<tr bgcolor='#".dechex($color^=1052688)."'>
		<th bgcolor='white'><span title='add row' data-translate='title' class='tool' onclick='addRow(this)'>&#xe908;</span></th>
		<td contenteditable='true'>".$key."</td>
		<td contenteditable='true'>".$val."</td>
		<th bgcolor='white'><span title='delete row' data-translate='title' class='tool red' onclick='deleteRow(this)'>&#xe907;</span></th>
		</tr>";
	}
	print($rows);

?>
					</tbody>
				</table>
			</div>
			<div id="manual" class="tab">
<?php
	
	include_once("modules/manual/embed.php");

?>
			</div>
		</div>
	</body>
</html>


<?php


function getNestedCategoriesID(&$items, $offset){
	$result = [];
	if(is_array($items[$offset])){
		foreach($items[$offset] as $key=>$val){
			$result[] = $val['PageID'];
			$result = array_merge($result, getNestedCategoriesID($items, $key));
		}
	}
	return $result;
}
	
function categoryTree(&$items, $offset){
	if(is_array($items[$offset])){
		$result.="<div class='root'>";
		foreach($items[$offset] as $key=>$val){
			if($val['title']===CATEGORY){
				$result.="<a href='/store/".$val['title']."' class='tree-root-item'>".$val['title']."</a>";
			}else $result.="<a href='/store/".$val['title']."' class='tree-item'>".$val['title']."</a>";
			$result .= categoryTree($items, $key);
		}
		$result.="</div>";
		return $result;
	}
}

?>