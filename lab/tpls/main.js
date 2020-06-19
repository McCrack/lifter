
/* Standby *********************************************************/

var SECTION = location.pathname.split(/\//)[1] || "welcome";

//window.localStorage.removeItem(SECTION);

var Standby = (window.localStorage[SECTION] || "undefined").jsonToObj() || {
	"bodymode":"leftmode",
	"leftbar":"modules-list",
	"rightbar":"manual"
};
var standby = new Proxy(Standby,{
	get(target, name){ return target[name] || null; },
	set(target, name, value){
		target[name] = value;
		window.localStorage[SECTION] = JSON.stringify(Standby);
	}
});

/* Change BODY mode ************************************************/

function changeBodyMode(){
	if(doc.body.className==="leftmode"){
		doc.body.className = "rightmode";
	}else doc.body.className = "leftmode";
	standby.bodymode = doc.body.className;
}

/* Leftbar *********************************************************/

function openTab(obj, bar){
	if(obj.className==="tool"){
		var tabs = doc.querySelectorAll("#"+bar+" .tab");
		for(var i=tabs.length; i--;){
			if(tabs[i].id===obj.dataset.tab){
				tabs[i].style.display = "block";
			}else tabs[i].style.display = "none";
		}
		if(obj.dataset.tab!="modules-list") standby[bar] = obj.dataset.tab;
	}
}
function executeModule(obj){
	if(obj.className==="tree-root-item"){
		var mode = obj.dataset.mode || "page";
		switch(mode){
			case "page": window.location.pathname = obj.dataset.href;
			break;
			case "box": new Box("{}", ".."+obj.dataset.href+"/box", true);
			break;
			default: alertBox("unknow mode");
			break;
		}
	}
}

/* Multilanguage *****************************************************************************************************/

var wordlist = {
	names:[],
	fragment:function(owner){
		owner = owner || document;
		let nodes = owner.querySelectorAll("*[data-translate]");
		for(let i=nodes.length; i--;){
			let property = nodes[i].dataset.translate;
			nodes[i][property] = this[nodes[i][property]];
		}
	},
	add:function(dictionary, name){
		if(name){
			if(isNaN( this.names.inArray(name) )){
				this.names.push(name);
			}else return false;
		}
		Object.assign(this, (dictionary || {}));
	}
}
var translate = new Proxy(wordlist, {
	get(target, key){ return target[key] || key; }
});

/* Settings ********************************************************/

function settingsBox(module){
	module = module || location.pathname.split(/\//)[1];
	var box = new Box("{}", "settings/box/"+module, true);
	box.onsubmit = function(form){
		XHR.push({
			"Content-Type":"application/json",
			"addressee":"/settings/actions/save/"+location.hostname.split(/\./)[0]+"/"+module, 
			"body":settingsToJson(form),
			"onsuccess":function(response){
				box.drop();
			}
		});
		return false;
	}
}
function settingsToJson(owner){
	owner = owner || doc;
	var key, cells, section, settings={};
	cells = owner.querySelectorAll("table.settings tr>td");
	for(var i=0; i<cells.length; i++){
		if(cells[i].className==="section"){
			section = cells[i].dataset.section || cells[i].textContent.trim();
			settings[section] = {};
		}else{
			key = cells[i].dataset.key || cells[i].textContent.trim();
			settings[section][key] = {
				"type":cells[i].parentNode.dataset.type || prompt('Please enter type of parameter "'+key+'" (enum, set or string)', "srting") || "string",
				"value":cells[++i].textContent.trim(),
				"valid":cells[++i].textContent.trim().split(/,\s*/)
			};
			if(isNotValid(
				settings[section][key]['value'],
				settings[section][key]['type'],
				settings[section][key]['valid']
			)) return false;
		}
	}
	return JSON.encode(settings);
}

function settingsValidator(settings){
		
	if(settings['type']==="enum" && (settings['valid'].indexOf(settings['value'])<0)){
		alertBox('"'+settings['value']+'" is not valid value');
		return false;
	}else if(settings['type']==="set"){
		settings['value'] = settings['value'].split(/,\s*/);
		for(var j=settings['value'].length; j--;){
			if(settings['valid'].indexOf(settings['value'][j])<0){
				alertBox('"'+settings['value'][j]+'" is not valid value');
				return false;
			}
		}
		settings['value'] = settings['value'].join(",")
	}
	return true;
}


/* Table rows ******************************************************/

function addRow(btn){
	var row = btn.ancestor("TR");
	var cells = row.querySelectorAll("td").length;
	var newRow = doc.create("tr", "<th><span title='Add row' class='tool' onclick='addRow(this)'>&#xe908;</span></th>", {"bgcolor":"#DEF"});
	for(var i=cells; i--;){
		newRow.appendChild(doc.create("td", "", {"contenteditable":"true"}));
	}
	newRow.appendChild(doc.create("th", "<span title='Drop row' class='tool red' onclick='deleteRow(this)'>&#xe907;</span>", {"bgcolor":"white"}));
	row.insertAfter(newRow);
}
function deleteRow(btn, onDelete){
	onDelete = onDelete || function(){ return null; };
	var row = btn.ancestor("TR");
		row.parentNode.removeChild(row);
	onDelete();
}

/* Patterns ********************************************************/

function showPattern(json, onapply){
	if(json){
		onapply = onapply || function(){};
		var edt;
		var pattern = new Box('{}', "patterns/json_patternbox/"+onapply, true);
		pattern.onopen=function(){
			edt = ace.edit(pattern.body.querySelector(".environment"));
			edt.setTheme("ace/theme/chrome");
			edt.getSession().setMode("ace/mode/json");
			edt.setShowInvisibles(true);
			edt.setShowPrintMargin(false);
			edt.setValue(json);
			edt.focus();
			edt.resize();
		}
		pattern.onsubmit = function(form){
			XHR.push({
				"Content-Type":"application/json",
				"addressee":"/patterns/actions/save-pattern/"+form.pname.value+"?path="+form.path.value, 
				"body":edt.getValue(),
				"onsuccess":function(response){
					pattern.body.querySelector(".leftbar").innerHTML = response;
				}
			});
		}
		pattern.add = function(){
			eval(onapply+"(edt.getValue())");
			pattern.drop();
		}
	}
}

/* Wordlist ********************************************************/

function showWordlistBox(str){
	if(typeof str!="string"){
		if(typeof str!="object"){
			return false;
		}else str = str.textContent;
	}
	var box = new Box('{"key":"'+str+'"}', "wordlist/box", true);
	return false;
}


/* Context menu ************************************************************************/

var showContextMenu = function(items, event){
	var context = this;
	context.drop_menu_tout;
	let top = (event.clientY-16);
	let left = (event.clientX+10);
	context.menu = document.create("div", items, {"class":"context-menu", "style":"top:"+top+"px;left:"+left+"px"});
	document.body.appendChild(context.menu);
	context.menu.onmouseover=function(){ clearTimeout(context.drop_menu_tout); }
	
	document.onclick = function(){
		document.body.removeChild(context.menu);
		document.onclick = null;
	}
	context.menu.onmouseout = function(){
		context.drop_menu_tout = setTimeout(function(){
			document.onclick();
		}, 300);
	}
}

/* Alerts ******************************************************************************/

var alertBox = function(msg){
	if(isNaN( translate.names.inArray("alerts") )){
		XHR.push({
			"addressee":"/xhr/wordlist?d=alerts",
			"onsuccess":function(response){
				eval(response);
			}
		});
	}
	var box = modalBox('{}', "xhr/boxfather/alert", function(){
		return false;
	}, false);
	box.onopen = function(){
		box.body.querySelector("h3").textContent = translate[msg];
	}
}
var confirmBox = function(msg, onsubmit){
	if(isNaN( translate.names.inArray("alerts") )){
		XHR.push({
			"addressee":"/xhr/wordlist?d=alerts",
			"onsuccess":function(response){
				eval(response);
			}
		});
	}
	var box = modalBox('{}', "xhr/boxfather/confbox", function(){
		onsubmit();
		box.drop();
		return false;
	}, false);
	box.onopen = function(){
		box.body.querySelector("h3").textContent = translate[msg];
	}
}
var promptBox = function(msg, onsubmit, pattern, def){
	if(isNaN( translate.names.inArray("alerts") )){
		XHR.push({
			"addressee":"/xhr/wordlist?d=alerts",
			"onsuccess":function(response){
				eval(response);
			}
		});
	}
	pattern = pattern || "";
	var box = modalBox('{}', "xhr/boxfather/prompt", function(form){
		onsubmit(form.field.value);
		box.drop();
	}, false);
	box.onopen = function(){
		box.body.querySelector("h3").textContent = translate[msg];
		box.window.field.pattern = pattern || ".+";
		box.window.field.value = def || "";
		box.window.field.focus();
	}
}