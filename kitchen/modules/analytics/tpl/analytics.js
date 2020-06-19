
/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
}

/*********************************************************/

function setPeriod(form){
	location.pathname = "/analytics/"+form.from.value+"/"+form.to.value;
	return false;
}
function showInfo(group, event){
	var circles = group.getElementsByTagName("circle");
	var rows = "";
	for(var i=0; i<circles.length; i++){
		rows += "<tr><td align='right' width='120'>"+circles[i].getAttribute("data-title")+":</td><td>"+circles[i].getAttribute("data-value")+"</td></tr>";
	}
	var infobar = doc.create("div",	"<table cellpadding='2'><thead><tr><th colspan='2'>"+group.getAttribute("data-day")+"</th></tr></thead><tbody>"+rows+"</tbody></table>", {
		"id":"infobar",
		"style":"top:"+event.clientY+"px;left:"+event.clientX+"px;"
	});
	doc.body.appendChild(infobar);
	if((infobar.offsetTop + infobar.offsetHeight) > doc.body.clientHeight){
		infobar.style.top = (event.clientY - infobar.offsetHeight)+"px";
	}
	if((infobar.offsetLeft + infobar.offsetWidth) > doc.body.clientWidth){
		infobar.style.left = (event.clientX - infobar.offsetWidth)+"px";
	}
	group.onmouseout = function(){ doc.body.removeChild(infobar); }
}
function showEditorBox(obj, id){
	if(obj.className != "context" && obj.className != "header" && obj.className != "subheader" && obj.className != "description") return false;
	var box = new Box("", "analytics/editorbox/"+obj.className);
		box.onopen = function(){
			box.body.querySelector("textarea").value = obj.textContent;
		}
		box.onsubmit = function(){
			XHR.push({
				"addressee":"/sitemap/actions/save-"+obj.className+"/"+id,
				"body":box.window.field.value || " ",
				"onsuccess":function(response){
					if(parseInt(response)){
						obj.textContent = box.window.field.value;
						box.drop();
					}
				}
			});
		}
}

function createElementID(){
	promptBox("Element Name", function(ename){
		XHR.push({
			"addressee":"/analytics/actions/create-element_id",
			"body":ename,
			"onsuccess":function(response){
				document.querySelector("#elements-analytic").innerHTML = response;
			}
		});
	});
}
function removeElementIDs(){
	var IDs = [];
	document.querySelectorAll("#elements-analytic>tr>td>input:checked").forEach(function(inp){
		IDs.push(inp.value);
	});
	XHR.push({
		"addressee":"/analytics/actions/remove-elements",
		"body":JSON.encode(IDs),
		"onsuccess":function(response){
			document.querySelector("#elements-analytic").innerHTML = response;
		}
	});
}
function resetCounters(){
	XHR.push({
		"addressee":"/analytics/actions/reset-counters",
		"onsuccess":function(response){
			document.querySelector("#elements-analytic").innerHTML = response;
		}
	});
}