<link rel="stylesheet" type="text/css" href="/modules/manual/tpl/manual.css"/>
<div class="caption">Manual</div>
<div class="content">
<?php

$doc = $mySQL->single_row("SELECT `content` FROM `gb_documentation` WHERE `title` LIKE '".SECTION."' ORDER BY (POW(2,`language`-1) & ".LANG_MASK.") LIMIT 1");
print($doc['content']);

?>
</div>