<?php

$post = $mySQL->single_row("
SELECT PageID,gb_blogcontent.content AS content FROM gb_amp
CROSS JOIN gb_blogcontent USING(PageID)
WHERE PageID < ".SUBPAGE."
ORDER BY PageID DESC
LIMIT 1");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>AMP - <?=$post['PageID']?></title>
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
	</head>
	<body>
<article><?=gzdecode($post['content'])?></article>
<script>
window.onload=function(){
	var content = doc.querySelector("article");
	setTimeout(function(){
		content.querySelectorAll("img").forEach(function(img, i){
			let amp = doc.create("amp-img","",{
				src:img.src,
				width:img.naturalWidth,
				height:img.naturalHeight,
				layout:"responsive"
			})
			img.parentNode.replaceChild(amp, img);
		});
		content.querySelectorAll("video").forEach(function(vid, i){
			let amp = doc.create("amp-video","",{
				src:vid.src,
				width:vid.videoWidth,
				height:vid.videoHeight,
				layout:"responsive"
			})
			vid.parentNode.replaceChild(amp, vid);
		});
		content.querySelectorAll(".video>iframe").forEach(function(ifm){
			let amp = doc.create("amp-youtube","",{
				"data-videoid":ifm.src.split(/\//).pop(),
				"width":"480",
				"height":"270",
				"layout":"responsive"
			})
			ifm.parentNode.replaceChild(amp, ifm);
		});
		content.querySelectorAll("*[contenteditable]").forEach(function(obj){
			obj.removeAttribute("contenteditable");
		});
		content.querySelectorAll(".adsense").forEach(function(obj){
			obj.parentNode.removeChild(obj);
		});


		XHR.push({
			"Content-Type":"text/html",
			"addressee":"/blogger/actions/save-amp/<?=$post['PageID']?>",
			"body":content.innerHTML,
			"onsuccess":function(response){
				setTimeout(function(){
				//	window.location.pathname = "blogger/amp/<?=$post['PageID']?>"
				},2000);
			}
		});
	},4000);
}
</script>
</body>
</html>