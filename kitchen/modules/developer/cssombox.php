<?php
	
	$brancher->auth("developer") or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();
?>

<form id="<?php print($handle); ?>" onsubmit="return createTheme(this.theme.value)" class="box" onreset="boxList[this.id].drop()" onmousedown="boxList.focus(this)" style="max-width:580px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2))"></span>
		CSSOM
	</div>
	<div class="box-body">
		<table rules="rows" width="100%" cellpadding="5" cellspacing="0" bordercolor="#CCC">
			<colgroup><col width="30"><col width="180"><col><col width="30"></colgroup>
			<tbody>
			</tbody>
		</table>
		<datalist id="rules-set">
			<option>background</option>
			<option>background-attachment</option>
			<option>background-color</option>
			<option>background-image</option>
			<option>background-position</option>
			<option>background-repeat</option>
			<option>background-size</option>
			<option>border</option>
			<option>border-radius</option>
			<option>border-width</option>
			<option>bottom</option>
			<option>box-shadow</option>
			<option>box-sizing</option>
			<option>color</option>
			<option>content</option>
			<option>cursor</option>
			<option>display</option>
			<option>float</option>
			<option>font</option>
			<option>font-family</option>
			<option>font-size</option>
			<option>height</option>
			<option>left</option>
			<option>letter-spacing</option>
			<option>line-height</option>
			<option>margin</option>
			<option>max-height</option>
			<option>max-width</option>
			<option>min-height</option>
			<option>min-width</option>
			<option>object-fit</option>
			<option>opacity</option>
			<option>outline</option>
			<option>overflow</option>
			<option>padding</option>
			<option>position</option>
			<option>right</option>
			<option>text-align</option>
			<option>text-decoration</option>
			<option>text-indent</option>
			<option>text-shadow</option>
			<option>text-transform</option>
			<option>top</option>
			<option>transition</option>
			<option>vertical-align</option>
			<option>visibility</option>
			<option>white-space</option>
			<option>width</option>
			<option>z-index</option>
		</datalist>
		<datalist id="values-set">
			<option>absolute</option>
			<option>auto</option>
			<option>black</option>
			<option>block</option>
			<option>bottom</option>
			<option>capitalize</option>
			<option>default</option>
			<option>fixed</option>
			<option>grey</option>
			<option>inherit</option>
			<option>inline</option>
			<option>inline-block</option>
			<option>hidden</option>
			<option>left</option>
			<option>lowercase</option>
			<option>normal</option>
			<option>none</option>
			<option>middle</option>
			<option>no-repeat</option>
			<option>pointer</option>
			<option>relative</option>
			<option>right</option>
			<option>scroll</option>
			<option>silver</option>
			<option>static</option>
			<option>sticky</option>
			<option>top</option>
			<option>transparent</option>
			<option>visible</option>
			<option>underline</option>
			<option>uppercase</option>
			<option>white</option>
		</datalist>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">apply</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>