<?php

require_once("core/index.php");

$config = new config();

define("DEFAULT_LANG", $config->{"language"});
define("USER_LANG", DEFAULT_LANG);

$params = preg_split("/\//", mb_strtolower(urldecode($_GET['params']), "utf-8"), -1, PREG_SPLIT_NO_EMPTY);

define("MODULE", empty($params[0]) ? false : mb_strtolower(urldecode($params[0]), "utf-8"));
define("SUBMODULE", empty($params[1]) ? false : mb_strtolower(urldecode($params[1]), "utf-8"));
define("ARG_1", empty($params[2]) ? false : mb_strtolower(urldecode($params[2]), "utf-8"));
define("ARG_2", empty($params[3]) ? false : mb_strtolower(urldecode($params[3]), "utf-8"));
define("ARG_3", empty($params[4]) ? false : mb_strtolower(urldecode($params[4]), "utf-8"));

if(is_dir("ajax/".MODULE)){
	$module_path = SUBMODULE ? "ajax/".MODULE."/".SUBMODULE.".php" : "ajax/".MODULE."/index.php";
	if(file_exists($module_path)){
		include_once($module_path);
	}
}

?>