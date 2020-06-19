
/* Initialization ****************************************/

window.onload=function(){
	standby.leftbar = "domains-list";
	standby.rightbar = "brancher";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
}

/*********************************************************/

function saveSettings(domain){
	var path = location.pathname.split(/\//);
	if(path[2]){
		if(settings = settingsToJson()){
			XHR.push({
				"addressee":"/settings/actions/save/"+path[2]+"/"+(path[3] || 0),
				"body":settings,
				"onsuccess":function(response){
					isNaN(response) ? alert(response) : location.reload();
				}
			});
		}
	}else{
		alertBox("domain not selected");
	}
	return false;
}
var jsontosettings = function(json){
	var sobj = JSON.parse(json);
	var rows="",color=16777215;
	for(var section in sobj){
		if(sobj.hasOwnProperty(section)){
			rows += "<tr align='center' style='color:#EEE;background-color:#069'><td colspan='5' class='section'>"+section+"</td></tr>";
			for(var key in sobj[section]){
				rows += 
				"<tr bgcolor='#"+((color^=2037018).toString(16))+"' data-type='"+sobj[section][key]['type']+"'>"+
				"<th bgcolor='white'><span title='Add row' class='tool' onclick='addRow(this)'>&#xe908;</span></th>"+
				"<td align='center' contenteditable='true' data-key='"+key+"'>"+key+"</td>"+
				"<td contenteditable='true'>"+sobj[section][key]['value']+"</td>"+
				"<td contenteditable='true'>"+sobj[section][key]['valid']+"</td>"+
				"<th bgcolor='white'><span title='Delete row' class='tool red' onclick='deleteRow(this)'>&#xe907;</span></th>"+
				"</tr>";
			}
		}
	}
	doc.querySelector("#environment>table>tbody").innerHTML = rows;
}
function changeBranch(form){
	var path = location.pathname.split(/\//);
	XHR.push({
		"Content-Type":"text/plain",
		"addressee":"/settings/actions/change-branch",
		"body":JSON.stringify({
			"subdomain":path[2],
			"module":path[3],
			"parent":form.branch.value
		}),
		"onsuccess":function(response){
			form.querySelector(".content").innerHTML = response;
		}
	});
}