<?php

require_once("core/index.php");
$config = new config();

define("PROTOCOL", getProtocol());

$mySQL = new dBase($config->host, $config->{"user name"}, $config->password, $config->{"db name"});
$mySQL->set_charset("utf8");

$page = [];
define("DEFAULT_LANG", $config->language);	// Set default language
$params = preg_split("/\//", mb_strtolower(urldecode($_GET['params']), "utf-8"), -1, PREG_SPLIT_NO_EMPTY);

if(in_array($params[0], $config->languageset)){
	define("USER_LANG", $params[0]);
	setcookie("lang", USER_LANG, time()+604800, "/");
	define("LANG_MASK", language_mask($config->languageset));
	
	if(empty($params[1])){
		include_once("modules/".$config->{"default module"}."/index.php");
	}elseif(is_numeric($params[1])){
		$page = $mySQL->single_row("SELECT `content` FROM `gb_".$config->subdomain."` INNER JOIN `gb_pages` USING(`PageID`) LEFT JOIN `gb_blogfeed` USING(`PageID`) WHERE `ID` = '".$params[0]."' AND `created` < ".time()." ORDER BY (POW(2,`language`-1) & ".LANG_MASK.") LIMIT 1");
		if(empty($page)){
			include_once("modules/404/index.php");
		}else $page = gzdecode($page['content']);
	}else{
		include_once("modules/404/index.php");
	}
	print($page);
}else{
	if(empty($_COOKIE['lang'])){
		define("USER_LANG", check_browser_language($config->languageset, DEFAULT_LANG));
		setcookie("lang", USER_LANG, time()+604800, "/");
	}else define("USER_LANG", $_COOKIE['lang']);
	define("LANG_MASK", language_mask($config->languageset));
	
	if(empty($params[0])){
		include_once("modules/".$config->{"default module"}."/index.php");
	}elseif(is_numeric($params[0])){
		$page = $mySQL->single_row("SELECT `content` FROM `gb_".$config->subdomain."` INNER JOIN `gb_pages` USING(`PageID`) LEFT JOIN `gb_blogfeed` USING(`PageID`) WHERE `ID` = '".$params[0]."' AND `created` < ".time()." ORDER BY (POW(2,`language`-1) & ".LANG_MASK.") LIMIT 1");
		if(empty($page)){
			include_once("modules/404/index.php");
		}else $page = gzdecode($page['content']);
	}else{
		include_once("modules/404/index.php");
	}
	print($page);
}


/*********************/

$mySQL->close();

/*********************/

function language_mask($set){
	$mask = 0;
	foreach($set as $key=>$val){
		if($val!=USER_LANG) $mask |= pow(2, $key);
	}
	return $mask;
}
function select_module($param, &$page){
	global $config, $mySQL;
	if(is_numeric($param)){
		$page = $mySQL->single_row("SELECT * FROM `gb_pages` INNER JOIN `gb_blogfeed` USING(`PageID`) LEFT JOIN `gb_".$config->subdomain."` USING(`PageID`) WHERE `ID` = '".$param."' AND `created` < ".time()." ORDER BY (POW(2,`language`-1) & ".LANG_MASK.") LIMIT 1");
		if(empty($page)){
			header('HTTP/1.0 404 Not Found');
			$page = $mySQL->single_row("SELECT * FROM `gb_pages` INNER JOIN `gb_sitemap` USING(`PageID`) LEFT JOIN `gb_static` USING(`PageID`) WHERE `title` LIKE 'static' LIMIT 1");
			return "modules/".$page['module']."/index.php";
		}elseif(empty($page['content'])){
			$page['content'] = reset($mySQL->single_row("SELECT `content` FROM `gb_blogcontent` WHERE `PageID` = '".$page['PageID']."' LIMIT 1"));
			return "modules/render/index.php";
		}else{
			$page = gzdecode($page['content']);
			return false;
		}
	}else{
		$page = $mySQL->single_row("SELECT * FROM `gb_pages` INNER JOIN `gb_sitemap` USING(`PageID`) LEFT JOIN `gb_static` USING(`PageID`) WHERE `title` LIKE '".$param."' AND `created` < ".time()." ORDER BY (POW(2,`language`-1) & ".LANG_MASK.") LIMIT 1");
		if(empty($page)){
			$page = $mySQL->single_row("SELECT * FROM `cbb_archive` INNER JOIN `cbb_post` USING(`id`) WHERE `title` LIKE '".$param."' LIMIT 1");
			if(empty($page)){
				header('HTTP/1.0 404 Not Found');
				$page = $mySQL->single_row("SELECT * FROM `gb_pages` INNER JOIN `gb_sitemap` USING(`PageID`) LEFT JOIN `gb_static` USING(`PageID`) WHERE `title` LIKE 'static' LIMIT 1");
			}else{
				return "modules/archive/index.php";
			}
		}
		if(file_exists("modules/".$page['module']."/index.php")){
			return "modules/".$page['module']."/index.php";
		}else return "modules/".$config->{"default module"}."/index.php";
	}
	return false;
}

?>