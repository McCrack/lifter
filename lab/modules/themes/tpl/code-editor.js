function showPatern(mode){
	var pedt;
	var pattern = new Box('{}', "patterns/"+mode+"_patternbox", false);
	pattern.onopen=function(){
		mode = {"html":"html","css":"css","js":"javascript"}[mode];
		pedt = ace.edit(pattern.body.querySelector(".environment"));
		pedt.setTheme("ace/theme/twilight");
		pedt.getSession().setMode("ace/mode/"+mode);
		pedt.setShowInvisibles(false);
		pedt.setShowPrintMargin(false);
		pedt.resize();
	}
	pattern.onsubmit = function(form){
		reauth();
		XHR.request("/patterns/actions/save-pattern/"+form.pname.value+"?path="+form.path.value, function(xhr){
			pattern.body.querySelector(".leftbar").innerHTML = xhr.response;
		}, pedt.getValue(), "text/"+mode);
	}
	pattern.add = function(){
		editor.session.insert(editor.selection.getCursor(), pedt.session.getValue());
		pattern.drop();
	}
}
function addImageURL(url){
	editor.session.insert(editor.selection.getCursor(), url);
}
function saveFile(){
	reauth();
	XHR.request("/themes/actions/save-file"+location.search, function(xhr){
		isNaN(xhr.response) ? alertBox(xhr.response) : alertBox("Done");
	}, editor.session.getValue(), "text/plain");
}

doc.onkeydown=function(event){
	if(event.ctrlKey && (event.keyCode===83)){
		event.preventDefault();
		saveFile();
	}
}
function addCSSRule(obj){
	if(obj.nodeName==="A"){
		editor.session.insert(editor.selection.getCursor(), obj.textContent+":");
	}
}
/*
function cssomBox(){
	var rows = objToTable( cssToObject(editor.getSession().doc.getTextRange(editor.selection.getRange())) );
	var box = modalBox('{}', "themes/cssombox", function(form){
		var obj = tableToObect(box.body);
		var css = objectToCSS(obj);
		editor.session.replace(editor.selection.getRange(), css); 
		box.drop();
	}, true);
	box.onopen = function(){
		box.body.querySelector("tbody").innerHTML = rows;
		box.align();
	}
}
function objToTable(sobj, level){
	level = level || 0;
	var rows="";
	for(var section in sobj){
		if(sobj.hasOwnProperty(section)){
			if(typeof sobj[section]==="object"){
				rows += 
				"<tr align='center' data-level='"+level+"' class='level-"+level+"'>"+
				"<th bgcolor='white' title='Add selector' onclick='addSelector(this.parentNode)'><span class='tool'>&#xe908;</span></th>"+
				"<td colspan='2' contenteditable='true'>"+section+"</td>"+
				"<th bgcolor='white' title='Drop selector' onclick='dropSelector(this.parentNode)'><span class='tool'>&#xe907;</span></th>"+
				"</tr>";
				rows += objToTable(sobj[section], level+1);
			}else if(typeof sobj[section]==="string"){
				rows += 
				"<tr class='level-"+level+"' data-level='"+level+"'>"+
				"<th bgcolor='white' title='Add rule' onclick='addRule(this.parentNode)'><span class='tool'>&#xe908;</span></th>"+
				"<td align='center' contenteditable='true' onfocus='focusCell(this, `rules-set`)'>"+section+"</td>"+
				"<td align='left' contenteditable='true' onfocus='focusCell(this, `values-set`)'>"+sobj[section]+"</td>"+
				"<th bgcolor='white' title='Drop rule' onclick='dropRule(this.parentNode)'><span class='tool'>&#xe907;</span></th>"+
				"</tr>";
			}
		}
	}
	return rows;
}
function cssToObject(cssText){
	cssText = cssText || "";
	var rule = null, selector = null;
	var i=0;
	var level = 0;
	var NCF = true;
	var cssTextRules = {};
	while(i<cssText.length){
		if(cssText[i]=='"') NCF ^= 1;
		if(NCF){
			switch(cssText[i]){
				case ";":
					if(level==0){
						rule = cssText.substr(0, i).split(":");
						cssTextRules[rule[0].trim()] = rule[1].trim();
						cssText = cssText.substr(++i);
						i=0;
						continue;
					}
				break;
				case "{":
					level++;
					if(level==1){
						selector = cssText.substr(0, i).trim();
						cssText = cssText.substr(++i);
						i=0;
						continue;
					}
				break;
				case "}":
					level--;
					if(level==0){
						cssTextRules[selector] = cssToObject(cssText.substr(0, i));
						cssText = cssText.substr(++i);
						i=0;
						continue;
					}
				break;
				default:break;
			}
		}
		i++;
	}
	return cssTextRules;
}
function tableToObect(owner){
	var level, sublevel, set={}, key, selector, cells;
	var rows = owner.querySelectorAll("tr");
	for(var i=0; i<rows.length;){
		level = rows[i].dataset.level;
		selector = rows[i].querySelector("td").textContent.trim();
		set[selector] = {};
		while(rows[++i] && level < rows[i].dataset.level){
			cells = rows[i].querySelectorAll("td");
			if(cells.length>1){
				set[selector][cells[0].textContent.trim()] = cells[1].textContent.trim();
			}else{
				sublevel = rows[i].dataset.level;
				key = cells[0].textContent.trim();
				set[selector][key] = {};
				while(rows[++i] && sublevel < rows[i].dataset.level){
					cells = rows[i].querySelectorAll("td");
					set[selector][key][cells[0].textContent.trim()] = cells[1].textContent.trim();
				}
				i--;
			}			
		}
	}
	return set;
}
function objectToCSS(obj){
	var union = [];
	for(var key in obj){
		if(typeof obj[key]==="object"){
			union.push(key+"{\n"+objectToCSS(obj[key])+"\n}");
		}else union.push("\t"+key+":"+obj[key]+";");
	}
	return union.join("\n");
}
function addSelector(row){
	body = row.parentNode;
	var level = row.dataset.level;
	do{
		row = row.next();
	}while(row && level < row.dataset.level);
	var selrow = doc.create("tr", "<th title='Add selector' onclick='addSelector(this.parentNode)'><span class='tool'>&#xe908;</span></th><td class='section' colspan='2' contenteditable='true'></td><th title='Drop selector' onclick='dropSelector(this.parentNode)'><span class='tool'>&#xe907;</span></th>", {"align":"center", "data-level":level, "class":"level-"+level});
	var rulerow = doc.create("tr", "<th title='Add rule' onclick='addRule(this.parentNode)'><span class='tool'>&#xe908;</span></th><td align='center' onfocus='focusCell(this, `rules-set`)' contenteditable='true'></td><td align='left' onfocus='focusCell(this, `values-set`)' contenteditable='true'></td><th title='Drop rule' onclick='dropRule(this.parentNode)'><span class='tool'>&#xe907;</span></th>", {"class":"level-"+(level+1), "data-level":(level+1)});
	if(row){
		body.insertBefore(rulerow, row);
		body.insertBefore(selrow, rulerow);
	}else{
		body.appendChild(selrow);
		body.appendChild(rulerow);
	}
}
function dropSelector(row){
	var body = row.parentNode;
	var level = row.dataset.level;
	var nextrow;
	do{
		nextrow = row.next();
		body.removeChild(row);
		row = nextrow;
	}while(row && level < row.dataset.level);
	view.refreshCSS();
}
function addRule(row){
	var level = row.dataset.level;
	row.insertAfter(doc.create("tr", "<th title='Add rule' onclick='addRule(this.parentNode)'><span class='tool'>&#xe908;</span></th><td contenteditable='true' onfocus='focusCell(this, `rules-set`)' align='center'></td><td align='left' onfocus='focusCell(this, `values-set`)' contenteditable='true'></td><th title='Drop rule' onclick='dropRule(this.parentNode)'><span class='tool'>&#xe907;</span></th>", {"data-level":level, "class":"level-"+level}));
}
function dropRule(row){
	row.parentNode.removeChild(row);
	view.refreshCSS();
}
function focusCell(cell, mode){
	var inp = doc.create("input", "", {class:"input-cell", list:mode, value:cell.textContent.trim(), onblur:"this.parentNode.textContent=this.value"});
	cell.innerHTML = "";
	cell.appendChild(inp);
	inp.focus();
	inp.select();
}
*/