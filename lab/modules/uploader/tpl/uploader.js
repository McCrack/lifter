var environment, root, pathline;

/* Initialization ****************************************/

standby.copylist = (standby.copylist || "").jsonToObj() || [];
standby.movelist = (standby.movelist || "").jsonToObj() || [];

/*********************************************************/

function reloadExplorer(path){
	XHR.push({
		"addressee":"/uploader/actions/tree",
		"body":path,
		"onsuccess":function(response){
			let obj = JSON.parse(response);
			pathline.value = path.split(/\//).slice(1).join("/");
			standby[standby.subdomain] = path;
			root.innerHTML = "";
			root.appendChild( buildFolderTree( obj ) );
			if(FOLDER_CONTENT){
				environment.innerHTML = "";
				showCurrentFolder( getCurrentFolder(obj) );
			}
			environment.dataset.path = path;
		}	
	});
}
function getCurrentFolder(obj){
	for(var key in obj){
		if(!obj.hasOwnProperty(key)) continue;
		if(obj[key]['type']==="openfolder"){
			return getCurrentFolder( obj[key]['content'] );
		}
	}
	return obj;
}
function buildFolderTree(obj){
	let root = doc.create("div", "", {"class":"root"});
	for(var key in obj){
		if(!obj.hasOwnProperty(key)) continue;
		let type = obj[key]['type'].split(/\//)[0];
		switch(type){
			default: if(SHOWFILES){
				if(type!="image"){
					//if(ONLY_IMAGES) continue;
					if(type!="text") type = "application";
				}
				root.appendChild( doc.create("span", key, {"class":"file", "data-type":type, "data-path":obj[key]['path']}) );
			} break;
			case "folder":
			case "openfolder":
				root.appendChild( doc.create("span", key, {"class":obj[key]['type'], "data-path":obj[key]['path'], "data-type":obj[key]['type']}) );
				root.appendChild(buildFolderTree(obj[key]['content']), {"class":"root"});
			break;				
		}
	}
	return root;
}

function showCurrentFolder(obj){
	let types = ["html","text","zip","pdf","audio","video"];
	for(var key in obj){
		if(!obj.hasOwnProperty(key)) continue;
		if(obj[key]['type'] === "folder"){
			var context = "folder";
			var preview = "/images/mime/folder.png";
			var type = ["folder"];
		}else{
			var context = "file";
			var type = obj[key]['type'].split(/\//);
			if(type[0] === "image"){
				var preview = pathToURL(obj[key]['path']);
			}else if(ONLY_IMAGES){
				continue;
			}else if(types.inArray(type[1])){
				var preview = "/images/mime/"+type[1]+".png";
			}else if(types.inArray(type[0])){
				var preview = "/images/mime/"+type[0]+".png";
			}else var preview = "/images/mime/application.png";
		}
		let sticker = (MULTISELECT) ? 
			doc.create("label", "<input type='checkbox' data-type='"+type[0]+"' data-mime='"+type[1]+"'>", {"class":"file-sticker"}) : 
			doc.create("label", "<input name='preview' type='radio' data-type='"+type[0]+"' data-mime='"+type[1]+"'>", {"class":"file-sticker"});
		let figure = doc.create("figure", "<img src='"+preview+"'>"+key, {"data-type":context, "data-path":obj[key]['path']});
		sticker.appendChild(figure);
		environment.appendChild(sticker);
	}
}

/*************************************************************************/

var outFolder = function(){
	let path = pathline.value.split(/\//);
	if(path.length>2){
		reloadExplorer("../"+path.slice(0, -1).join("/"));
	}else{
		alertBox("This is root");
		pathline.value = standby.subdomain+"/data";
	}
}
function Open(event){
	var obj = event.target;
	switch(obj.nodeName){
		case "IMG": obj = obj.parentNode; break;
		case "LABEL": obj = obj.last(); break;
		default: break;
	}
	let mode = {"image":"file","text":"file","application":"file"}[obj.dataset.type] || obj.dataset.type;
	if(mode==="folder" || mode==="openfolder"){
		reloadExplorer(obj.dataset.path);
	}else if(mode==="file")	openFile(obj.dataset.path);
}

/*************************************************************************/

function createContextMenu(event){
	let obj = event.target;
	switch(event.target.nodeName){
		case "FIGURE": obj = event.target; break;
		case "FORM": obj = event.target; break;
		case "IMG": obj = event.target.parentNode; break;
		case "LABEL": obj = event.target.last(); break;
		default: return false;
	}
	new Context(obj, event);
	return false;
}

var Context = function(obj, event){
	var context = this;
	var items = doc.fragment();
	context.openfolder = function(){
		//~~~ Paste ~~~//
		if(standby.copylist.length || standby.movelist.length){
			var f_paste = doc.create("div", "", {"class":"context-item","data-itm":translate['paste']});
			f_paste.onmousedown = function(){ pasteFiles(obj); }
			items.appendChild(f_paste);
			items.appendChild(doc.create("hr","",{ size:1, color:"#CCC"}));
		}
		//~~~ Upload ~~~//
		var upload = doc.create("div", "&#xf07c;", {"class":"context-item", "data-itm":translate['upload']});
		upload.onclick = function(){ uploadFiles(obj.dataset.path); }
		items.appendChild(upload);
		//~~~ Import images ~~~//
		var f_import = doc.create("div", "&#xe909;", {"class":"context-item", "data-itm":translate['import images']});
		f_import.onmousedown = importImagesDialog;
		items.appendChild(f_import);
		//~~~ Create folder ~~~//
		var folder = doc.create("div", "&#xe2cc;", {"class":"context-item", "data-itm":translate['create folder']});
		folder.onmousedown = function(){ createFolder(); }
		items.appendChild(folder);
		
		new showContextMenu(items, event, obj);
	}
	context.folder = function(){
		//~~~ Add to ZIP ~~~//
		var f_zip = doc.create("div", "&#xe90a;", {"class":"context-item","data-itm":translate['create archive']});
		f_zip.onmousedown = function(){
			XHR.push({
				"Content-Type":"text/plain",
				"addressee":"/uploader/actions/create-zip",
				"body":obj.dataset.path,
				"onsuccess":function(response){
					downloadFile(obj.dataset.path+".zip");
					let folder = JSON.parse(response);
					if(FOLDER_CONTENT){
						environment.innerHTML = "";
						showCurrentFolder( folder );
					}
				}
			});
		}
		items.appendChild(f_zip);
		items.appendChild(doc.create("hr","",{ size:1, color:"#CCC"}));
		
		context.mainitems();
		
		new showContextMenu(items, event, obj);
	}
	context.file = function(){
		//~~~ UnZIP if Archive ~~~//
		if(obj.dataset.path.split(/\./).slice(-1).toString()==="zip"){
			var unzip = doc.create("div", "&#xe90a;", {"class":"context-item","data-itm":translate['unzip']});
			unzip.onmousedown = function(){
				XHR.push({
					"Content-Type":"text/plain",
					"addressee":"/uploader/actions/unzip",
					"body":obj.dataset.path,
					"onsuccess":function(response){
						let obj = JSON.parse(response);
						root.innerHTML = "";
						root.appendChild( buildFolderTree( obj ) );
						if(FOLDER_CONTENT){
							environment.innerHTML = "";
							showCurrentFolder( getCurrentFolder(obj) );
						}
					}
				});
			}
			items.appendChild(unzip);
		}
		//~~~ Download ~~~//
		var f_download = doc.create("div", "&#xe905;", {"class":"context-item","data-itm":translate['download']});
		f_download.onmousedown = function(){ downloadFile(obj.dataset.path); }
		items.appendChild(f_download);
		items.appendChild(doc.create("hr","",{ size:1, color:"#CCC"}));
	
		context.mainitems();
	
		new showContextMenu(items, event, obj);
	}
	context.mainitems = function(){
		//~~~ Copy files ~~~//
		var f_copy = doc.create("div", "", {"class":"context-item","data-itm":translate['copy']});
		f_copy.onmousedown = function(){
			let filelist = environment.querySelectorAll("input:checked+figure");
			let copylist = [];
			standby.movelist = [];
			if(filelist.length){
				for(var i=filelist.length; i--;){
					copylist.push(filelist[i].dataset.path);
				}
			}else copylist.push(obj.dataset.path);
			standby.copylist = copylist;
		}
		items.appendChild(f_copy);
		//~~~ Cut files ~~~//
		var f_cut = doc.create("div", "", {"class":"context-item","data-itm":translate['cut']});
		f_cut.onmousedown = function(){
			let filelist = environment.querySelectorAll("input:checked+figure");
			let movelist = [];
			standby.copylist = [];
			if(filelist.length){
				for(var i=filelist.length; i--;){
					movelist.push(filelist[i].dataset.path);
				}
			}else movelist.push(obj.dataset.path);
			standby.movelist = movelist;
		}
		items.appendChild(f_cut);
		//~~~ Rename ~~~//
		var f_rename = doc.create("div", "", {"class":"context-item","data-itm":translate['rename']});
		f_rename.onmousedown = function(){ renameElements(obj); }
		items.appendChild(f_rename);
		//~~~ Delete ~~~//
		var f_remove = doc.create("div", "", {"class":"context-item","data-itm":translate['delete']});
		f_remove.onmousedown = function(){ removeElements(obj); }
		items.appendChild(f_remove);
	}
	let mode = {"image":"file","text":"file","application":"file"}[obj.dataset.type] || obj.dataset.type;
	context[mode]();
}

/* Actions ***************************************************/

function uploadFiles(path){
	var inp = doc.create("input","",{type:"file",name:"files[]",accept:"*.*",multiple:"multiple"});
	inp.onchange = function(){
		XHR.uploader(inp.files, "/uploader/actions/upload?path="+path, function(response){
			XHR.push({
				"addressee":"/uploader/actions/get-folder",
				"body":path,
				"onsuccess":function(response){
					let obj = JSON.parse(response);
					if(FOLDER_CONTENT){
						environment.innerHTML = "";
						showCurrentFolder( obj );
					}else if(SHOWFILES){
						reloadExplorer(path);
					}
				}	
			});
		});
	}
	inp.click();
}
function downloadFile(path){
	location.href = "/uploader/actions/download?path="+path;
}
function createFolder(){
	promptBox("new folder name", function(folderName){
		folderName = folderName.trim();
		var path = environment.dataset.path+"/"+folderName.toLowerCase();
		XHR.push({
			"Content-Type":"text/plain",
			"addressee":"/uploader/actions/create-folder",
			"body":path,
			"onsuccess":function(response){
				let obj = JSON.parse(response);
				if(FOLDER_CONTENT){
					environment.innerHTML = "";
					showCurrentFolder( obj );
				}else reloadExplorer(path)
			}					
		});
	}, "^[a-zA-Z0-9_-]+$");
}
function renameElements(obj){
	promptBox("new name", function(value){
		value = value.trim();
		var newpath = (obj.dataset.path.split(/\//).slice(0, -1).join("/"))+"/"+value;
		XHR.push({"addressee":"/uploader/actions/rename",
			"body":JSON.stringify({
				"old":obj.dataset.path,
				"new":newpath
			}),
			"onsuccess":function(response){
				if(isNaN(response)){
					alert(response);
					return false;
				}
				obj.lastChild.nodeValue = value;
				obj.dataset.path = newpath;
			}					
		});
	}, "^[a-zA-Z0-9._-]+$", obj.textContent.trim());
}
function removeElements(obj){
	let list = environment.querySelectorAll("input:checked+figure");
	let filelist = [];
	if(list.length){
		for(let i=list.length; i--;) filelist.push(list[i].dataset.path);
	}else if(obj){
		filelist.push(obj.dataset.path);
	}else alertBox("elements not selected");

	confirmBox("delete selected", function(){
		environment.reset();
		XHR.push({
			"addressee":"/uploader/actions/remove",
			"body":JSON.encode(filelist),
			"onsuccess":function(response){
				let obj = JSON.parse(response);
				root.innerHTML = "";
				root.appendChild( buildFolderTree( obj ) );
				if(FOLDER_CONTENT){
					environment.innerHTML = "";
					showCurrentFolder( getCurrentFolder(obj) );
				}
			}					
		});
	});
}

function pasteFiles(obj, refresh){
	if(standby.movelist.length){
		var addressee = "/uploader/actions/move";
		var body = JSON.encode({
			src : standby.movelist,
			dest: obj.dataset.path
		});
	}else if(standby.copylist.length){
		var addressee = "/uploader/actions/copy";
		var body = JSON.encode({
			src : standby.copylist,
			dest: obj.dataset.path
		});
	}
	XHR.push({
		"addressee":addressee,
		"body":body,
		"onsuccess":function(response){
			let obj = JSON.parse(response);
			root.innerHTML = "";
			root.appendChild( buildFolderTree( obj ) );
			if(FOLDER_CONTENT){
				environment.innerHTML = "";
				showCurrentFolder( getCurrentFolder(obj) );
			}
			standby.copylist = [];
			standby.movelist = [];
		}					
	});
}
function importImagesDialog(obj){
	obj = obj || environment;
	promptBox("web page address", function(url){
		var box = new Box('{"url":"'+url+'"}', "uploader/parserbox", true);
		box.onopen = function(){ setTimeout(imgSizeFilter, 800); }
		box.onsubmit = function(form){
			var imgList = [];
			var imgs = box.body.querySelectorAll("input");
			for(var i=imgs.length; i--;){
				if(imgs[i].checked){
					imgList.push(imgs[i].value);
				}
			}
			XHR.push({
				"addressee":"/uploader/actions/import",
				"body":JSON.encode({
					"dest":obj.dataset.path,
					"sourcelist":imgList
				}),
				"onsuccess":function(response){
					box.drop();
					XHR.push({
						"addressee":"/uploader/actions/get-folder",
						"body":environment.dataset.path,
						"onsuccess":function(response){
							let obj = JSON.parse(response);
							if(FOLDER_CONTENT){
								environment.innerHTML = "";
								showCurrentFolder( obj );
							}
						}	
					});
				}
			});
			return false;
		}
	});
}
function imgSizeFilter(){
	var box = boxList[boxList.onFocus];
	var val = parseInt(box.window.size.value);
	var rule = box.window.rule.value;
	var imgs = box.body.querySelectorAll("img");
	for(var i=imgs.length; i--;){
		var sticker = imgs[i].parent(2);
		if(imgs[i][rule] < val){
			sticker.style.display="none";
			sticker.querySelector("input").checked = false;
		}else sticker.style.display="inline-block";
	}
}
function pathToURL(path){
let host = location.hostname.split(/\./);
	host[0] = standby.subdomain;	
	return "https://"+host.join(".")+"/"+path.split(/\//).slice(2).join("/");
}