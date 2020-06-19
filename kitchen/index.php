<?php

require_once("core/index.php");

$config = new config();

$mask = 0;
foreach($config->languageset as $key=>$val){
	if($val!=$config->language){
		$mask |= pow(2, $key);
	}
}
define("LANG_MASK", $mask);

$mySQL = new dBase($config->host, $config->{"user name"}, $config->password, $config->{"db name"});
$mySQL->set_charset("utf8");

$mySQL->login();

$params=preg_split("/\//", $_GET['params'], -1, PREG_SPLIT_NO_EMPTY);

define("DEFAULT_LANG", $config->{"language"});
define("USER_LANG", DEFAULT_LANG);

define("SECTION", empty($params[0]) ? $config->{"default module"} : mb_strtolower(urldecode($params[0]), "utf-8"));
define("PAGE", empty($params[1]) ? 0 : mb_strtolower(urldecode($params[1]), "utf-8"));
define("SUBPAGE", empty($params[2]) ? 0 : mb_strtolower(urldecode($params[2]), "utf-8"));
define("PARAMETER", empty($params[3]) ? false : mb_strtolower(urldecode($params[3]), "utf-8"));
define("SUBPARAMETER", empty($params[4]) ? false : mb_strtolower(urldecode($params[4]), "utf-8"));

define("BASE_FOLDER", $config->{"base folder"});
define("BASE_DOMAIN", $config->{BASE_FOLDER});

define("PROTOCOL", getProtocol());

$brancher = new Brancher();

if(is_dir("modules/".SECTION)){
	if(file_exists("modules/".SECTION."/".PAGE.".php")){
		include_once("modules/".SECTION."/".PAGE.".php");
	}else include_once("modules/".SECTION."/page.php");
}else include_once("modules/".$config->{"default module"}."/page.php");

$mySQL->close();

?>