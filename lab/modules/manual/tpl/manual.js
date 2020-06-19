
/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	standby.leftbar = "materials-list";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
}

/*********************************************************/

function saveMaterial(){
	var id = location.pathname.split(/\//)[2] || false;
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
	var id = location.pathname.split(/\//)[2] || 0;
	var box = new Box('{}', "manual/createbox/"+id, true);
	box.onsubmit = function(form){
		XHR.push({
			"addressee":"/manual/actions/create/"+id,
			"body":JSON.stringyfy({
				"name":form.material.value.trim(),
				"language":form.language.value,
				"pid":form.pid.value
			}),
			"onsuccess":function(response){
				isNaN(response) ? alertBox(response) : location.pathname = "/manual/"+response;
			}
		});
	}
}