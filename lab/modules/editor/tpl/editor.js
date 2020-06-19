
var HTMLDesigner = function(subdoc, html, docmap){
	var edt = this;
	this.doc = subdoc;
	this.body = this.node = this.doc.querySelector("#content");
	this.toolbar =  this.doc.querySelector("#toolbar");;
	this.range = this.doc.createRange();
	this.changetimer = edt.savetimer = null;
	this.editing = false;
	this.map = docmap || null;
	this.html = html || null;
	this.save = this.onRefresh = function(){
		return false;
	}
	if(this.map){
		this.map.onclick = function(event){
			var node = event.target;
			while(node.tagName!="NAV"){
				node = node.parentNode;
			}
			var nodes = edt.map.querySelectorAll("nav."+node.className);
			for(var i=0; !node.contains(nodes[i]); i++);
			edt.node = edt.body.parentNode.querySelectorAll(node.className)[i];
			edt.body.focus();
			edt.range = edt.doc.getSelection().getRangeAt(0);
			if(event.target.className==="opening-tag"){
				//edt.range.selectNodeContents(edt.node);
				edt.range.setStartBefore(edt.node);
				edt.range.collapse(true);
			}else if(event.target.className==="closing-tag"){
				edt.range.setStartAfter(edt.node);
				edt.range.collapse(false);
			}else if(event.target.className==="text-node"){
				edt.range.selectNodeContents(edt.node.childNodes[event.target.dataset.node]);
			}
			edt.setSelection();
			edt.sync();
			edt.node.scrollIntoView(false);
		}
	}
	this.refresh = function(){
		edt.range = edt.doc.getSelection().getRangeAt(0);
		edt.node = edt.range.commonAncestorContainer.nodeType==3 ? edt.range.commonAncestorContainer.parentNode : edt.range.commonAncestorContainer;
		edt.onRefresh();
	}
	this.doc.sync = this.sync = function(node){
		clearTimeout(edt.changetimer);
		edt.changetimer = setTimeout(function(){
			if(edt.html) html.session.setValue(edt.getValue().trim());
			if(edt.map){
				edt.map.innerHTML = edt.buildDocumentMap();
				edt.map.querySelector("#selected-range").scrollIntoView(false);
				edt.map.scrollBy(0, 100);
			}
			if(edt.editing){
				clearTimeout(edt.savetimer);
				edt.savetimer = setTimeout(edt.autosave, 6000);
			}
			edt.editing = false;
		},2000);
	}
	this.autosave = function(){
		if(edt.editing){
			edt.savetimer = setTimeout(edt.autosave, 2000);
		}else edt.save();
	}
	this.buildDocumentMap = function(node){
		node = node || edt.body;
		var tName = node.nodeName.toLowerCase();
		var nType = edt.range.commonAncestorContainer.nodeType;
		var res = "<nav class='"+tName+"' "+(((nType===1) && !node.compareDocumentPosition(edt.node)) ? "id='selected-range'" : "")+"><span class='opening-tag'>"+tName+"</span>";
		var childs = node.childNodes;
		if(childs.length && node.isContentEditable){
			res += "<div class='root'>";
			for(var i=0; i<childs.length; i++){
				if(childs[i].nodeType===1){
					res += this.buildDocumentMap(childs[i]);
				}else if(childs[i].nodeType===3){
					res += "<span class='text-node' "+(((nType===3) && !childs[i].compareDocumentPosition(edt.range.commonAncestorContainer)) ? "id='selected-range'" : "")+" data-node='"+i+"'>"+childs[i].nodeValue.substring(0,10).trim()+"...</span>";
				}
			}
			res += "</div>";
		}
		res += " <span class='closing-tag'>"+tName+"</span></nav>";
		return res;
	}
	this.doc.setSelection = this.setSelection = function(){
		setTimeout(function(){
			var scroll = edt.body.parentNode.scrollTop;
			edt.body.focus();
			edt.body.parentNode.scrollTop = scroll;
			var selection=edt.doc.getSelection();
			selection.removeAllRanges();
			selection.addRange(edt.range);
		},0);
	}
	this.addCSSFile = function(path){
		this.doc.querySelector("#head").appendChild(edt.doc.create("link","",{rel:"stylesheet",type:"text/css",href:path}));
	}
	this.addJSFile = function(path){
		this.doc.querySelector("#head").appendChild(edt.doc.create("script","",{type:"text/javascript",src:path}));
	}
	this.setValue = this.doc.setValue = function(value){
		edt.body.innerHTML = value;
		if(edt.map){ edt.map.innerHTML = edt.buildDocumentMap(); }
	}
	this.getValue = this.doc.getValue = function(){
		return edt.body.innerHTML;
	}
	this.doc.properties = function(){
		var box = modalBox('{}', "editor/propertiesbox/"+edt.node.nodeName, function(form){
			cells=box.body.querySelectorAll("table>tbody>tr>td");
			for(var i=0; i<cells.length; i+=2){
				if(cells[i+1].textContent.trim()){
					edt.node.setAttribute(cells[i].textContent, cells[i+1].textContent);
				}else edt.node.removeAttribute(cells[i].textContent);
			}
			boxList.drop(boxList.onFocus);
			edt.editing = true;
		}, false);
		box.onopen = function(){
			var tbody = box.body.querySelector("tbody");
			var keys = tbody.querySelectorAll("tbody>tr>td");
			for(var i=0; i<keys.length; i+=2){
				keys[i+1].textContent = edt.node.getAttribute(keys[i].textContent);
			}
			for(var key in edt.node.dataset){
				tbody.appendChild(doc.create("tr","<td>"+key+"</td><td contenteditable='true'>"+edt.node.dataset[key]+"</td>", {"bgcolor":"#FFFFFF"}));
			}
		}
	}
	this.doc.spellCheck = function(){
		edt.body.spellcheck = edt.body.spellcheck ? false : true;
	}
	this.checkfont = function(){
		edt.toolbar.reset();
		edt.toolbar.fsize.value = edt.node.getCss("font-size");
		edt.toolbar.family.value = edt.node.getCss("font-family");
	}
	this.doc.drop = function(){
		if(!edt.node.isContentEditable){
			edt.node.parentNode.removeChild(edt.node);
		}else if(!edt.range.startContainer.compareDocumentPosition(edt.range.endContainer) && edt.range.commonAncestorContainer.id!="content"){
			edt.node.outerHTML = edt.node.innerHTML;
		}
		edt.setSelection();
		edt.refresh();
		edt.editing = true;
		edt.sync();
	}
	this.doc.insertTag=function(command, tag){
		if(edt.node.nodeName!=tag){
			edt.doc.execCommand(command, false, arguments[2]);
			edt.setSelection();
			setTimeout(function(){
				edt.refresh();
				edt.editing = true;
				edt.sync();
			}, 10);
		}
	}
	this.doc.formatblock = function(tag){
		edt.setSelection();
		edt.doc.execCommand("formatblock", false, tag);
		edt.refresh();
		edt.editing = true;
		edt.sync();
	}
	this.doc.createlink = function(){
		modalBox('{}', "editor/linkbox", function(form){
			var node = edt.doc.create("a", edt.range.toString(), {href:form.url.value,target:form.target.value});
			boxList[form.id].drop();
			edt.range.surroundContents(node);
			edt.range.setStartAfter(node);
			edt.setSelection();
			edt.editing = true;
			edt.sync();			
		}, false);
	}
	this.doc.freeTag=function(tName){
		promptBox("tag name", function(tName){
			var node = edt.doc.create(tName);
			edt.range.surroundContents(node);
			edt.range.setStartAfter(node);
			edt.setSelection();
			edt.editing = true;
			edt.sync();
		});
	}
	this.doc.breakline=function(){
		edt.range = edt.doc.getSelection().getRangeAt(0);
		var nod = edt.range.commonAncestorContainer;
		if(nod.id==="content"){
			var p = doc.create("p");
			nod.appendChild(p);
			edt.node.scrollIntoView(true);
		}else{
			while(nod.parentNode.id!="content"){
				nod = nod.parentNode;
				edt.range.setEndAfter(nod);
			}
			var p = doc.create("p", edt.range.extractContents().textContent);
			nod.insertAfter(p);
		}
		edt.range.selectNodeContents(p);
		edt.range.collapse(true);
		edt.refresh();
		edt.editing = true;
		edt.sync();
	}
	this.doc.list=function(type){
		edt.doc.execCommand(type);
		edt.editing = true;
		edt.sync();
	}
	this.doc.setProperty=function(property, value){
		edt.node.setAttribute(property,value);
		edt.setSelection();
	}
	this.doc.setCSSRule = function(property, value){
		if(edt.range.collapsed || (edt.range.toString() == edt.node.textContent)){
			edt.node.style[property]=value;
		}else edt.range.surroundContents(edt.doc.create("span",null,{"style":property+":"+value}));
		edt.setSelection();
	}
	this.doc.insertFigure=function(src){
		edt.setSelection();
		setTimeout(function(){
			edt.doc.execCommand("insertHTML",false, "<figure><img src='"+src+"'><figcaption class='op-center'></figure>");
			edt.editing = true;
			edt.sync();
		}, 10);
	}
	this.doc.insertImage=function(src){
		edt.setSelection();
		setTimeout(function(){
			edt.doc.execCommand("insertImage", false, src);
			edt.editing = true;
			edt.sync();
		}, 10);
	}
	this.doc.imgBox=function(){
		var box = new Box('{}', "editor/actions/module/image", false);
		box.open = edt.doc.insertImage;
		box.onsubmit = function(form){
			var data = box.getModuleContent(form);
			if(data){
				edt.setSelection();
				setTimeout(function(){
					edt.doc.execCommand("insertHTML",false, data);
					edt.editing = true;
					edt.sync();
					boxList[box.window.id].drop();
				}, 10);
			}
		}
	}
	this.doc.setFont=function(value){
		edt.node.style.fontFamily = value;
		edt.setSelection();
	}
	this.doc.setFontSize=function(value){
		edt.node.style.fontSize = parseInt(value)+"px";
	}
	this.doc.pattern=function(){
		var pedt;
		var pattern = new Box('{}', "patterns/html_patternbox", false);
		pattern.onopen=function(){
			pedt = ace.edit(pattern.body.querySelector(".environment"));
			pedt.setTheme("ace/theme/twilight");
			pedt.getSession().setMode("ace/mode/html");
			pedt.setShowInvisibles(false);
			pedt.setShowPrintMargin(false);
			pedt.focus();
			pedt.resize();
		}
		pattern.onsubmit = function(form){
			XHR.push({
				"Content-Type":"text/html",
				"addressee":"/patterns/actions/save-pattern/"+form.pname.value+"?path="+form.path.value,
				"body":pedt.getValue(),
				"onsuccess":function(response){
					pattern.body.querySelector(".leftbar").innerHTML = response;
				}
			});
		}
		pattern.add = function(){
			edt.setSelection();
			setTimeout(function(){
				edt.doc.execCommand("insertHTML", false, pedt.getValue());
				boxList[pattern.window.id].drop();
				edt.refresh();
				edt.editing = true;
				edt.sync();
			}, 10);
		}
	}
	this.body.onpaste=function(event){
		
		event.preventDefault();
		var items=event.clipboardData.getData("text").split(/\n+/);
		var data = [];
		for(var i=0; i<items.length; i++){
			if(items[i].trim()) data.push("<p>"+items[i].trim()+"</p>");
		}
		edt.doc.execCommand("insertHTML", false, data.join("\n"));
		edt.editing = true;
		edt.sync();
		
	}
	this.doc.module = function(modName){
		var box = new Box('{}', "editor/actions/module/"+modName, false);
		box.onsubmit = function(form){
			var data = box.getModuleContent(form, edt.doc);
			if(data){
				edt.setSelection();
				setTimeout(function(){
					edt.doc.execCommand("insertHTML",false, data);
					edt.editing = true;
					edt.sync();
					boxList[box.window.id].drop();
				}, 10);
			}
		}
	}
	this.doc.modules = function(){
		var modlist = modalBox('{}', "editor/modules", function(form){ return false; }, false);
		modlist.onopen = function(){
			modlist.body.onchange = function(event){
				var box = new Box('{}', 'editor/actions/module/'+event.target.value, false);
				box.onsubmit = function(form){
					var data = box.getModuleContent(form, edt.doc);
					if(data){
						edt.setSelection();
						setTimeout(function(){
							edt.doc.execCommand("insertHTML",false, data);
							edt.editing = true;
							edt.sync();
							boxList[box.window.id].drop();
						}, 10);
					}
				}
				boxList.drop(boxList.onFocus);
				return false;
			}
		}
	}
	
	this.body.onkeydown=function(event){
		if(event.ctrlKey){
			switch(event.keyCode){
				case 83:														// Key "s" - Save
					event.preventDefault();
					edt.save();
				break;
				case 66: 														// Key "b" - bold
					event.preventDefault();
					edt.doc.insertTag('bold', 'B');
				break;
				case 73: 														// Key "i" - italic
					event.preventDefault();
					edt.doc.insertTag('italic','I');
				break;
				case 85: 														// Key "u" - underline
					event.preventDefault();
					edt.doc.insertTag('underline','U')
				break;
				case 13: 														// Key "Enter" - paragraph
					event.preventDefault();
					edt.doc.formatblock('p');
				break;
				default:break;
			}
		}else if(event.altKey){
			event.preventDefault();
		}else if(event.shiftKey){
			switch(event.keyCode){
				case 13: 														// Key "Enter" - breakline
					edt.editing = true;
					setTimeout(edt.sync, 100);
				break;
				default:break;
			}
		}else{
			edt.editing = true;
			switch(event.keyCode){
				case 13: 														// Key "Enter" - breakline
					event.preventDefault();
					edt.doc.breakline();
				break;
				case 46: 														// Key "Delete"
					if(edt.range.commonAncestorContainer.nodeType===1){
						event.preventDefault();
						edt.doc.drop();
					}					
				break;
				default:
					edt.body.onkeyup = edt.body.onclick;
				break;
			}
		}
	}
	this.body.onclick = function(event){
		edt.node = event.target;
		if(!edt.node.isContentEditable){
			while(!edt.node.parentNode.isContentEditable){
				edt.node = edt.node.parentNode;
			}
		}
		edt.range = edt.doc.getSelection().getRangeAt(0);
		if(edt.map){
			edt.map.innerHTML = edt.buildDocumentMap();
			edt.map.querySelector("#selected-range").scrollIntoView(false);
			edt.map.scrollBy(0, 100);
		}
		if(event.type==="click"){
			if(edt.html){
				edt.html.find(event.target.outerHTML,{});
			}
		}else if(event.type==="keyup"){
			if(edt.editing){
				clearTimeout(edt.savetimer);
				edt.savetimer = setTimeout(edt.autosave, 6000);
			}
			edt.editing = false;
		}
		edt.checkfont();
		edt.body.onkeyup = null;
	}
}