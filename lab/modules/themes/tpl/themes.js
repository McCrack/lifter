var CTRLKEY=1, ALTKEY=2, SHIFTKEY=4, keyMask=0;
doc.onkeydown = doc.onkeyup = function(event){
	var btn = doc.querySelector("#fixed-btn");
	if(btn){
		keyMask = (CTRLKEY * event.ctrlKey) | (ALTKEY * event.altKey) | (SHIFTKEY * event.shiftKey);
		if(keyMask & 1){
			doc.querySelector("#fixed-btn").className = "selected";
		}else doc.querySelector("#fixed-btn").removeAttribute("class");
	}
}
function multiSelect(btn){
	if(btn.className==="selected"){
		btn.removeAttribute("class");
		keyMask &= 6;
	}else{
		btn.className="selected";
		keyMask |= 1;
	}
}

function selectFile(frm, obj){
	if(obj.nodeName!="TR") obj = obj.ancestor("TR");
	if(keyMask & CTRLKEY){
		obj.className="selected";
		return false;
	}else{
		var rows = frm.querySelectorAll("tr");
		for(var i=rows.length; i--;){
			if(rows[i].contains(obj)){
				setTimeout(function(){ obj.className="selected"; }, 220);
			}else rows[i].removeAttribute("class");
		}
	}
}

function editable(event){
	event.preventDefault();
	var obj = event.target;
	var row = obj.parentNode;
	if(row.className==="selected"){
		setTimeout(function(){ row.removeAttribute("class"); },100);
		obj.contentEditable = true;
		obj.focus();
	}
	obj.onblur = function(){
		obj.contentEditable = false;
	}
	obj.oninput = function(){
		this.onblur = function(){
			obj.contentEditable = false;
			var path = row.dataset.realpath.split(/\//);
				path[path.length-1] = obj.textContent.trim();
			var	newPath = path.join("/");
			reauth();
			XHR.request("/themes/actions/rename", function(xhr){
				obj.onblur = null;
				if(isNaN(xhr.response)){
					alert(xhr.response);
				}else row.dataset.realpath = newPath;
			}, '{"old":"'+row.dataset.realpath+'","new":"'+newPath+'"}', "application/json");
		}
	}
}
function unzip(){
	var file = doc.querySelector("#environment tr.selected").dataset.realpath;
	reauth();
	XHR.request("/themes/actions/unzip", function(xhr){
		parseInt(xhr.response) ? location.reload() : alertBox("unzip error");
	}, file, "text/plain");
}
function removeElements(){
	var rows = doc.querySelectorAll("#environment tr.selected");
	if(rows.length){
		confirmBox("delete elements", function(){
			var params = [];
			for(var i=rows.length; i--;){
				params.push(rows[i].dataset.realpath);
			}
			reauth();
			XHR.request("/themes/actions/remove", function(xhr){
				isNaN(xhr.response) ? alert(xhr.response) : location.reload();
			}, JSON.encode(params), "application/json");
		});
	}else alertBox("elements not selected");
}
var openFile = function(obj){
	var mode = obj.dataset.realpath.split(/\./).pop();
	if(mode in {"html":0, "css":0, "js":0}) location.href = "/themes/"+mode+"?p="+obj.dataset.realpath;
}
var openFolder = function(obj){
	location.href = "/themes/"+obj.dataset.path;
}
function showFile(event){
	var img = event.target;
	var type = img.dataset.type.split(/\//);
	if(type[0]==="image"){
		var offsetY = event.clientY-32;
		var offsetX = event.clientX+20;
		var box = doc.create("div","<img src='"+img.src+"'><br>", {id:"preview", align:"center", style:"top:"+offsetY+"px;left:"+offsetX+"px"});
			box.appendChild(doc.create("div", "<span>Type: "+type[1]+"</span> <span>Size: "+img.dataset.size+"</span> <span>Width: "+img.naturalWidth+"px</span> <span>Height: "+img.naturalHeight+"px</span>", {align:"justify"}));
		doc.body.appendChild(box);
		
		if(document.body.clientHeight < (box.offsetHeight + box.offsetTop)){
			box.style.top = (offsetY - box.offsetHeight + 32)+"px";
		}
	}
}
function hideFile(){
	var preview = doc.querySelector("body>#preview");
	if(preview) doc.body.removeChild(preview);
}

function createFile(){
	var path = location.pathname.split(/\//);
	if(path[2]){
		if(path[3]){
			promptBox("enter file name", function(value){
				var ext = value.split(/\./).pop();
				reauth();
				XHR.request("/themes/actions/create-file", function(xhr){
					location.pathname="themes/"+ext+"?p="+xhr.response;
				}, value, "text/plain");
			}, "^[a-zA-Z0-9-._]+$");
		}else alertBox("theme not selected");
	}else alertBox("domain not selected");
}
function importImagesDialog(onImport){
	promptBox("web page address", function(url){
		onImport = onImport || null;
		var box = new Box('{"url":"'+url+'"}', "uploader/parserbox", true);
			box.onopen = function(){ setTimeout(imgSizeFilter, 200); }
			box.onsubmit = function(form){
				var imgList = [];
				var imgs = box.body.querySelectorAll("input");
				for(var i=imgs.length; i--;){
					if(imgs[i].checked){
						imgList.push(imgs[i].value);
					}
				}
				reauth();
				XHR.request("/themes/actions/import", function(xhr){
					if(parseInt(xhr.response)){
						box.ondrop = onImport;
						box.drop();
					}else alertBox("saving error")
				}, JSON.encode(imgList), "application/json");
			}
	});
}
function imgSizeFilter(){
	var box = boxList[boxList.onFocus];
	var val = parseInt(box.window.size.value);
	var rule = box.window.rule.value;
	var imgs = box.body.querySelectorAll("img");
	for(var i=imgs.length; i--;){
		var sticker = imgs[i].parentNode;
		if(imgs[i][rule] > val){
			sticker.style.display="inline-block";
		}else{
			sticker.style.display="none";
			sticker.querySelector("input").checked = false;
		}
	}
}
function createTheme(){
	var subdomain = location.pathname.split(/\//)[2];
	if(subdomain){
		promptBox("enter theme name", function(value){
			setTimeout(function(){ 
				modalBox('{}', "themes/box", function(form){
				var params = {
					"template":form.theme.value,
					"name":value,
					"subdomain":subdomain
				}
				reauth();
				XHR.request("/themes/actions/create-theme", function(xhr){
					isNaN(xhr.response) ? alert(xhr.response) : location.pathname="themes/"+subdomain+"/"+value;
				}, JSON.stringify(params), "text/plain");
				return false;
				}, true); 
			},200);
		}, "^[a-zA-Z0-9-_]+$");
	}else alertBox("domain not selected");
}
