
/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	standby.rightbar = "citizens";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
}

/*********************************************************/

function openCitizen(row){
	if(row.nodeName!="TR"){
		row = row.ancestor("tr");
	}
	var path = location.pathname.split(/\//);
		path[2] = path[2] || 1;
		path[3] = row.dataset.id;
		location.pathname = path.join("/");
}
function sendLetter(form){
	var editor = form.querySelector("iframe");
	XHR.push({
		"Content-Type":"application/x-www-form-urlencoded",
		"addressee":"/community/actions/mailing",
		"body":"from="+form.from.value+"&to="+form.to.value+"&theme="+form.theme.value+"&message="+editor.contentWindow.document.getValue(),
		"onsuccess":function(response){
			parseInt(response) ? alert("The letter was sent.") : alert(response);
		}
	});
	return false;
}
function addToStaff(){
	var id = location.pathname.split(/\//)[3];
	XHR.push({
		"Content-Type":"text/plain",
		"addressee":"/community/actions/add-to-staff/"+id,
		"onsuccess":function(response){
			isNaN(response) ? alert(response) : location.pathname="/staff/"+response;
		}
	});
	return false;
}
function saveCitizen(){
	var id = location.pathname.split(/\//)[3];
	if(id){
		var cells = doc.querySelectorAll("#options>table>tbody>tr>td");
		var options = {};
		for(var i=0; i<cells.length; i+=2){
			var key = cells[i].textContent.trim().replace(/\\/,"");
			if(key){
				options[key] = cells[i+1].textContent.trim().replace(/\\/,"");
			}
		}
		XHR.push({
			"addressee":"/community/actions/save/"+id,
			"body":JSON.stringify(options),
			"onsuccess":function(response){
				isNaN(response) ? alert(response) : null;
			}
		});
	}
	return false;
}