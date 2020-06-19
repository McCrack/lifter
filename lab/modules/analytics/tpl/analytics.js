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