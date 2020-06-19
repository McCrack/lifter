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
doc.onkeydown=function(event){
	if(event.ctrlKey && (event.keyCode===83)){
		event.preventDefault();
		saveFile();
	}
}
function saveFile(){
	XHR.push({
		"Content-Type":"text/plain",
		"addressee":"/developer/actions/save-file"+location.search,
		"body":editor.session.getValue(),
		"onsuccess":function(response){
			if(isNaN(response)){
				alertBox(response);
			}else{
				noChanged = true;
				window.parent.doc.querySelector("#topbar>label[for='"+frame_handle+"']").removeAttribute("changed");
			}
		}
	});
}