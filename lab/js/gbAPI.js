
var doc = document;

/* Ajax **************************************************************************************************************/

if(window.XMLHttpRequest){
	XMLHttpRequest.prototype.ready = true;
	XMLHttpRequest.prototype.stack = [];
	XMLHttpRequest.prototype.defaults = {
		"body":'{}',
		"async":true,
		"protect":true,
		"method":"POST",
		"timeout":15000,
		"Cache-Control":"no-cache",
		"onsuccess":function(response){ return true },
		"onerror":function(response){ console.log(response); },
		"Content-Type":"application/json"	//	"text/plain", "text/xml", "text/html", "application/octet-stream", "multipart/form-data", "application/x-www-form-urlencoded";
	};
	XMLHttpRequest.prototype.push = function(request){
		if(request['addressee']){
			for(var key in XHR.defaults){
				if(XHR.defaults.hasOwnProperty(key)){
					request[key] = request[key] || XHR.defaults[key];
				}	
			}
			XHR.stack.push(request);
			XHR.execute();
		}else console.log("XHR ERROR: Not specified addressee");
	};
	XMLHttpRequest.prototype.execute = function(){
		if(XHR.ready){
			var request = XHR.stack.shift();
			XHR.ready=false;
			
			XHR.open(request.method, request.addressee, request.async);
			XHR.timeout = request.timeout;
			XHR.setRequestHeader("Content-Type", request['Content-Type']);
			
			var indicator = doc.create("div", "", {id:"loading-indicator", style:"opacity:1.0"});
			doc.body.appendChild(indicator);
			XHR.onreadystatechange=function(){
				if(XHR.readyState==4){
					XHR.ready=true;
					doc.body.removeChild(indicator);
					(XHR.status==200) ? request.onsuccess(XHR.response) : request.onerror(XHR.statusText);
					if(XHR.stack.length) XHR.execute();
				}
			}
			if(request.protect) reauth();
			XHR.send(request.body);
		}
	}
	XMLHttpRequest.prototype.uploader = function(files, addressee, onsuccess){
		onsuccess = onsuccess || function(){ return true }
		var box = modalBox('{}', "uploader/progress", function(){}, true);
		box.onopen = function(){
			var seek = 0;
			box.log = box.body.querySelector("#upload-log");
			box.progressbar = box.body.querySelector("#progress");
			var BLOCK_SIZE = 2097152;
			for(var i=files.length; i--;){
				for(var j=0; j<files[i].size; j+=BLOCK_SIZE){
					seek = j + BLOCK_SIZE;
					XHR.push({
						"seek":seek,
						"not_last":i,
						"size":files[i].size,
						"Content-Type":"application/octet-stream",
						"addressee":addressee+"&file="+files[i].name.translite("_")+"&seek="+j,
						"body":files[i].slice(j, j+BLOCK_SIZE),
						"onsuccess":function(response){
							box.progressbar.max = this.size;
							box.progressbar.value = this.seek;
							if(this.seek >= this.size){
								if(this.not_last){
									box.log.innerHTML += response;
								}else{
									box.drop();
									onsuccess(response);
								}
							}
						}					
					});
				}
			}
		}
	}
	var XHR = new XMLHttpRequest();
}
var JSONP = new function(){
	this.request=function(url, onSuccess, options){
		options=options || {};
		var temp=[];
		var loaded = false;							// флаг, что вызов прошел успешно
		var callbackName = 'f'+random().toString(16);	// сгенерировать имя JSONP-функции для запроса
		
		options.callback="JSONP."+callbackName;			//
		for(var key in options){						//
			if(options.hasOwnProperty(key)){			//
				temp.push(key+"="+options[key]);		// формируем URL запроса
			}											//
		}												//
		url+="?"+temp.join("&");						//
		JSONP[callbackName]=function(data){				// ..и создадим саму функцию в реестре
			loaded = true; 								// обработчик вызвался, указать что всё ок 
			onSuccess(data);							// вызвать onSuccess
			document.head.removeChild(script);			// удаляем результат вызова из секции head
			delete JSONP[callbackName];				// чистим реестр
		}
		var script = doc.create("script","",{"src":url, "type":"text/javascript"});
		script.onload = script.onerror = script.onreadystatechange = function(){
			if(!loaded || this.readyState == 'complete' || this.readyState == 'loaded'){
				document.head.removeChild(script);
				delete JSONP[callbackName];
			}
		}
		document.head.appendChild(script);
	}
}

/* Box ************************************************************************************************************/

var modalBox = function(params, source, onsubmit, protect){
	var substrate = doc.body.querySelector("#substrate");
	if(substrate) boxList[boxList.onFocus].drop();

	substrate = doc.create("div", "", {id:"substrate"});
	doc.body.appendChild(substrate);
	
	setTimeout(function(){
		substrate.style.opacity = 1.0;
		substrate.style.paddingTop = "0px";
	}, 100);
	var box = new Box(params, source, protect);
		box.onsubmit = function(form){
			onsubmit(form);
		}
	return box;
}
var Box = function(params, source, protect){
	var box = this;
	this.handle = this.window = this.title = this.body = this.left = this.top = null;
	setTimeout(function(){
		XHR.push({
			"reauth":protect || false,
			"addressee":"/"+source,
			"body":params,
			"Content-Type":"application/json",
			"onsuccess":function(response){
				var substrate = doc.create("div", response);

				box.window = substrate.querySelector(".box");
				box.window.style.zIndex = 98;
				box.handle = box.window.id;
				boxList[box.handle] = box;
				box.body = box.window.querySelector(".box-body");
				box.title = box.window.querySelector(".box-title");
				box.title.onmousedown = box.move;
				
				translate.fragment(box.window);
				
				var scripts = substrate.querySelectorAll("script");
				for(var i=0; i<scripts.length; i++){
					var script = doc.create("script");
					if(scripts[i].src){
						script.src = scripts[i].src;
					}else script.innerHTML = scripts[i].innerHTML;
					scripts[i].parentNode.replaceChild(script, scripts[i]);
				}
				
				substrate = doc.body.querySelector("#substrate");
				if(substrate){
					substrate.appendChild(box.window);
				}else doc.body.appendChild(box.window);
				
				box.align();
				boxList.focus(box.window);
				if(box.onopen) box.onopen();
				if(box.onsubmit){
					box.window.onsubmit = function(event){
						event.preventDefault();
						var fields = box.window.querySelectorAll("input, textarea");
						for(var i=fields.length; i--;){
							if(!fields[i].value.isFormat(fields[i].getAttribute("pattern"))){
								return false;
							}
						}
						box.onsubmit(this)
						return false;
					}
				}
				window.onkeyup = function(event){
					if(event.keyCode===27){
						box.drop();
					}
				}
			}
		});
	}, 50);
	this.move = function(event){
		if(event.preventDefault){ event.preventDefault(); }else{ event.returnValue = false; }
		var initialX = event.clientX - box.window.offsetLeft;
		var initialY = event.clientY - box.window.offsetTop;
		document.onmousemove = function(event){
			box.left = box.window.style.left = (event.clientX - initialX)+"px";
			box.top = box.window.style.top = (event.clientY - initialY)+"px";
		}
		document.onmouseup = function(){document.onmousemove = null}
	};
	this.align = function(){
		box.body.style.maxHeight=(doc.height-100)+"px";
		box.window.style.top = box.top || "calc(50% - "+(box.window.offsetHeight/2)+"px)";
		box.window.style.left = box.left || "calc(50% - "+(box.window.offsetWidth/2)+"px)";
	},
	this.drop = function(){
		if(box.ondrop) box.ondrop();
		delete(boxList[box.window.id]);
		var substrate = doc.body.querySelector("#substrate");
		if(substrate){
			substrate.style.opacity = 0.0;
			substrate.style.paddingTop = "100%";
			setTimeout(function(){ doc.body.removeChild(substrate); }, 100);
		}else doc.body.removeChild(box.window);
	};
	this.onsubmit = this.ondrop = this.onopen = null;
}
var boxList = {
	"onFocus":"",
	"focus":function(obj){
		for(var key in boxList){
			if(typeof boxList[key]=="object"){
				boxList[key].window.style.zIndex--;
				boxList[key].window.style.opacity = 0.8;
			}
		}
		obj.style.zIndex = 98;
		obj.style.opacity = 1.0;
		handle = boxList.onFocus = obj.id;
	},
	"drop":function(handle){
		boxList[handle].drop();
	},
	"clear":function(){
		for(var key in boxList){
			if(typeof boxList[key]=="object"){
				boxList[key].drop();
				delete(boxList[key]);
			}
		}
	}
};

/* Object ************************************************************************************************************/

function inArray(obj, value){
	for(var key in obj) if(obj.hasOwnProperty(key) && (obj[key] == value)) return key;
	return false;
}
function flip(obj){
	var outObj={};
	obj=obj || {};
	for(var key in obj){
		if(typeof(obj[key]) in {"string":0,"number":0,"boolean":0} || obj[key]===null && obj.hasOwnProperty(key)){
			outObj[obj[key]]=key;
		}
	} return outObj;
}
function join(selector, obj){
	var outArr=[];
	obj=obj || {};
	for(var key in obj){
		if(typeof(obj[key]) in {"string":0,"number":0,"boolean":0} || obj[key]===null && obj.hasOwnProperty(key)){
			outArr.push(obj[key]);
		}
	} return outArr.join(selector);
}

/* Array *************************************************************************************************************/

Array.prototype.inArray = function(value){
	for(var i=this.length; i--;) if(this[i] == value) return i;
	return NaN;
}
Array.prototype.toJSON = function(){
	var isArray, item, t, json = [];
	for(var i=0; i<this.length; i++){
		item=this[i];
		t=typeof(item);
		isArray = (item.constructor == Array);
		if(t=="string"){
			item = '"'+item+'"';
		}else if(t=="object" && item!==null){
			item=JSON.encode(item);
		}
		json.push(String(item));
	}
	return '['+String(json)+']';
}
Array.prototype.flip = function(){
	var obj = {};
	for(var i=0; i<this.length; i++){
		obj[this[i]]=i;
	}
	return obj;
}


/* String ************************************************************************************************************/

String.prototype.trim=function(){
	return this.replace(/(^\s+)|(\s+$)/g, "");
}

String.prototype.levenshtein=function(substr){
	var length1=this.length;
	var length2=substr.length;
	var diff,tab=new Array(); 
	for(var i=length1+1; i--;){
		tab[i]=new Array();
		tab[i].length=length2+1;
		tab[i][0]=i;
	}
	for(var j=length2+1; j--;){tab[0][j]=j;}
	for(var i=1; i<=length1; i++){
		for(var j=1; j<=length2; j++){
			diff=(this.toLowerCase().charAt(i-1)!=substr.toLowerCase().charAt(j-1));
			tab[i][j]=Math.min(Math.min(tab[i-1][j]+1, tab[i][j-1]+1), tab[i-1][j-1]+diff);     
		}
	}
	return tab[length1][length2];
}

String.prototype.translite=function(){
	var dictionary={
	"а":"a",	"б":"b",	"в":"v",	"г":"g",	"ґ":"g",	"д":"d",
	"е":"e",	"є":"ye",	"ж":"zh",	"з":"z",	"и":"i",	"і":"i",
	"ї":"yi",	"й":"y",	"к":"k",	"л":"l",	"м":"m",	"н":"n",
	"о":"o",	"п":"p",	"р":"r",	"с":"s",	"т":"t",	"у":"u",
	"ф":"f",	"х":"h",	"ы":"y",	"э":"e",	"ё":"e",	"ц":"ts",
	"ч":"ch",	"ш":"sh",	"щ":"shch",	"ю":"yu",	"я":"ya",	" ":"-",
	"ь":"",		"ъ":""};
	
	var str = this.trim().toLowerCase();
	if(~str.search(/[іїґє]/)){
		dictionary['г'] = "h";
		dictionary['и'] = "y"
		dictionary['х'] = "kh";
	}
	var str = str.replace(/./g, function(x){
		if(dictionary.hasOwnProperty( x )){
			return dictionary[x];
		}else return x.replace(/[^.a-z0-9_-]+/,"");
	});
	return str.replace(/-{2,}/g,"-");
}

String.prototype.isFormat=function(reg){
	var str = this;
	var pattern = new RegExp(reg || ".");
	if(!pattern.test(str)){
		alertBox("incorrect format");
		return false;
	}else return true;
}
String.prototype.jsonToObj=function(){
	var obj,str = this;
	try{
		obj = JSON.parse(str);
	}catch(e){
		obj = false;
	}
	return obj;
}
String.prototype.format=function(numbers){
	var str = this;
	for(var i=0; i<numbers.length; i++){
		pattern = /%\d*[dbx]/.exec(str)[0];
		key=pattern[pattern.length-1];
		value=parseInt(numbers[i]).toString({"d":10, "b":2, "x":16}[key]);
		lng=parseInt(pattern.substring(1));
		for(var fill="0"; value.length<lng; value=fill+value);
		str = str.replace(pattern, value);
	}
	return str;
}

/* Number ************************************************************************************************************/

function random(min, max){
	min = min || 0;
	max = max || 2147483647;
	return (Math.random() * (max - min + 1) + min)^0;
}

/* COOKIES ***********************************************************************************************************/

	var COOKIE = new function(){
		this.get=function(cName){
			var obj = {};
			var cookies=document.cookie.split(/;|=/);
			for(var i=0; i<cookies.length; i++){
				if(cookies[i].trim()===cName) return decodeURI(cookies[++i]);
			}
		}
		this.set=function(name, value, options){
			options = options || {};
			
			var expires = options.expires;
			if(typeof(expires) == "number" && expires){
				var d = new Date();
				d.setTime(d.getTime() + expires * 1000);
				expires = options.expires = d;
			}
			if(expires && expires.toUTCString) {
				options.expires = expires.toUTCString();
			}
			value = encodeURIComponent(value);
			var updatedCookie = name+"="+value;
			for(var key in options){
				if(options.hasOwnProperty(key)){ 
					updatedCookie+="; "+key;
					if(options[key]!==true){
						updatedCookie+="="+options[key];
					}
				}
			}
			document.cookie = updatedCookie;
		}
		this.remove=function(name){
			this.set(name, "", {"expires":-1});
		}
		this.clear=function(){
			for(var key in this){
				if(typeof(this[key])=="string"){
					this.set(key, "", {"expires":-1});
				}
			}
		}
	}

/* URL ***************************************************************************************************************/

function splitParams(str){
	var path = str.replace(/^\?/, "").split(/\&/);
	var params={}, temp=[];
	for(var i=0; i<path.length; i++){
		temp = path[i].split(/=/);
		params[temp[0]] = temp[1];
	}
	return params;
}

/* HTMLElement *******************************************************************************************************/

	HTMLDocument = Document || HTMLDocument;
	
	HTMLDocument.prototype.width = self.innerWidth || doc.documentElement.clientWidth;
	HTMLDocument.prototype.height = self.innerHeight || doc.documentElement.clientHeight;
	
	HTMLDocument.prototype.create=function(tagName, content, attributes){
		if(tagName){
            var obj = this.createElement(tagName);
            if(content){
                if(typeof(content)=="string"){
					obj.innerHTML=content;
			}else if(typeof(content)=="object" && (content.nodeType in {"1":null, "3":null, "11":null})){
                    obj.appendChild(content);
                }
            }
            if(attributes){
                for(var key in attributes){
					if(attributes.hasOwnProperty(key)){
						obj.setAttribute(key, attributes[key]);
					}
				}
            }
        }else obj=this.fragment(content);
        return obj;
    }
	HTMLDocument.prototype.fragment=function(content){
		var obj = this.createDocumentFragment();
        if(content){
			if(typeof(content)=="string"){
				var temp=this.createElement("div");
                    temp.innerHTML=content;
                var children=temp.childNodes;
                obj.appendChildren(children);
			}else if(typeof(content)=="object" && content.nodeType in {1:null, 3:null, 11:null}){
                obj.appendChild(content);
            }
        }return obj;
	}
	HTMLElement.prototype.first=function(){
        var node=this.firstChild;
        while(node && node.nodeType!=1){
			node=node.nextSibling;
		}
        return node || null;
    }
	HTMLElement.prototype.last=function(){
        var node=this.lastChild;
        while(node && node.nodeType!=1){
			node=node.previousSibling;
		}
        return node || null;
    }
	HTMLElement.prototype.next=function(){
		var node=this.nextSibling;
		while(node && node.nodeType!=1){
			node = node.nextSibling;
		}
		return node || null;
    }
	HTMLElement.prototype.previous=function(){
		var node=this.previousSibling;
		while(node && node.nodeType!=1){
			node = node.previousSibling;
		}
		return node || null;
    }
	HTMLElement.prototype.parent=function(level){
		level=level || 1;
		var node=this;
		for(; level--;){
			if(node){ node=node.parentNode; }
		}
		return node;
    }
	HTMLElement.prototype.ancestor=function(tagName){
		if(tagName){
			tagName=tagName.toUpperCase();
			var node=this.parentNode;
			while(node && node.nodeName!=tagName){
				node=node.parentNode;
			}
			return node || null;
		}else return false;
    }
	HTMLElement.prototype.insertToBegin=function(node){
		if(node){
			var first;
			if(first=this.firstChild){
				first = this.insertBefore(node, first);
			}else{
				first = this.appendChild(node);
			}
			return first;
		}else return false;
    }
	HTMLElement.prototype.insertBeforeNode=function(node){
		if(typeof node==="string"){
			this.insertAdjacentHTML("afterBegin", node);
		}else if(typeof node==="object"){
			this.insertAdjacentElement("afterBegin", node)
		}else return false;
    }
	HTMLElement.prototype.insertAfter=function(node){
		if(typeof node==="string"){
			this.insertAdjacentHTML("afterEnd", node);
		}else if(typeof node==="object"){
			this.insertAdjacentElement("afterEnd", node)
		}else return false;
    }
	HTMLElement.prototype.childElements=function(){
		var children = this.childNodes;
		var childrenList = [];
		if(children.length){
			for(var i=0; i<children.length; i++){
				if(children[i].nodeType==1){
					childrenList.push(children[i]);
				}
			}
		}
		return childrenList;
    }
	HTMLElement.prototype.appendChilds = DocumentFragment.prototype.appendChilds = function(nodeList){
		for(var i=0; i<nodeList.length; i++){
			this.appendChild(nodeList[i]);
		}
		return i;
	}
	HTMLElement.prototype.getCss=function(rule){
		var obj = window.getComputedStyle(this, "");
		return obj.getPropertyValue(rule);
	}
	HTMLElement.prototype.fullScrollTop=function(){
		var srl = 0;
		var obj = this;
		while(obj.nodeType==1){
			srl += obj.scrollTop;
			obj = obj.parentNode;
		}
		return srl;
	}
	HTMLElement.prototype.fullScrollLeft=function(){
		var srl = 0;
		var obj = this;
		while(obj.nodeType==1){
			srl += obj.scrollLeft;
			obj = obj.parentNode;
		}
		return srl;
	}
	HTMLElement.prototype.swap=function(dir){
		if(dir){
			var node = this.nextElementSibling;
			if(node){
				node.insertAdjacentElement("afterEnd", this);
			}
		}else{
			var node = this.previousElementSibling;
			if(node){
				node.insertAdjacentElement("beforebegin", this);
			}
		}
    }
	
/* JSON **************************************************************************************************************/

var JSON = JSON || new Object;

JSON.encode = function(obj){
	var t = typeof(obj);
	if(t!="object" || obj=== null){
		if(t=="string"){ obj='"'+obj+'"'; }
		return String(obj);
	}else{
		var item, json = [], isArray = (obj && obj.constructor == Array);
		for(var key in obj){
			if(obj.hasOwnProperty(key)){
				item=obj[key]; t=typeof(item);
				if(t=="string"){
					item = '"'+item.replace(/"/g,"&quot;").replace(/\t|\n|\v/g,"")+'"';
				}else if(t=="object" && item!==null){
					item=JSON.encode(item);
				}
				json.push((isArray ? '' : '"'+key.replace(/"/g,"&quot;")+'":')+String(item));
			}
		}
		return isArray ? '['+String(json)+']' : '{'+String(json)+'}';
	}
}
JSON.stringify = JSON.stringify || JSON.encode;
JSON.parse = JSON.parse || function(str){
	if(str==="") str = '""';
	eval("var obj="+str+";");
	return obj;
}

/* Session ***********************************************************************************************************/

var session = window.sessionStorage || new function(){
	try{
		JSON.parse(window.name);
	}catch(e){ window.name = "{}"; }
	
	this.getItem = function(varName){
		return JSON.parse(window.name)[varName] || null;
	}
	this.setItem = function(varName, val){
		var temp=JSON.parse(window.name);
			temp[varName]=val;
			window.name=JSON.stringify(temp);
	}
}

var storage = window.localStorage || session;

session.__proto__.open=function(){
	var today = new Date();
		today.setUTCHours(0,0,0,0);
	var oldTimestamp = session.getItem("today");
	var newTimestamp = today.getTime();
	if(newTimestamp > oldTimestamp){
		session.setItem("today", today.getTime());
		return false;
	}else return true;
}
function reauth(){
	var cookies=document.cookie.split(/;\s*/g);
	for(var i=cookies.length; i--;){
		var cookie=cookies[i].split(/=/g);
		if(cookie[0]==="key"){
			document.cookie = "finger="+encodeURIComponent( md5( session.getItem("login") + session.getItem("passwd") + decodeURI(cookie[1])))+"; path=/";
			break;
		}
	}
}

/* Date **************************************************************************************************************/

function date(pattern, timestamp){
	var M = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
	var F = ["January","February","March","April","May","June","July","August","September","October","November","December"];
	pattern=pattern||"d.m.Y";
	var today= timestamp ? new Date(timestamp) : new Date();
	var params=pattern.trim().split(/\W+/);
	var set={
		"d":"%02d".format([today.getDate()]),
		"m":"%02d".format([today.getMonth()+1]),
		"M":M[today.getMonth()],
		"F":F[today.getMonth()],
		"Y":"%04d".format([today.getFullYear()]),
		"H":"%02d".format([today.getHours()]),
		"i":"%02d".format([today.getMinutes()]),
		"s":"%02d".format([today.getSeconds()]),
		"D":today.getDay(),
		"U":((today.getTime()/1000)^0)
	}
	for(var i=0; i<params.length; i++){
		pattern=pattern.replace(params[i], set[params[i]]);
	}
	return pattern;
}

/* Other *************************************************************************************************************/

window.showModalDialog = window.showModalDialog || function(url, winName){
	options = "location=no,menubar=no,resizable=no,scrollbars=no,toolbar=no,directories=no,status=no";
	window.open(url, winName, options); 
}

var softScroll = {
	"x": function(position){
		var delta = position - (window.pageXOffset || document.documentElement.scrollLeft);
		var dir = delta / (delta = Math.abs(delta));
		interval = setInterval(function(){
			step=(delta/10)^0;
			delta-=step;
			window.scrollBy(step*dir, 0)
			if(delta<10){
				window.scrollBy(delta, 0)
				clearInterval(interval);
			}
		},5)
	},
	"y": function(position){
		var delta = position - (window.pageYOffset || document.documentElement.scrollTop);
		var dir = delta / (delta = Math.abs(delta));
		interval = setInterval(function(){
			step=(delta/10)^0;
			delta-=step;
			window.scrollBy(0, step*dir)
			if(delta<10){
				window.scrollBy(0, delta*dir)
				clearInterval(interval);
			}
		},5)
	}
}

function isNotValid(value, type, valid){
	if(typeof valid==="string")	valid = valid.split(/,\s*/);
	switch(type){
		case "number":
			if(isNaN(value)){
				alertBox('"'+value+'" is not valid value');
				return true;
			}
		break;
		case "enum":
			if((valid.indexOf(value)<0)){
				alertBox('"'+value+'" is not valid value');
				return true;
			}
		break;
		case "set":
			value = value.split(/,\s*/);
			for(var j=value.length; j--;){
				if(valid.indexOf(value[j])<0){
					alertBox('"'+value[j]+'" is not valid value');
					return true;
				}
			}
		break;
		default: break;
	}
	return false;
}

/*********************************************************************************************************/

var datepicker=function(event, bg){
	bg = bg || blue;
	var obj=event.target;
	var months=["January","February","March","April","May","June","July","August","September","October","November","December"];
	if(obj.value){
		var current=obj.value.split(/\D+/);
		d = new Date(Date.parse(current[1]+"/"+current[0]+"/"+current[2]));
	}else d = new Date();
	d.setDate(1);
	refreshPicker=function(){
		var thYear=d.getFullYear(), thMonth=d.getMonth(), thDay=d.getDay();
		var daysInThMonth = Math.round((+new Date(thYear+(thMonth==11), (thMonth+1)%12, 1) - new Date(thYear, thMonth, 1))/ 86400000);
		
		calendar=document.create("div", "<div class='pickerbar'><label onclick='setMonth(-1)' title='Previous month'>&#xe020;</label> <label onclick='setYear(-1)' title='Previous year'>&#xe045;</label> <label>"+months[thMonth]+" "+thYear+"</label> <label onclick='setYear(1)' title='Next year'>&#xe044;</label> <label onclick='setMonth(1)' title='Next month'>&#xe01f;</label></div><span style='color:#F66;'>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>");
		for(var i=1-thDay; i<=daysInThMonth; i++){
			if(i>0){
				calendar.appendChild(document.create("span", i.toString(), {"class":"dateCell","onclick":"setDate(this)"}));
			}else calendar.appendChild(document.create("span"));
		}
		return calendar;
	}
	var offset = obj.getBoundingClientRect();
	picker=document.create("div", refreshPicker(), {"class":"datepicker "+bg,"style":"top:"+(offset.top - obj.fullScrollTop())+"px;left:"+offset.left+"px"});
	setDate=function(cell){
		d.setDate(cell.textContent);
		obj.value=date("d.m.Y", d.getTime());
		document.body.removeChild(picker);
	}
	setYear=function(dir){
		d.setFullYear(d.getFullYear()+dir);
		picker.removeChild(calendar);
		picker.appendChild(refreshPicker());
	}
	setMonth=function(dir){
		d.setMonth(d.getMonth()+dir);
		picker.removeChild(calendar);
		picker.appendChild(refreshPicker());
	}
	document.body.appendChild(picker);
	
	if(document.body.clientHeight < (picker.offsetTop + picker.offsetHeight)){
		picker.style.top = (document.body.clientHeight-picker.offsetHeight)+"px";
	}
	if(document.body.clientWidth < (picker.offsetLeft + picker.offsetWidth)){
		picker.style.left = (document.body.clientWidth-picker.offsetWidth)+"px";
	}
	
	picker.onmouseout=function(){ obj.onblur=function(){ document.body.removeChild(picker); }}
	picker.onmouseover=function(){ obj.onblur=null; }
}