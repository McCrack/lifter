var CTRLKEY=1, ALTKEY=2, SHIFTKEY=4, keyMask=0;

doc.onkeydown = doc.onkeyup = function(event){
	keyMask = (CTRLKEY * event.ctrlKey) | (ALTKEY * event.altKey) | (SHIFTKEY * event.shiftKey);
}

function selectFile(event){
	var obj = event.target;
	if(obj.nodeName!="TR") obj = obj.ancestor("TR");
	if(keyMask & CTRLKEY){
		obj.className="selected";
		return false;
	}else{
		var rows = doc.querySelectorAll("#environment tr");
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
			var path = row.dataset.path.split(/\//);
			var oldPath = "../"+standby.subdomain+"/modules/"+path.join("/");
				path[path.length-1] = obj.textContent.trim();
			var newPath = "../"+standby.subdomain+"/modules/"+path.join("/");
			reauth();
			XHR.request("/constructor/actions/rename", function(xhr){
				obj.onblur = null;
				if(isNaN(xhr.response)){
					alert(xhr.response);
				}else{
					row.dataset.path = path.join("/");
				}
			}, '{"old":"'+oldPath+'","new":"'+newPath+'"}', "application/json");
		}
	}
}

var openFolder = openFile = function openFolder(obj){
	location.search = "d="+standby.subdomain+"&p="+obj.dataset.path;
}

function saveFile(){
	reauth();
	XHR.request("/constructor/actions/savefile", function(xhr){
		parseInt(xhr.response) ? alertBox("Done") : alertBox("Failed to file save");
	}, edt.getValue(), "text/plain");
}

/**************************************************/

function unzip(){
	var file = "../"+standby.subdomain+"/modules/"+doc.querySelector("#environment tr.selected").dataset.path;
	reauth();
	XHR.request("/constructor/actions/unzip", function(xhr){
		parseInt(xhr.response) ? location.reload() : alertBox("unzip error");
	}, file, "text/plain");
}

function removeElements(){
	var rows = doc.querySelectorAll("#environment tr.selected");
	if(rows.length){
		confirmBox("delete elements", function(){
			var params = [];
			for(var i=rows.length; i--;){
				params.push("../"+standby.subdomain+"/modules/"+rows[i].dataset.path);
			}
			reauth();
			XHR.request("/constructor/actions/remove", function(xhr){
				isNaN(xhr.response) ? alert(xhr.response) : location.reload();
			}, JSON.encode(params), "application/json");
		});
	}else alertBox("elements not selected");
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

/*******************************************************************/

function createfile(){
	promptBox("new file name", function(fileName){
		reauth();
		XHR.request("/constructor/actions/create-file", function(xhr){
			if(isNaN(xhr.response)){
				setTimeout(function(){ alertBox(xhr.response); }, 300);
			}else{
				location.reload();
			}
		}, fileName.trim().toLowerCase(), "text/plain");	
	}, "^[a-zA-Z0-9_.-]+$");
}
function newFolder(){
	promptBox("new folder name", function(folderName){
		reauth();
		XHR.request("/constructor/actions/create-folder", function(xhr){
			isNaN(xhr.response) ? alertBox(xhr.response) : location.reload();
		}, folderName.trim().toLowerCase(), "text/plain");
	}, "^[a-zA-Z0-9_-]+$");
}

/*******************************************************************/

function reloadManual(form){
	reauth();
	XHR.request("/manual/actions/reload/"+standby.module+"/"+form.language.value, function(xhr){
		var frame = doc.querySelector("#manual>iframe.HTMLDesigner").contentWindow.document;
			var response = xhr.response;
			if(frame.changed){
				confirmBox("save changes", function(){
					reauth();
					XHR.request("/manual/actions/save/"+standby.module+"/"+form.dataset.lang, function(xhr){
						frame.setValue(response);
					}, frame.getValue(), "text/html");
					form.dataset.lang = form.language.value;
				});
			}else{
				frame.setValue(response);
				form.dataset.lang = form.language.value;
			}
	}, "", "text/plain");
}
function saveManual(form){
	var frame = doc.querySelector("#manual>iframe.HTMLDesigner").contentWindow.document;
	reauth();
	XHR.request("/manual/actions/save-or-create/"+standby.module+"/"+form.language.value, function(xhr){
		frame.changed = false;
	}, frame.getValue(), "text/html");
	return false;
}

/****************************************************************************/

function createInstaller(){
	reauth();
	XHR.request("/constructor/actions/create-installer", function(xhr){
		location.pathname = xhr.response;
	}, "", "text/plain");
	return false;
}