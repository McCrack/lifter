<?php
	$handle = "b".time();
?>

<form id="<?php print($handle); ?>" onsubmit="return false" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="max-width:540px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		<span data-translate="textContent">properties</span> <?php print('"'.SUBPAGE.'"'); ?>
	</div>
	<div class="box-body">
		<table width="100%" cellpadding="4" cellspacing="0" rules="cols" bordercolor="white" style="border:1px solid #999">
			<thead>
				<tr align="center" bgcolor="#38B" style="color:#FFF">
					<td width="200px" data-translate="textContent">attribute</td>
					<td data-translate="textContent">value</td>
				</tr>
			</thead>
			<tbody>
				<tr bgcolor="#FFFFFF"><td>id</td><td contenteditable="true"></td></tr>
				<tr bgcolor="#EAEAEA"><td>class</td><td contenteditable="true"></td></tr>
				<tr bgcolor="#FFFFFF"><td>style</td><td contenteditable="true"></td></tr>
				<tr bgcolor="#EAEAEA"><td>contenteditable</td><td contenteditable="true"></td></tr>
<?php

$color=15395562;
switch(SUBPAGE){
	case "table": $list=array("align","background","bgcolor","border","cellpadding","cellspacing","cols","frame","height","rules","summary","width"); break;
	case "td": $list=array("abbr","align","valign","colspan","rowspan","width","height","nowrap","headers","axis","background","bgcolor","bordercolor","char","charoff","scope"); break;
	case "th": $list=array("abbr","align","valign","colspan","rowspan","width","height","nowrap","headers","axis","background","bgcolor","bordercolor","char","charoff","scope"); break;
	case "thead": $list=array("align","char","charoff","bgcolor","valign"); break;
	case "tbody": $list=array("align","char","charoff","bgcolor","valign"); break;
	case "tfoot": $list=array("align","char","charoff","bgcolor","valign"); break;
	case "tr": $list=array("align","char","charoff","bgcolor","valign"); break;
	case "a": $list=array("accesskey","coords","download","href","hreflang","name","rel","rev","shape","tabindex","target","title","type"); break;
	case "ol": $list=array("type","reversed","start"); break;
	case "ul": $list=array("type"); break;
	case "li": $list=array("type","value"); break;
	case "div": $list=array("align"); break;
	case "p": $list=array("align"); break;
	case "h1": $list=array("align"); break;
	case "h2": $list=array("align"); break;
	case "h3": $list=array("align"); break;
	case "h4": $list=array("align"); break;
	case "h5": $list=array("align"); break;
	case "h6": $list=array("align"); break;
	case "canvas": $list=array("height","width"); break;
	case "caption": $list=array("align","valign"); break;
	case "q": $list=array("cite"); break;
	case "img": $list=array("align","alt","border","height","hspace","ismap","longdesc","lowsrc","src","vspace","width","usemap"); break;
	case "audio": $list=array("autoplay","controls","loop","preload","src"); break;
	case "video": $list=array("autoplay","controls","height","loop","poster","preload","src","width"); break;
	case "form": $list=array("accept-charset","action","autocomplete","enctype","method","name","novalidate","target"); break;
	case "select": $list=array("accesskey","autofocus","disabled","form","multiple","name","required","size","tabindex"); break;
	case "textarea": $list=array("accesskey","autofocus","cols","disabled","form","maxlength","name","placeholder","readonly","required","rows","tabindex","wrap"); break;
	case "input": $list=array("accept","accesskey","align","alt","autocomplete","autofocus","border","checked","disabled","form","formaction","formenctype","formmethod","formnovalidate","formtarget","list","max","maxlength","min","multiple","name","pattern","placeholder","readonly","required","size","src","step","tabindex","type","value"); break;
	case "button": $list=array("accesskey","autofocus","disabled","form","formaction","formenctype","formmethod","formnovalidate","formtarget","name","type","value"); break;
	case "hr": $list=array("align","color","noshade","size","width"); break;
	case "iframe": $list=array("align","allowtransparency","frameborder","height","hspace","marginheight","marginwidth","name","sandbox","scrolling","seamless","src","srcdoc","vspace","width"); break;
	case "object": $list=array("align","archive","classid","code","codebase","codetype","data","height","hspace","tabindex","type","vspace","width"); break;
	default:break;
}
foreach($list as $key) $rows.="<tr bgcolor='#".dechex($color^=1381653)."'><td>".$key."</td><td contenteditable='true'></td></tr>";
print($rows);

?>
			</tbody>
		</table>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">apply</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>