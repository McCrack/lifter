
/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	standby.leftbar = "sitemap";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#explorer").scrollTop = standby.mapScrollTop || 0;
}

/*********************************************************/


function reloadTree(lang){
	var path = location.pathname.split(/\//);
		path[2] = lang;
	XHR.push({
		"addressee":"/sitemap/actions/reload-tree/"+lang,
		"onsuccess":function(response){
			doc.querySelector("#explorer>.root").innerHTML = response;
			window.history.pushState("", "tree", path.join("/"));
		}
	});
}
function reloadMaterial(lang){
	var path = location.pathname.split(/\//);
	if(path[2]){	
		path[3] = lang;
		location.pathname = path.join("/");
	}
}

/*******************************************/

function saveContent(PageID){
	XHR.push({
		"Content-Type":"text/html",
		"addressee":"/sitemap/actions/save-content/"+PageID, 
		"body":editor.getValue()
	});
}

/*******************************************/

function jsontooptions(json){
	var sobj = JSON.parse(json);
	var rows="",color=16777215;
	for(var key in sobj){
		rows += 
		"<tr bgcolor='#"+((color^=2037018).toString(16))+"'>"+
		"<th bgcolor='white'><span title='Add row' class='tool' onclick='addRow(this)'>&#xe908;</span></th>"+
		"<td align='center' contenteditable='true'>"+key+"</td>"+
		"<td contenteditable='true'>"+sobj[key]+"</td>"+
		"<th bgcolor='white'><span title='Delete row' class='tool red' onclick='deleteRow(this)'>&#xe907;</span></th>"+
		"</tr>";
	}
	doc.querySelector("#options>tbody").innerHTML = rows;
}
function patternWithoutValidate(owner){
	owner = owner || doc;
	var key, cells, options={};
	cells = owner.querySelectorAll("tbody>tr>td");
	for(var i=0; i<cells.length; i+=2){
		key = cells[i].textContent.trim();
		if(key) options[key] = cells[i+1].textContent.trim();
	}
	return JSON.encode(options);
}

/*********************************************************/

function focusCell(cell){
	var inp = doc.create("input", "", {class:"input-cell", list:"filters-list", value:cell.textContent.trim(), onblur:"this.parentNode.textContent=this.value"});
	cell.innerHTML = "";
	cell.appendChild(inp);
	inp.focus();
	inp.select();
}

function addBlockRow(row){
	row.insertAfter( doc.create("tr", "<th title='Add value' onclick='addBlockRow(this.parentNode)' bgcolor='white'><span class='tool'>&#xe908;</span><td onfocus='focusCell(this)' contenteditable='true'></td></th><th bgcolor='white'><span title='Drop row' class='tool red' onclick='deleteRow(this)'>&#xe907;</span></th>", {"bgcolor":"#DEF"}) );
}

/*********************************************************/

function showPatern(mode, theme){
	var pedt;
	var pattern = new Box('{}', "patterns/"+mode+"_patternbox", false);
	pattern.onopen=function(){
		mode = {"js":"javascript"}[mode] || mode;
		pedt = ace.edit(pattern.body.querySelector(".environment"));
		
		pedt.setTheme("ace/theme/"+(theme || "twilight"));
		pedt.getSession().setMode("ace/mode/"+mode);
		pedt.setShowInvisibles(false);
		pedt.setShowPrintMargin(false);
		pedt.resize();
	}
	pattern.onsubmit = function(form){
		XHR.push({
			"Content-Type":"text/"+mode,
			"addressee":"/patterns/actions/save-pattern/"+form.pname.value+"?path="+form.path.value,
			"body":pedt.getValue(),
			"onsuccess":function(response){
				pattern.body.querySelector(".leftbar").innerHTML = response;
			}
		});
	}
	pattern.add = function(){
		editor.session.insert(editor.selection.getCursor(), pedt.session.getValue());
		noChanged = false;
		window.parent.doc.querySelector("#topbar>label[for='"+frame_handle+"']").setAttribute("changed", true);
		pattern.drop();
	}
}