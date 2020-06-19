<?php

/* BRANCHER ********************************************************************************************************************************************************/

class Brancher{
	public $register=array(), $map=array(), $current;
	public function __construct($path="brancher.json"){
		$this->register = JSON::load($path);
	}
	public function tree(&$branch, $isNoProtec=false){
		$tree = "";
		foreach($branch as $key=>$item){
			$groups = preg_split("/,\s*/", $item['options']['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
			if(in_array(USER_GROUP, $groups)){
				if($isNoProtec || ($item['options']['status']['value']==="enabled")){
					$tree.="<label data-href='/".$key."' data-mode='".$item['options']['mode']['value']."' data-translate='textContent' class='tree-root-item'>".$key."</label>";
					if(count($item['branch'])>0){
						$tree.="<div class='root'>";
						$tree.=$this->tree($item['branch'], $isNoProtec);
						$tree.="</div>";
					}
				}
			}
		}
		return $tree;
	}
	public function auth(){
		$map = $this->createMap();
		$module = &$this->getModule($this->register, $map);
		$groups = preg_split("/,\s*/", $module['options']['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
		return in_array(USER_GROUP, $groups);
		
	}
	public function &getModule(&$array, &$map){
		$key = current($map);
		$subarray = &$array[$key];
		$next = next($map);
		if($next && !empty($subarray['branch'][$next])){
			return $this->getModule($subarray['branch'], $map);
		}return $subarray;
	}
	public function createMap($module=SECTION){
		$this->map = array();
		$this->bypassBranch($this->register, $module);
		return $this->map;
	}
	public function buildBranch($map, $branch=array()){
		$key = $map[0];
		if(array_shift($map)){
			if(count($map)){
				$branch[$key]['branch'] = $this->buildBranch($map, $branch[$key]);
			}else{
				$branch[$key] = $this->getModule($this->register, $this->map);
			}
			return $this->current = $branch;
		}
	}
	private function bypassBranch($branch, $module){
		foreach($branch as $key=>$item){
			if($key===$module){
				$this->map[] = $key;
				return true;
			}elseif(count($item['branch'])>0){
				if($this->bypassBranch($item['branch'], $module)){
					array_unshift($this->map, $key);
					return true;
				}
			}
		}
		return false;
	}
	public function dropBranch(&$array, $map){
		$key = current($map);
		if(next($map)){
			$this->dropBranch($array[$key]['branch'], $map);
		}else{
			$res = $array[$key];
			unset($array[$key]);
			return $res;
		}
	}
}

/* CONFIG ********************************************************************************************************************************************************/

class config{
	public $list=array();
	public function __construct($path="config.init"){
		$this->list=JSON::load($path);
		if(empty($this->list)){ exit("<b>ERROR:</b> config file not found."); }		
		foreach($this->list as $section=>$items){
			foreach($items as $key=>$val){
				$this->{$key}=$val['value'];
			}
		}
		$this->{"languageset"} = $this->list['general']['language']['valid'];
	}
	public function __get($key){
		return $this->list[$key]?$this->list[$key]:$key;
	}
}

/* DATABASE ****************************************************************************************************************************************************/

class dBase extends mysqli{
    public $count, $levels=array(), $result=array();
	
	public function select($table, $field="*", $where="1", $order=""){
		if(is_array($field))	$field=implode(",", $field);
		if(is_array($where))	$where=implode(" AND ", $where);
		if(!empty($order))		$order="ORDER BY `".$order."`";
		$this->real_query("SELECT ".$field." FROM `".$table."` WHERE ".$where." ".$order);
		if($result=$this->store_result()){
			$this->count=$result->num_rows;
			$this->result=array();
			while($row = $result->fetch_assoc()){
				$this->result[]=$row;
			}
			$result->free();
			return $this->result;
		}else return false;
	}
	public function update($table, $fields, $where="1"){
		$field=[];
		foreach($fields as $key=>$val){
			$field[]="`".$key."`='".$val."'";
		}		
		if(is_array($where)) $where=implode(" AND ", $where);
		$this->multi_query("UPDATE ".$table." SET ".implode(",", $field)." WHERE ".$where);
		return $this->affected_rows;
	}
	public function insert($table, $fields){
		$field=[];
		foreach($fields as $key=>$values) $field[]="`".$key."`='".$values."'";
		$this->query("INSERT INTO ".$table." SET ".implode(",", $field));
		return $this->insert_id;
	}
	public function query($query){
		$this->multi_query($query);
		if($result=$this->store_result()){
			$this->count=$result->num_rows;
			$this->result=array();
			while($row = $result->fetch_assoc()){
				$this->result[]=$row;
			}
			$result->free();
			return $this->result;
		}else return $this->affected_rows;
	}
	public function single_row($query){
		$this->real_query($query);
		if($result=$this->store_result()){
			$row = $result->fetch_assoc();
			$result->close();
			$this->count=1;
			return $row;
		}else return $this->insert_id;
	}
	public function group_rows($query){
        $this->multi_query($query);
        if($result=$this->store_result()){
			$this->count=$result->num_rows;
			$this->result=array();
			while($row = $result->fetch_assoc()){
				foreach($row as $key=>$val){
					$this->result[$key][]=$val;
				}
			}			
            $result->free();
			return $this->result;
        }else return false;
    }
	public function tree($query, $id="id", $pid="pid"){
		$this->real_query($query);
		if($result=$this->store_result()){
			$this->count=$result->num_rows;
			$this->result=array();
			while($row = $result->fetch_assoc()){
				foreach($row as $key=>$val){
					$this->result[$row[$pid]][$row[$id]][$key]=$val;
				}
			}
			$result->free();
			return $this->result;
		}else return false;
    }
	public function toTree($list, $id, $parent){
		$result = [];
		foreach($list as $row){
			foreach($row as $key=>$val){
				$result[$row[$parent]][$row[$id]][$key] = $val;
			}
		}
		return $result;
	}
	public function login(){
		$this->real_query("SELECT * FROM `gb_staff` LEFT JOIN `gb_community` USING(`CommunityID`) WHERE '".$_COOKIE['finger']."' = MD5(CONCAT(`Login`, `Passwd`, '".$_COOKIE['key']."')) LIMIT 1");
		if($result=$this->store_result()){
			$row = $result->fetch_assoc();
			define("USER_ID", $row['UserID']);
			define("USER_LOGIN", $row['Login']);
			define("USER_NAME", $row['Name']);
			define("USER_GROUP", $row['Group']);
			define("USER_EMAIL", $row['Email']);
			define("COMMUNITY_ID", $row['CommunityID']);
			$result->free();
		}
		setcookie("key", rand(), 0, "/");
	}
	public function auth($access){
		return in_array($access, $this->levels);
	}
}

/* JSON ********************************************************************************************************************************************************/

class JSON{
	public static function save($path, &$array){
		if(is_string($array)){
			$json=$array;
		}elseif(is_array($array)||is_object($array)){
			$json=self::traversing($array);
		}		
		return file_put_contents($path, $json);
	}
	public static function load($path, $assoc=true){
		$str=file_get_contents($path);
		return json_decode($str, $assoc);
	}
	public static function parse($str, $assoc=true){ return json_decode($str, $assoc); }
	public static function encode($str){ return json_encode($str, JSON_UNESCAPED_UNICODE); }
	public static function stringify($array){ return self::traversing($array); }
	private static function traversing($value){
		if(is_int($value)){
			return (string)$value;   
		}elseif(is_string($value)){
			$value=str_replace(array('\\', '/', '"', "\r", "\n", "\b", "\f", "\t"), array('\\\\', '\/', '\"', '\r', '\n', '\b', '\f', '\t'), $value);
			$convmap=array(0x80, 0xFFFF, 0, 0xFFFF);
			$result="";
			for($i=mb_strlen($value); $i--;){
				$mb_char = mb_substr($value, $i, 1);
				if(mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match)){
					$result = sprintf("\\u%04x", $match[1]) . $result;
				}else $result = $mb_char . $result;
			}
			return '"' . $result . '"';   
		}elseif(is_float($value)){ return str_replace(",", ".", $value);         
		}elseif(is_null($value)){ return 'null';
		}elseif(is_bool($value)){ return $value ? 'true' : 'false';
		}elseif(is_array($value)){
			$keys=array_keys($value);
			$with_keys=array_keys($keys)!==$keys;
		}elseif(is_object($value)){
			$with_keys=true;
		}else return '';
		$result=array();
		if($with_keys){
			foreach($value as $key=>$v){
				$result[]=self::traversing((string)$key).':'.self::traversing($v);    
			}
			return '{'.implode(',', $result).'}';     
		}else{
			foreach ($value as $key=>$val) {
				$result[]=self::traversing($val);    
			}
			return '['.implode(',', $result).']';
		}
	}
}

/* FS ********************************************************************************************************************************/

function buildFolderTree($root, &$map){
	$files = $folders = [];
	$current = array_shift($map);
	foreach(scandir($root) as $file){
		$fullpath = $root."/".$file;
		if(is_dir($fullpath) && $file!="." && $file!=".."){
			$folders[$file] = ["path"=>$fullpath,"type"=>"folder","content"=>[]];
			if($current===$file){
				$folders[$file]['type'] = "openfolder";
				$folders[$file]['content'] = buildFolderTree($fullpath, $map, $showfiles);
			}
		}elseif(is_file($fullpath)) $files[$file] = ["path"=>$fullpath, "type"=>mime_content_type($fullpath)];
	}
	return $folders+$files;
}
function copyFolder($source, $dest){
	foreach(scandir($source) as $file){
		if(($file!="." && $file!="..") && is_dir($source."/".$file)){
			mkpath($dest."/".$file);
			copyFolder($source."/".$file, $dest."/".$file);
		}elseif(is_file($source."/".$file)){
			copy($source."/".$file, $dest."/".$file);
		}
	}
}
function mkpath($path){
	$step=array();
	foreach(explode("/", $path) as $item){
		$step[]=$item;
		@mkdir(implode("/",$step));
	}
}
function deletedir($dir){
    foreach(scandir($dir) as $file){
        if($file!="." && $file!=".."){
            if(is_dir($dir."/".$file)){ deletedir($dir."/".$file); }
            elseif(is_file($dir."/".$file)){ unlink($dir."/".$file); }
        }
    }
    return @rmdir($dir);
}
function folderToZip($path, &$zipFile, $local){
	if($zipFile){
		foreach(scandir($path) as $file){
			$fullpath = $path."/".$file;
			if(($file!="." && $file!="..") && is_dir($fullpath)){
				folderToZip($fullpath, $zipFile, $local."/".$file);
			}elseif(is_file($fullpath)) $zipFile->addFile($fullpath, $local."/".$file);
		}
	}else return false;
}

/* EMAIL ********************************************************************************************************************************************************/

function sendmail($address, $message, $mode="plain", $theme="Message from the site", $sender){
	if(empty($sender)){
		$sender="site@".$_SERVER['HTTP_HOST'];
	}
	$headers="MIME-Version: 1.0\r\n";
	$headers.="Content-type: text/".$mode."; charset=utf8\r\n";
	if($mode=="plain"){
		$message=wordwrap($message, 70);
	}
	$headers.="From: ".$sender."\r\n";
	$resp = mail($address, "=?utf-8?B?".base64_encode($theme)."?=", $message, $headers);
	return $resp;
}

/* OTHER **************************************************************************************************************************/

function translite($str="", $dot=true){
	$dictionary=[
	"а"=>"a",	"б"=>"b",	"в"=>"v",	"г"=>"g",	"ґ"=>"g",	"д"=>"d",
	"е"=>"e",	"є"=>"ye",	"ж"=>"zh",	"з"=>"z",	"и"=>"i",	"і"=>"i",
	"ї"=>"yi",	"й"=>"y",	"к"=>"k",	"л"=>"l",	"м"=>"m",	"н"=>"n",
	"о"=>"o",	"п"=>"p",	"р"=>"r",	"с"=>"s",	"т"=>"t",	"у"=>"u",
	"ф"=>"f",	"х"=>"h",	"ы"=>"y",	"э"=>"e",	"ё"=>"e",	"ц"=>"ts",
	"ч"=>"ch",	"ш"=>"sh",	"щ"=>"shch","ю"=>"yu",	"я"=>"ya",	" "=>"-"];

	$str = mb_strtolower( trim($str), "UTF-8");
	if(preg_match("/і|ї|ґ|є/",$str)){
		$dictionary['г'] = "h";
		$dictionary['и'] = "y";
		$dictionary['х'] = "kh";
	}
	$str = strtr($str, $dictionary);
	if($dot){
		$str = preg_replace("/(?(?=\W)[^-.]|^$)/", "", $str);
	}else $str = preg_replace("/(?(?=\W)[^-]|^$)/", "", $str);
	return preg_replace("/-{2,}/","-",$str);
}

function getProtocol(){
	if(isset($_SERVER['HTTPS'])){
		if(("on" == strtolower($_SERVER['HTTPS'])) || ("1" == $_SERVER['HTTPS'])){
			$protocol = "https";
		}
	}elseif(isset($_SERVER['SERVER_PORT']) && ("443" == $_SERVER['SERVER_PORT'])){
		$protocol = "https";
	}else $protocol = "http";
	return $protocol;
}

function keywords($keywords, $tid){
	if(count($keywords)>0){
	// Получаем список ранее добавленых в материал тематических тегов
		global $mySQL;
		$tagination = $mySQL->single_row("SELECT * FROM `gb_tagination` WHERE `tid` = ".$tid." LIMIT 1");
		$IDs = [];
		$cnt = count($tagination)-1;
		for($j=0; $j<$cnt; $j++){ for($i=32; $i--;){
			if($tagination[$j] & pow(2, $i)){ $IDs[] = (32 * $j) + ($i + 1); }
		}}
		$lastkeys = $mySQL->group_rows("SELECT `tag` FROM `gb_keywords` WHERE `id` IN (".implode(",",$IDs).")");
		if(empty($lastkeys)) $lastkeys = array("tag"=>[]);
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Вычитаем из списка тегов ранее добавленные в материал, чтобы не накручивать счетчик использования
		$keys = array_diff($keywords, $lastkeys['tag']); 
	// Получаем список уже существующих тегов и подымаем им счетчик использования
		$exist = $mySQL->group_rows("SELECT `id`,`tag` FROM `gb_keywords` WHERE `tag` IN ('".implode("','",$keys)."')");
		$mySQL->query("UPDATE `gb_keywords` SET `rating`=`rating`+1 WHERE `id` IN (".implode(",", $exist['id']).")");
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Вычитаем из списка тегов те, которые уже есть в базе, и добавляем в базу оставшиеся
		if(empty($exist['tag'])){
			$new = $keys;
		}else $new = array_diff($keys, $exist['tag']);
		if(!empty($new)){
			$mySQL->query("INSERT INTO `gb_keywords` (`tag`) VALUES ('".implode("'),('", $new)."')");
		}
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Получаем ID всех тематических тегов материала
		$IDs = $mySQL->group_rows("SELECT `id` FROM `gb_keywords` WHERE `tag` IN ('".implode("','",$keywords)."')");
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Вычисляем битовую маску
		$mask = $section = [];
		foreach($IDs['id'] as $id) $section[($id/32)^0] |= pow(2, ($id % 32)-1);
		foreach($section as $key=>$val)	$mask[] = "`".$key."`=".$val;
	// Получаем ID битовой маски
		$tegination = $mySQL->single_row("SELECT `tid` FROM `gb_tagination` USE INDEX(`section`) WHERE ".implode(" AND ", $mask)." LIMIT 1");
		if(empty($tegination['tid'])){
			$tid = $mySQL->single_row("INSERT INTO `gb_tagination` SET ".implode(", ", $mask));
			return $tid;
		}else return $tegination['tid'];
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	}else return 2;
}
function round_time($ts, $step){
	return(floor(floor($ts / 60) / 60) * 3600 + floor(date("i", $ts) / $step) * $step * 60);
}
?>