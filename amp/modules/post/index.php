<?php

$post = $mySQL->single_row("
SELECT * FROM `gb_blogfeed` 
CROSS JOIN `gb_amp` USING(`PageID`)
CROSS JOIN `gb_pages` USING(`PageID`) 
WHERE 
	`ID` = '".ID."' AND 
	`created` < ".time()." 
ORDER BY (POW(2,`language`-1) & ".LANG_MASK.") 
LIMIT 1");
if(empty($post)){
	$mySQL->close();
	header('HTTP/1.0 404 Not Found');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Page Not Found</title>
		<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
	</head>
	<body><br><br><br><h1 align="center">Page Not Found</h1></body>
</html>
<?php
	exit();
}else $post['content'] = gzdecode($post['content']);

$canonical = PROTOCOL."://".HOST."/".$post['language']."/".$post['ID'];
$author = $mySQL->single_row("SELECT * FROM `gb_staff` LEFT JOIN `gb_community` USING(`CommunityID`) WHERE `UserID`=".$post['UserID']." LIMIT 1");
$preview = getimagesize($post['preview']);

/* Template ********************************************************/

?>
<!doctype html>
<html amp lang="ru_RU">
<head>
	<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-51627207-1', 'auto');
    ga('send', 'pageview');
    </script>

	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
	
	<title><?=$post['header']?></title>
	<meta name="description" content="<?=$page['subheader']?>">

	<link rel="canonical" href="<?=$canonical?>">
	
	<style amp-boilerplate="">body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>
	<style amp-custom><?=$fonts?>body{margin:0;background-color:#F3F3F3;}a{color:#058;text-decoration:none;}#loft{font-size:0;padding:5px 0;background-color:#222;}#loft amp-img{border:1px solid #777;}#author{color:#278;padding:2px;position:relative;font:italic 15px tinos;}#author::before{content:"";left:0;bottom:0;width:42%;height:2px;display:block;position:absolute;background-color:#578;}time{color:#345;float:right;font:bold 15px/1.5 calibri;}article{color:#222;padding:20px;margin:0 auto;max-width:640px;font:18px "open sans";box-sizing:border-box;background-color:white;box-shadow:0 0 5px -3px rgba(0,0,0,.5);}article>header>h1{font:bold 30px ubuntu;}article>header>h1:first-line{font-size:110%;}article>header>h1:first-letter{color:#F25;}article h2{font:bold 22px "open sans";}article p{margin:20px 0;}figure{margin:0 -20px;}figcaption,figcaption>a{color:#AAA;padding:1px 6px;font:15px ubuntu;}blockquote{color:#555;margin:0;padding:4vh 10px;font:italic 18px tinos;background:url(/themes/2018/images/quotes.jpg) left top no-repeat;}ul,ol{padding-left:4vw;}li{margin:8px 0;font-size:16px;}</style>

	<script async src="https://cdn.ampproject.org/v0.js"></script>
    <script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>
    <script async custom-element="amp-iframe" src="https://cdn.ampproject.org/v0/amp-iframe-0.1.js"></script>
    <script async custom-element="amp-youtube" src="https://cdn.ampproject.org/v0/amp-youtube-0.1.js"></script>
</head>
<body>
	<div id="loft" align="center">
      <a href="/"><amp-img src="/logo-64x64.png" width="30" height="30"></amp-img></a>
    </div>
    <article>
      <header>
        <h1><?=$page['header']?></h1>
        <h2><?=$page['subheader']?></h2>
        <figure>
          <amp-img src="<?=$page['preview']?>" width="640" height="336" layout="responsive"></amp-img>
        </figure>
      </header>
      <?=$post['content']?>
      <br>
      <footer>
        <time><?=date("d M, Y")?></time>
        <span id="author"><?=$page['Name']?></span>
      </footer>
    </article>
	<!-- End Footer -->
</body>
</html>