<?php

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

/* MULTILANGUAGE **********************************************************************************************************************************************/

class Wordlist{
	public $dictionary=array();
	public function __construct($wordlist="", $subdomain="lab", $lang=USER_LANG){
		if(is_array($wordlist)){
			foreach($wordlist as $file){
				if(file_exists("../".$subdomain."/localization/".$file.".json")){
					$subwl = JSON::load("../".$subdomain."/localization/".$file.".json");
					$this->dictionary = array_merge($this->dictionary, $subwl[$lang]);
				}
			}
		}elseif(is_string($wordlist)){
			if(file_exists("../".$subdomain."/localization/".$wordlist.".json")){
				$wl = JSON::load("../".$subdomain."/localization/".$wordlist.".json");
				$this->dictionary = &$wl[$lang];
			}else{
				foreach(scandir("../".$subdomain."/localization") as $file){
					if(is_file("../".$subdomain."/localization/".$file)){
						$ext = explode(".", $file);
						if(end($ext)==="json"){
							$subwl = JSON::load("../".$subdomain."/localization/".$file);
							$this->dictionary = array_merge($this->dictionary, $subwl[$lang]);
						}
					}
				}
			}
		}
	}
	public function __get($key){
		return empty($this->dictionary[$key]) ? $key : $this->dictionary[$key];
	}
	public function translateDocument($doc){
		$nodes=$doc->xpath("//*[@data-translate]");
		for($i=$nodes->length; $i--;){
			$att=$nodes->item($i)->getAttribute("data-translate");
			if(empty($att)) $att="nodeValue";
			$nodes->item($i)->{$att} = empty($this->dictionary[$nodes->item($i)->{$att}]) ? $nodes->item($i)->{$att} : $this->dictionary[$nodes->item($i)->{$att}];
		}
	}
	public static function translite($str, $space=" ", $url=false){
		$str = strtr($str, array("а"=>"a", "б"=>"b", "в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e", "ж"=>"g", "з"=>"z", "и"=>"i", "і"=>"i", "ї"=>"yi", "й"=>"y", "к"=>"k", "л"=>"l", "м"=>"m", "н"=>"n", "о"=>"o", "п"=>"p", "р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u", "ф"=>"f", "ы"=>"i", "э"=>"e", "ё"=>"yo", "х"=>"h", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh", "щ"=>"shch", "ъ"=>"", "ь"=>"", "ю"=>"yu", "я"=>"ya", "А"=>"A", "Б"=>"B", "В"=>"V", "Г"=>"G", "Д"=>"D", "Е"=>"E", "Ж"=>"G", "З"=>"Z", "И"=>"I", "І"=>"I", "Ї"=>"YI", "Й"=>"Y", "К"=>"K", "Л"=>"L", "М"=>"M", "Н"=>"N", "О"=>"O", "П"=>"P", "Р"=>"R", "С"=>"S", "Т"=>"T", "У"=>"U", "Ф"=>"F", "Ы"=>"I", "Э"=>"E", "Ё"=>"YO", "Х"=>"H", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH", "Щ"=>"SHCH", "Ъ"=>"", "Ь"=>"", "Ю"=>"YU", "Я"=>"YA", " "=>$space));
		if($url) $str = preg_replace("/(?!-)\W/", "", $str);
		return $str;
	}
}

/* DOM ********************************************************************************************************************************************************/

class HTMLDocument extends DOMDocument{
	public function __construct($path){ 
		if(file_exists($path)){
			$this->loadHTMLFile($path);
		}else{
			$this->loadHTML($path);
		}
		$this->registerNodeClass('DOMElement', 'extElement');
		$this->formatOutput=true;
	}
	public function xpath($query, $node=false){
		$xp = new DOMXPath($this);
		return $xp->evaluate($query, $node?$node:$this->documentElement);
	}
	public function getElementByAttribute($attr, $val, $num=0){ return $this->xpath("//*[@".$attr."='".$val."']")->item($num); }
	public function getElementsByAttribute($attr, $val){ return $this->xpath("//*[@".$attr."='".$val."']"); }
	public function createFragment($inner=""){
        $fragment=$this->createDocumentFragment();
        if(is_string($inner)){
			$dom = new DOMDocument;
			$dom->loadHTML("<!DOCTYPE html><meta http-equiv='Content-Type' content='text/html; charset=utf-8'><div id='html-to-dom-input-wrapper'>".$inner."</div>");
			foreach($dom->getElementById("html-to-dom-input-wrapper")->childNodes as $child){
				$fragment->appendChild($this->importNode($child, true));
			}
        }elseif(is_object($inner)){
            $type=get_class($inner);
			if($type=="extElement" || $type=="DOMDocumentFragment" || $type=="DOMElement"){
				$fragment->appendChild($inner);
			}elseif($type=="DOMNodeList"){
				for($i=0; $i<$inner->length; ++$i){ $fragment->appendChild($inner->item($i)); }
			}
        }
        return $fragment;
    }
	public function create($nodeName, $inner=null, $attributes=null){
		$newNode=$this->createElement($nodeName);
		if(is_string($inner)){
			$newNode->appendChild($this->createFragment($inner));
        }elseif(is_object($inner)){
			$type=get_class($inner);
			if($type=="extElement" || $type=="DOMDocumentFragment" || $type=="DOMElement"){
				$newNode->appendChild($inner);
			}elseif($type=="DOMNodeList"){
				for($i=0; $i<$inner->length; ++$i){ $newNode->appendChild($inner->item($i)); }
			}
        }
		if(is_array($attributes)){
			foreach($attributes as $key=>$val){	$newNode->setAttribute($key, $val); }
		}
		return $newNode;
    }
	public function importHTML($str){
		$dom = new DOMDocument;
		$dom->loadHTML("<!DOCTYPE html><html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'></head><body><div id='html-to-dom-input-wrapper'>".$str."</div></body></html>");
		$fragment=$this->createDocumentFragment();
		foreach($dom->getElementById("html-to-dom-input-wrapper")->childNodes as $child){
			$fragment->appendChild($this->importNode($child, true));
		}
		return $fragment;
	}
	public function __toString(){ return $this->saveHTML(); }
	public function __get($name){ return $this->xpath("id('".$name."')")->item(0); }
	public function __set($name, $val){ $this->xpath("id('".$name."')")->item(0)->nodeValue=$val; }
}
class XMLDocument extends HTMLDocument{
	public function __construct($path){
		if(file_exists($path)){
			$this->load($path);
		}else $this->loadXML($path);
		$this->registerNodeClass('DOMElement', 'extElement');
		$this->formatOutput=true;
	}
	public function createFragment($inner=""){
        $fragment=$this->createDocumentFragment();
        if(is_string($inner)){
			$fragment->appendXML($inner);
        }elseif(is_object($inner)){
            $type=get_class($inner);
			if($type=="extElement" || $type=="DOMDocumentFragment" || $type=="DOMElement"){
				$fragment->appendChild($inner);
			}elseif($type=="DOMNodeList"){
				for($i=0; $i<$inner->length; ++$i){
					$fragment->appendChild($inner->item($i));
				}
			}
        }
        return $fragment;
    }
	public function __get($name){ return $this->xpath("//*[@id='".$name."'][1]")->item(0); }
	public function __set($name, $val){ $this->xpath("//*[@id='".$name."'][1]")->item(0)->nodeValue=$val; }
	public function __toString(){ return $this->saveXML(); }
}
class extElement extends DOMElement{
	public function __get($name){ return $this->getAttribute($name); }
	public function __set($name, $value){ $this->setAttribute($name, $value); }
	public function __toString(){ return $this->textContent; }
    public function getElementByAttribute($attr, $val, $num=0){ return $this->ownerDocument->xpath(".//*[@".$attr."='".$val."']", $this)->item($num); }
    public function getElementsByAttribute($attr, $val){ return $this->ownerDocument->xpath(".//*[@".$attr."='".$val."']", $this); }
	public function childs($tagName="*"){ return $this->ownerDocument->xpath($tagName, $this); }
	public function xpath($query){ return $this->ownerDocument->xpath($query, $this); }
	public function __invoke($val=false){
		if($val){
			$this->nodeValue = $val;
		}else return $this->ownerDocument->createFragment($this->childs());
	}
	public function appendHTML($str){
		if(is_string($str)){
		return $this->appendChild($this->ownerDocument->importHTML($str));
		}else return false;
	}
	public function first($type=1){
		$node=$this->firstChild;
		while($node && $node->nodeType!=$type){ $node=$node->nextSibling; }
		return $node ? $node : false;
	}
	public function last($type=1){
		$node=$this->lastChild;
		while($node && $node->nodeType!=$type){ $node=$node->previousSibling; }
		return $node ? $node : false;
	}
	public function next($type=1){
		$node=$this->nextSibling;
		while($node && $node->nodeType!=$type){
			$node=$node->nextSibling;
		}
		return $node ? $node : false;
	}
	public function previous($type=1){
		$node=$this->previousSibling;
		while($node && $node->nodeType!=$type){
			$node=$node->previousSibling;
		}
		return $node ? $node : false;
	}
	public function insertAfter($newnode){
		$refnode = $this->next(1);
		if($refnode){
			$this->parentNode->insertBefore($newnode, $refnode);
		}else $this->parentNode->appendChild($newnode);
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
		$field=array();
		foreach($fields as $key=>$val){
			$field[]="`".$key."`='".$val."'";
		}		
		$field=implode(",", $field);
		if(is_array($where)) $where=implode(" AND ", $where);
		$this->multi_query("UPDATE `".$table."` SET ".$field." WHERE ".$where);
		return $this->affected_rows;
	}
	public function multi_insert($table, $fields){
		$field=array();
		$vals=array();
		foreach($fields as $key=>$values){
			$field[]="`".$key."`";
			foreach($values as $itr=>$val){
				$vals[$itr][]="'".$val."'";
			}
		}
		foreach($vals as $key=>&$val){
			$val=implode(",", $val);
		}
		$this->query("INSERT INTO `".$table."` (".implode(",", $field).") VALUES (".implode("),(", $vals).")");
		return $this->insert_id;
	}
	public function insert($table, $fields){
		$field=array();
		foreach($fields as $key=>$values) $field[]="`".$key."`='".$values."'";
		$this->query("INSERT INTO `".$table."` SET ".implode(",", $field));
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

/* FS ********************************************************************************************************************************************************/

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
function fillingZipArchive($zip, $path){
	$zip->addEmptyDir($module);
	foreach(scandir("modules/".$path) as $file){
		if(is_file("modules/".$path."/".$file)){
			$zip->addFile("modules/".$path."/".$file, $path."/".$file);
		}elseif(is_dir("modules/".$path."/".$file) && ($file!="." && $file!="..")){
			fillingZipArchive($zip, $path."/".$file);
		}
		
	}
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

/* OTHER ********************************************************************************************************************************************************/

function getProtocol(){
	if(isset($_SERVER['HTTPS'])){
		$protocol = "https";
	}elseif(isset($_SERVER['SERVER_PORT']) && ("443"==$_SERVER['SERVER_PORT'])){
		$protocol = "https";
	}else $protocol = "http";
	return $protocol;
}
function check_browser_language($set, $default){
	$language = $default;
	if(($subset = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']))){
		if(preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $subset, $subset)){
			$subset = array_combine($subset[1], $subset[2]);
			foreach($subset as $n => $v){
				$n = strtok($n, '-');
				if(in_array($n, $set)){
					$language = $n;
					break;
				}
			}
		}
	}return $language;
}
function round_time($ts, $step){
	return(floor(floor($ts / 60) / 60) * 3600 + floor(date("i", $ts) / $step) * $step * 60);
}
?>