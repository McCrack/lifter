<!DOCTYPE html>
<html>
	<head id="head">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Lifter.gb</title>
		<link rel="stylesheet" media="all" type="text/css" href="/modules/auth/tpl/auth.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>		
		<script src="/modules/auth/tpl/auth.js"></script>
	</head>
	<body class="auth">
		<div id="preview">
			<h1>Lifter</h1>
		</div>
		<br>
		<form onsubmit="return auth(this)">
			<span>Login:</span>
			<input name="login" autofocus="autofocus" value="" style="width:210px">
			<span>Password:</span>
			<input name="passwd" type="password" value="">
			<button type="submit">&#xe929;</button>
		</form>
	</body>
</html>