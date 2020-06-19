<?php

$list = $mySQL->query("SELECT Email FROM gb_community WHERE Email LIKE '%@%'");

$fp = fopen("tpls/community.txt", "w");

foreach($list as $itm){
	//file_put_contents("tpls/community.txt", $itm['Email'].PHP_EOL,  FILE_APPEND );
	$test = fwrite($fp, $itm['Email'].",".PHP_EOL);
	if($test){
		print $itm['Email']."<br>";
	}else print "Ошибка при записи в файл<br>";
}

fclose($fp);

?>