var tabbar, environment, root, handle;

/* Initialization ****************************************/

var subdomain = location.pathname.split(/\//)[2] || false;
//standby[subdomain] = null;
//standby.editable = null;
standby.editable = standby.editable || {};

var editable = new Proxy(standby.editable,{
	get(target, name){ return target[name] || null; },
	set(target, name, value){
		target[name] = value;
		window.localStorage[SECTION] = JSON.stringify(Standby);
	},
	deleteProperty: function(target, property){
		delete(target[property]);
		window.localStorage[SECTION] = JSON.stringify(Standby);
	}
});

window.onload=function(){
	translate.fragment();
	standby.leftbar = "explorer";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	if(subdomain) reloadExplorer(standby[subdomain] || "../"+subdomain);
	
	tabbar = doc.querySelector("#topbar");
	environment = doc.querySelector("#environment");
	root = doc.querySelector("#explorer>.dom-root");
	
	for(var handle in editable){
		if(editable.hasOwnProperty(handle)) loadFile(handle, editable[handle]);
	}
}

/*********************************************************/


var Open = function(obj){
	if(obj.className==="folder"){
		reloadExplorer(obj.dataset.path);
	}else if(obj.className==="openfolder"){
		var path = obj.dataset.path.split(/\//);
			path.pop();
		reloadExplorer(path.join("/"));
	}else if(obj.dataset.type==="text" || obj.dataset.type==="application"){
		if(handle = inArray(editable, obj.dataset.path)){
			tabbar.querySelector("label[for='"+handle+"']").click();
			return false;
		}
		handle = "t-"+Date.now();
		editable[handle] = obj.dataset.path;
		loadFile(handle, obj.dataset.path);
	}
}
function reloadExplorer(path){
	XHR.push({
		"addressee":"/developer/actions/tree",
		"body":path,
		"onsuccess":function(response){
			standby[subdomain] = path;
			root.innerHTML = "";
			root.appendChild( buildFolderTree( JSON.parse(response) ) );
		}	
	});
}
function buildFolderTree(obj){
	let root = doc.create("div", "", {"class":"root"});
	for(var key in obj){
		if(!obj.hasOwnProperty(key)) continue;
		let type = obj[key]['type'].split(/\//)[0];
		switch(type){
			default:
				type = "application";
			case "image":
			case "text":
				root.appendChild( doc.create("span", key, {"class":"file", "data-type":type, "data-path":obj[key]['path']}) );
			break;
			case "folder":
			case "openfolder":
				root.appendChild( doc.create("span", key, {"class":type, "data-path":obj[key]['path']}) );
				root.appendChild(buildFolderTree(obj[key]['content']), {"class":"root"});
			break;
		}
	}
	return root;
}

function loadFile(handle, path){
	var inp = doc.create("input","", {"id":handle, "type":"radio", "name":"tab"});
	inp.onchange = function(){
		(tabbar.querySelector("label.selected") || {}).className = "";
		tabbar.querySelector("label[for='"+handle+"']").className = "selected";
	}
	environment.appendChild(inp);
	let file = path.split(/\//).pop();
	let modes = ["txt","html","css","less","js","php","sql","json","xml"];
	let mode = modes[modes.inArray( file.split(/\./).pop() ) || 0];
	
	var editor = doc.create("iframe","",{ "src":"/code-editor/"+mode+"?path="+path+"&handle="+handle, "class":"HTMLDesigner"});
	reauth();
	environment.appendChild(editor);
	
	var tab_btn = doc.create("label", file, {"for":handle, "title":path});
	let close_tab = doc.create("span", "&#xe907;", {"class":"tool", "title":"Close"});
	close_tab.onclick = function(){
		delete(editable[handle]);
		if(inp.checked) (tab_btn.previous() || tab_btn.next() || tabbar).click();			
		tabbar.removeChild(tab_btn);
		environment.removeChild(inp);
		environment.removeChild(editor);		
	}
	tab_btn.appendChild(close_tab);
	tabbar.appendChild(tab_btn);
	tab_btn.click();
}

/*************************************************************************/

function fs_Actions(event){
	var obj = event.target;
	var items = doc.fragment();
	if(obj.className==="folder" || obj.className==="openfolder"){
	//~~~ Create folder ~~~//
		var folder = doc.create("div", "&#xe2cc;", {"class":"context-item","data-itm":translate['create folder']});
		folder.onmousedown = function(){
			promptBox("new folder name", function(folderName){
				XHR.push({
					"Content-Type":"text/plain",
					"addressee":"/developer/actions/create-folder",
					"body":obj.dataset.path+"/"+folderName.trim().toLowerCase(),
					"onsuccess":function(response){
						root.innerHTML = "";
						root.appendChild( buildFolderTree( JSON.parse(response) ) );
					}					
				});
			}, "^[a-zA-Z0-9_-]+$");
		}
		items.appendChild(folder);
		//~~~ Create file ~~~//
		var f_create = doc.create("div", "&#xf15b;", {"class":"context-item","data-itm":translate['create file']});
		f_create.onmousedown = function(){
			promptBox("enter file name", function(fileName){
				XHR.push({
					"Content-Type":"text/plain",
					"addressee":"/developer/actions/create-file",
					"body":obj.dataset.path+"/"+fileName.trim().toLowerCase(),
					"onsuccess":function(response){
						root.innerHTML = "";
						root.appendChild( buildFolderTree( JSON.parse(response) ) );
					}					
				});
			}, "^[a-zA-Z0-9-._]+$");
		}
		items.appendChild(f_create);
		//~~~ Upload ~~~//
		var upload = doc.create("div", "&#xf07c;", {"class":"context-item","data-itm":translate['upload']});
		upload.onclick = function(){
			var inp = doc.create("input","",{type:"file",name:"files[]",accept:"*.*",multiple:"multiple"});
			inp.onchange = function(){
				XHR.uploader(inp.files, "/uploader/actions/upload?path="+obj.dataset.path, function(response){
					XHR.push({
						"Content-Type":"text/html",
						"addressee":"/developer/actions/load-folder",
						"body":obj.dataset.path,
						"onsuccess":function(response){
							var root = obj.next();
							if(root && root.className==="root"){
								root.innerHTML = response;
							}else obj.insertAfter( doc.create("div", response, {"class":"root"}) );
							obj.className = "openfolder";
						}
					});
				});
			}
			inp.click();
		}
		items.appendChild(upload);
		
		//~~~ Add to ZIP ~~~//
		var f_zip = doc.create("div", "&#xe90a;", {"class":"context-item","data-itm":translate['create archive']});
		f_zip.onmousedown = function(){
			XHR.push({
				"Content-Type":"text/plain",
				"addressee":"/developer/actions/create-zip",
				"body":obj.dataset.path,
				"onsuccess":function(response){
					root.innerHTML = "";
					root.appendChild( buildFolderTree( JSON.parse(response) ) );
					download_file(obj.dataset.path+".zip");					
				}
			});
		}
		items.appendChild(f_zip);
		
		
		items.appendChild(doc.create("hr","",{ size:1, color:"#CCC"}));
		//~~~ Paste ~~~//
		if(standby.copy){
			var f_paste = doc.create("div", "", {"class":"context-item","data-itm":translate['paste']});
			f_paste.onmousedown = function(){
				XHR.push({
					"addressee":"/developer/actions/copy",
					"body":JSON.stringify({
						"src":standby.copy,
						"dest":obj.dataset.path
					}),
					"onsuccess":function(response){
						standby[subdomain] = obj.dataset.path;
						standby.copy = null;
						root.innerHTML = "";
						root.appendChild( buildFolderTree( JSON.parse(response) ) );
					}					
				});
			}
			items.appendChild(f_paste);
		}else if(standby.move){
			var f_paste = doc.create("div", "", {"class":"context-item","data-itm":translate['paste']});
			f_paste.onmousedown = function(){
				XHR.push({
					"addressee":"/developer/actions/move",
					"body":JSON.stringify({
						"src":standby.move,
						"dest":obj.dataset.path
					}),
					"onsuccess":function(response){
						standby[subdomain] = obj.dataset.path;
						standby.move = null;
						root.innerHTML = "";
						root.appendChild( buildFolderTree( JSON.parse(response) ) );
					}					
				});
			}
			items.appendChild(f_paste);
		}
	}else if(obj.className==="file" || obj.className==="image"){
		//~~~ UnZIP if Archive ~~~//
		if(obj.textContent.split(/\./)[1]==="zip"){
			var unzip = doc.create("div", "&#xe90a;", {"class":"context-item","data-itm":translate['unzip']});
			unzip.onmousedown = function(){
				XHR.push({"Content-Type":"text/plain","addressee":"/developer/actions/unzip","body":obj.dataset.path,
					"onsuccess":function(response){
						root.innerHTML = "";
						root.appendChild( buildFolderTree( JSON.parse(response) ) );
					}					
				});
			}
			items.appendChild(unzip);
		}
		//~~~ Download ~~~//
		var f_download = doc.create("div", "&#xe905;", {"class":"context-item","data-itm":translate['download']});
		f_download.onmousedown = function(){ download_file(obj.dataset.path); }
		items.appendChild(f_download);
		items.appendChild(doc.create("hr","",{ size:1, color:"#CCC"}));
	}
	//~~~ Copy file ~~~//
	var f_copy = doc.create("div", "", {"class":"context-item","data-itm":translate['copy']});
	f_copy.onmousedown = function(){
		standby.move = null;
		standby.copy = obj.dataset.path;
	}
	items.appendChild(f_copy);
	//~~~ Cut file ~~~//
	var f_cut = doc.create("div", "", {"class":"context-item","data-itm":translate['cut']});
	f_cut.onmousedown = function(){
		standby.move = obj.dataset.path;
		standby.copy = null;
	}
	items.appendChild(f_cut);
	//~~~ Rename ~~~//
	var f_rename = doc.create("div", "", {"class":"context-item","data-itm":translate['rename']});
	f_rename.onmousedown = function(){
		var inp = doc.create("input", "", {"value":obj.textContent, "class":obj.className, "data-path":obj.dataset.path});
		inp.onchange = inp.onblur = function(){
			var path = inp.dataset.path.split(/\//);
				path[path.length-1] = inp.value.trim();
			var	newPath = path.join("/");
			
			XHR.push({"addressee":"/developer/actions/rename",
				"body":JSON.stringify({"old":inp.dataset.path, "new":newPath}),
				"onsuccess":function(response){
					obj = doc.create("span", inp.value, {"class":"file", "class":inp.className, "data-path":newPath});
					inp.parentNode.replaceChild(obj, inp);
				}					
			});
		}
		obj.parentNode.replaceChild(inp, obj);
		setTimeout(function(){ inp.select(); }, 100);
	}
	items.appendChild(f_rename);
	//~~~ Delete ~~~//
	var f_remove = doc.create("div", "", {"class":"context-item","data-itm":translate['delete']});
		f_remove.onmousedown = function(){
			confirmBox('Delete "'+obj.textContent+'"?', function(){
				XHR.push({
					"Content-Type":"text/plain",
					"addressee":"/developer/actions/remove",
					"body":obj.dataset.path,
					"onsuccess":function(response){
						if(isNaN(response)){
							alert(response);
						}else{
							var content = obj.next();
							var root = obj.parentNode;
								root.removeChild(obj);
							if(content.className==="root"){
								root.removeChild(content);
							}
						}
					}					
				});
			});
		}
		items.appendChild(f_remove);
	new showContextMenu(items, event, obj);
	return false;
}
function download_file(path){
	location.href = "/developer/actions/download?path="+path;
}

/*
function createTheme(){
	var subdomain = location.pathname.split(/\//)[2];
	if(subdomain){
		promptBox("enter theme name", function(value){
			setTimeout(function(){ 
				modalBox('{}', "themes/box", function(form){
					XHR.push({
						"Content-Type":"text/plain",
						"addressee":"/themes/actions/create-theme",
						"body":JSON.stringify({
							"template":form.theme.value,
							"name":value,
							"subdomain":subdomain
						}),
						"onsuccess":function(response){
							isNaN(response) ? alert(response) : location.pathname="themes/"+subdomain+"/"+value;
						}
					});
					return false;
				}, true); 
			},200);
		}, "^[a-zA-Z0-9-_]+$");
	}else alertBox("domain not selected");
}
*/