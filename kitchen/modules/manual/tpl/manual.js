
/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	standby.leftbar = "materials-list";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
}

/*********************************************************/

function saveMaterial(){
	var id = location.pathname.split(/\//)[3] || null;
	if(id){
		var content = doc.querySelector("iframe.HTMLDesigner").contentWindow.document.getValue();
		XHR.push({
			"Content-Type":"text/html",
			"addressee":"/manual/actions/save/"+id,
			"body":content.trim(),
			"onsuccess":function(response){
				parseInt(response) ? null : alert(response);
			}
		});
	}else alertBox("material not selected");
}
function createMaterial(){
	var box = new Box('{}', "manual/createbox/"+(location.pathname.split(/\//)[3] || 0)+"/"+LANGUAGE, true);
}

function reloadTree(lang){
	var path = location.pathname.split(/\//);
		path[2] = lang;
		window.history.pushState("", "tree", path.join("/"));
	XHR.push({
		"addressee":"/manual/actions/reload-tree/"+lang,
		"onsuccess":function(response){
			LANGUAGE = lang;
			doc.querySelector("#materials-list>.root").innerHTML = response;
			window.history.pushState("", "tree", path.join("/"));
		}
	});
}