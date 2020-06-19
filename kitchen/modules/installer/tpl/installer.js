function onuploadInstaller(){
	var box = boxList[boxList.onFocus];
	openFilesDialog('welcome',function(files){
		boxList.focus(box.window);
		XHR.push({
			"Content-Type":"text/plain",
			"addressee":"/installer/actions/refresh/"+files[0].name,
			"onsuccess":function(response){
				box.body.querySelector(".left-tree>.install-root").innerHTML = response;
				checkInstallModule(files[0].name);
			}
		});
	});
}
function removeInstaller(){
	var form = boxList[boxList.onFocus].window;
	if(form.filename.value){
		XHR.push({
			"Content-Type":"text/plain",
			"addressee":"/installer/actions/remove/"+form.filename.value,
			"onsuccess":function(response){
				form.querySelector(".left-tree>.install-root").innerHTML = response;
			}
		});
	}else alertBox("nothing selected");
}
function checkInstallModule(filename){
	XHR.push({
		"Content-Type":"text/plain",
		"addressee":"/installer/actions/checkinstall/"+filename,
		"onsuccess":function(response){
			var box = boxList[boxList.onFocus];
			box.body.querySelector(".environment>.install-log").innerHTML = response;
		}
	});
}
function installModule(form){
	if(form.filename.value){
		XHR.push({
			"Content-Type":"text/plain",
			"addressee":"/installer/actions/install/"+form.filename.value,
			"onsuccess":function(response){
				form.querySelector(".environment>.install-log").innerHTML = response;
			}
		});
	}else alertBox("nothing selected");
	return false;
}