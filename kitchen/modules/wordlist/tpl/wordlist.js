
/* Initialization ****************************************/

window.onload=function(){
	standby.leftbar = "word-list";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
}

/*********************************************************/

function saveWordlist(){
	var path = window.location.pathname.split(/\//);
	if(path[3]){
		XHR.push({
			"addressee":"/wordlist/actions/save/"+path[2]+"/"+path[3],
			"body":wordlistToJson(),
			"onsuccess":function(response){
				isNaN(response) ? alert(response) : location.reload();
			}
		});
	}else alertBox("wordlist not selected");	
	return false;
}
function createWordlist(){
	var path = window.location.pathname.split(/\//);
	if(path[2]){
		promptBox("wordlist name", function(wname){
			XHR.push({
				"addressee":"/wordlist/actions/create",
				"body":JSON.stringify({
					"domain":path[2],
					"name":wname
				}),
				"onsuccess":function(response){
					if(parseInt(response)){
						window.location.pathname = "/wordlist/"+path[2]+"/"+wname; 
					}else alert(response);
				}
			});
		},"[a-z0-9_-]+");
	}else alertBox("domain not selected");
	return false;
}
function removeWordlist(){
	var path = window.location.pathname.split(/\//);
	if(path[3]){
		confirmBox("delete wordlist", function(){
			XHR.push({
				"addressee":"/wordlist/actions/remove/"+path[2]+"/"+path[3],
				"onsuccess":function(response){
					parseInt(response) ? location.pathname = "wordlist" : alert(response);
				}
			});
		});
	}else alertBox("wordlist not selected");
}
function addLanguage(){
	promptBox("language index", function(lname){
		if(/^[a-z]{2}$/.test(lname)){
			var cells = doc.querySelectorAll("#wordlist>thead>tr>th");
			var cell = cells[cells.length-1];
				cell.insertAfter(doc.create("th",lname));
			
			var rows = doc.querySelectorAll("#wordlist>tbody>tr");
			for(var i=rows.length; i--;){
				cells=rows[i].querySelectorAll("td");
				cell = cells[cells.length-1];
				cell.insertAfter(doc.create("td","",{"contenteditable":"true"}));
			}
		}else{
			boxList[boxList.onFocus].drop();
			alertBox("incorrect language");
		}
	},"[a-z]{2}");
}
function wordlistToJson(){
	var cells, wordlist={};
	var names=doc.querySelectorAll("#wordlist>thead>tr>th");
	var rows=doc.querySelectorAll("#wordlist>tbody>tr");
	for(var j=1; j<names.length; j++){ wordlist[names[j].textContent]={}; }
	for(var i=0; i<rows.length; i++){
		cells=rows[i].querySelectorAll("td");
		for(var j=1; j<names.length; j++){ 
			wordlist[names[j].textContent][cells[0].textContent]=cells[j].textContent;
		}
	}
	return JSON.encode(wordlist);
}
var jsontowordlist = function(json){
	var wobj = JSON.parse(json);
	var color=16777215;
	var keys=[], head="";
	for(var key in wobj){
		if(wobj.hasOwnProperty(key)){
			head += "<th>"+key+"</th>";
			keys = keys.concat(wobj[key]);
		}
	}
	var rows = "";
	for(var key in keys[0]){
		rows += "<tr bgcolor='#"+((color^=2037018).toString(16))+"'>"+
		"<th bgcolor='white'><span title='add row' lang='title' class='tool' onclick='addRow(this)'>+</span></th>"+
		"<td align='center' contenteditable='true'>"+key+"</td>";
		for(var lang in wobj){
			rows += "<td contenteditable='true'>"+wobj[lang][key]+"</td>";
		}
		rows += "<th bgcolor='white'><span title='delete row' lang='title' class='tool' onclick='deleteRow(this)'>-</span></th></tr>";
	}
	doc.querySelector("#wordlist>thead>tr").innerHTML = "<td width='26'></td><th>Keys</th>"+head+"<td width='26'></td>";
	doc.querySelector("#wordlist>tbody").innerHTML = rows;
}

/*************************************************************************/

function openShortWordlist(obj){
	if(obj.nodeName==="A"){
		XHR.push({
			"Content-Type":"text/plain",
			"addressee":"/wordlist/actions/showkey",
			"body":obj.href,
			"onsuccess":function(response){
				var box = boxList[boxList.onFocus];
				box.body.dataset.path = obj.href;
				box.body.querySelector(".environment").innerHTML = response;
			}
		});
	}
	return false;
}
function saveShortWordlist(form){
	var lang = "";
	var params = {"path":form.path.value, wordlist:{}};
	if(params.path){
		var table = form.querySelector(".environment>table");
		var key = table.querySelector("thead>tr>td").textContent;
		var langs = table.querySelectorAll("tbody>tr>th");
		var values = table.querySelectorAll("tbody>tr>td");
		for(var i=0; i<values.length; i++){
			lang = langs[i].textContent;
			params.wordlist[lang] = {};
			params.wordlist[lang][key] = values[i].textContent;
		}
		XHR.push({
			"addressee":"/wordlist/actions/savekey",
			"body":JSON.stringify(params),
			"onsuccess":function(response){
				boxList[form.id].drop();
			}
		});
	}
	return false;
}