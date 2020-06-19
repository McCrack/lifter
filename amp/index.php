<?php

if(preg_match("/(android|phone|ipad|tablet|blackberry|bb10|symbian|series|samsung|webos|mobile|opera m|htc|fennec|windowsphone|wp7|wp8)/i",$_SERVER['HTTP_USER_AGENT'])){
	define("MOBILE", true);
	define("DESKTOP", false);
	define("DEVICE", "mobile");
}else{
	define("MOBILE", false);
	define("DESKTOP", true);
	define("DEVICE", "desktop");
}

if(preg_match("/[A-Z]/", $_GET['params'])) header("Location: ".mb_strtolower($_GET['params'], "utf-8"));

require_once("core/index.php");

$config = new config("config.init");

$host = explode(".", $_SERVER['HTTP_HOST']);
define("HOST", implode(".", array_slice($host, 1)));
define("PROTOCOL", getProtocol());
define("DEFAULT_LANG", $config->language);

$params = preg_split("/\//", urldecode($_GET['params']), -1, PREG_SPLIT_NO_EMPTY);

$mask = 0;
if(in_array($params[0], $config->languageset)){
	define("USER_LANG", $params[0]);
	$params = array_slice($params, 1);
}else define("USER_LANG", DEFAULT_LANG);
foreach($config->languageset as $key=>$val){
	if($val!=USER_LANG) $mask |= pow(2, $key);
}
define("LANG_MASK", $mask);

if(empty($params[0])){
	exit;
}else define("ID", $params[0]);

//setcookie("AMP_EXP", "amp-story", time()+604800, "/");

/***********************************/

if(is_numeric(ID)){
	require_once("core/db.php");	

	$page = $mySQL->getRow("
	SELECT ID,PageID,header,subheader,preview,language,created,modified,gb_blogfeed.tid as tid,Name,content
	FROM gb_blogfeed
	CROSS JOIN gb_pages USING(PageID)
	CROSS JOIN gb_amp USING(PageID)
	LEFT JOIN gb_staff USING(UserID) CROSS JOIN gb_community USING(CommunityID)
	WHERE ID = {int} AND created<{int} LIMIT 1
	", ID, time());

	if(empty($page)){
		$page = $mySQL->getRow("
		SELECT header,language FROM gb_blogfeed
		WHERE ID = {int}
		LIMIT 1", ID, time());
		if(!empty($page)){
			header("Location: https://m.lifter.com.ua/".$page['language']."/".ID."/".translite($page['header']), true, 301);
		}else header('HTTP/1.0 404 Not Found');
	}

	$title = $page['header']." - ".$config->{"site name"};

	$created = date("Y-m-d\TH:i:s", $page['created']);
	$modified = date("Y-m-d\TH:i:s", $page['modified']);
	$author = $mySQL->single_row("SELECT `Name` FROM gb_staff LEFT JOIN gb_community USING(CommunityID) WHERE UserID=".$page['UserID'])['Name'];
	$logo = getimagesize("logo.png");

	$canonical = "https://".HOST."/".$page['language']."/".$page['ID'];

	$fonts = "";
	foreach(scandir("fonts") as $file){
		$font = explode(".", $file);
		if(is_file("fonts/".$file)){
			$fName = str_replace("-", " ", $font[0]);
			switch($font[1]){
				case "otf":
				case "ttf":
					$fonts.="
					@font-face{
						font-family:'".$fName."';
						src: local('".$fName."'), url(/fonts/".$file.") format('truetype');
					}";
				break;
				case "woff":
					$fonts.="
					@font-face{
						font-family:'".$fName."';
						src: url('/fonts/".$file."') format('woff');
					}";
				break;
				default:break;
			}
		}
	}

	define("THEME", MOBILE ? $config->{"theme"} : $config->{"theme"});
	include_once("themes/".THEME."/amp.html");
}

?>