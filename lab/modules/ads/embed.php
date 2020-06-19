<link rel="stylesheet" type="text/css" href="/modules/ads/tpl/ads.css">
<form id="ads">
	<div class="caption" data-translate="textContent">ads</div>
	<div id="ads-list">
<?php
	
	$ads = new DOMDocument;
	$ads->load("modules/ads/adsfeed.xml");
	print($ads->saveHTML());
	
?>
	</div>
	<div>
		<textarea id="message-field" name="message" required placeholder="..."></textarea><button type="submit">&#xf1d8;</button>
	</div>
	<script src="/modules/ads/tpl/ads.js"></script>
</form>