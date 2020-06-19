<?php

/* Metadata collection ***************************************************/
	
	$canonical = PROTOCOL."://".$config->domain."/".$page['language']."/".$page['ID'];
	
/* Template ********************************************************/

	ob_start(); 

?>
	
<!doctype html>
<html lang="ru_RU" prefix="op: http://media.facebook.com/op#">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta property="op:markup_version" content="v1.0">
		<meta property="fb:article_style" content="default">
		<link rel="canonical" href="<?php print($canonical); ?>">
	</head>
	<body>
		<article>
			<figure class='op-tracker'>
				<iframe>
					<script type='text/javascript'>
					(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
					ga('create', 'UA-51627207-1', 'auto');
					ga('send', 'pageview');
					</script>
				</iframe>
			</figure>
			<header>
				<h1>404</h1>
				<h2>Page not found</h2>
			</header>
		</article>
	</body>
</html>

<?php

	$page = ob_get_contents();
	ob_end_clean();
	
?>