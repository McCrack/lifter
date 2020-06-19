
/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	standby.leftbar = "sitemap";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
	
	(function(obj){
		reauth();
		var frame = doc.create("iframe","",{ "id":"preview", "src":"/uploader/image-frame", "class":"uploader-frame", "height":obj.getAttribute("height")});
		frame.onload = function(){ frame.contentWindow.document.setImage(obj.src); 	}
		obj.parentNode.replaceChild(frame, obj);
	})(doc.querySelector('#preview'));
}

/*********************************************************/


function addkeyword(obj){
	var form = doc.querySelector("#meta");
	if(obj.nodeName=="SPAN"){
		var tags=form.keywords.value.trim().split(/,+\s*/);
		if(tags[0]){
			tags.push(obj.textContent);
			tags = tags.flip();
			form.keywords.value = join(", ", flip(tags));
		}else form.keywords.value = obj.textContent;
	}
}
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

function addPage(){
	var path = location.pathname.split(/\//);
	var language = path[2] || 0;
	var box = modalBox('{}', "sitemap/addpagebox/"+language, function(form){
		var url = form.url.value.trim();
			url = url.toLowerCase();
			url = url.translite("-", true);
		var params = {
			"parent":(path[3] || "root"),
			"name":url,
			"type":form.entity.value,
			"language":form.language.value
		}
		XHR.push({
			"addressee":"/sitemap/actions/add-page",
			"body":JSON.stringify(params),
			"onsuccess":function(response){
				box.drop();
				setTimeout(function(){
					isNaN(response) ? alertBox(response) : location.pathname = "sitemap/"+params['language']+"/"+params['name'];
				}, 300);
			}
		});
	}, true);
}
function savePage(){
	var meta = doc.querySelector("#meta");
	var url = meta.url.value.trim() || prompt("Please enter page name").trim();
	if(!url) return false;
		url = url.toLowerCase();
		url = url.translite("-", true);
	var prt = meta.prt.value.trim()  || "root";
		prt = prt.toLowerCase();
		prt = prt.translite("-", true);
	
	var params = {
		"id":meta.pageID.value || 0,
		"parent":prt,
		"name":url,
		"header":meta.header.value.trim() || "",
		"subheader":meta.subheader.value.trim() || "",
		"context":meta.context.value.trim() || "",
		"type":meta.entity.value,
		"language":meta.language.value,
		"module":meta.module.value,
		"template":meta.template.value,
		"preview":meta.querySelector("#preview").contentWindow.document.getImage(),
		"description":meta.desc.value.trim() || "",
		"published":doc.querySelector("#topbar").published.checked ? "Published" : "Not published",
		"options":(function(){
			var properties = {};
			var cells = doc.querySelectorAll("table#options>tbody>tr>td");
			for(var i=0; i<cells.length; i+=2){
				var key = cells[i].textContent.trim();
					val = cells[i+1].textContent.trim();
				if(key) properties[key] = val;
				// properties.push( cells[i].textContent.trim() );
			}
			return properties;
		})()
	}
	var box = modalBox('{}', "sitemap/savebox", function(){}, true);
	box.onopen = function(){
		var log = box.window.querySelector("#upload-log");
		XHR.push({
			"addressee":"/sitemap/actions/save-metadata",
			"body":JSON.encode(params),
			"onsuccess":function(response){
				var answer = JSON.parse(response);
				for(var key in answer){
					log.appendChild(doc.create("div", "<tt><b>"+key+"</b>: "+answer[key]+"</tt>"));
				}
				if(answer['PageID']){
					saveContent(function(response){
						
						
						box.window.querySelector(".box-footer>button").disabled = false;
						log.innerHTML += "<hr size='1' color='#AAA'>";
						if(isNaN(response)){
							log.appendChild(doc.create("h3", "<tt>Content - <span class='red'>Failed save</span></tt>"));
						}else log.appendChild(doc.create("h3", "<tt>Content - <span class='green'>Saved</span></tt>"));
						
					}, answer['PageID']);
					
				}
			}
		});
	}
	return false;
}
function saveContent(onSave, PageID){
	if(PageID){
		XHR.push({
			"Content-Type":"text/html",
			"addressee":"/sitemap/actions/save-content/"+PageID, 
			"body":editor.getValue(),
			"onsuccess":onSave
		});
	}
}
function removePage(){
	var id = doc.querySelector("#meta").pageID.value || 0;
	if(id){
		confirmBox("remove material", function(){
			XHR.push({
				"Content-Type":"text/plain",
				"addressee":"/sitemap/actions/remove-page/"+id, 
				"onsuccess":function(response){
					setTimeout(function(){
						isNaN(response) ? alertBox(response) : (location.pathname = "sitemap"); 
					}, 300);
				}
			});
		});		
	}else alertBox("material not selected");
}

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