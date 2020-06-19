
/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	standby.leftbar = "post-feed";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
	
	(function(obj){
		reauth();
		var frame = doc.create("iframe","",{ "id":"preview", "src":"/uploader/image-frame", "class":"uploader-frame", "height":obj.getAttribute("height")});
		frame.onload = function(){ frame.contentWindow.document.setImage(obj.src); }
		obj.parentNode.replaceChild(frame, obj);
	})(doc.querySelector('#preview'));
}

/*********************************************************/


function reloadFeed(lang, page){
	if(isNaN(page)) return false;
	var path = location.pathname.split(/\//);
		path[2] = lang;
		path[3] = page;
	XHR.push({
		"addressee":"/blogger/actions/reload/"+lang+"/"+page,
		"onsuccess":function(response){
			doc.querySelector("#feed").innerHTML = response;
			window.history.pushState("", "feedpage", path.join("/"));
		}
	});
}
function addkeyword(obj){
	var form = doc.querySelector("#heading");
	if(obj.nodeName=="SPAN"){
		var tags=form.keywords.value.trim().split(/,+\s*/);
		if(tags[0]){
			tags.push(obj.textContent);
			tags = tags.flip();
			form.keywords.value = join(", ", flip(tags));
		}else form.keywords.value = obj.textContent;
	}
}

/*******************************************/

function CreatePost(){
	var lang = location.pathname.split(/\//)[2] || "ru";
	var form = doc.querySelector("#heading");
	XHR.push({
		"Content-Type":"text/plain",
		"addressee":"/blogger/actions/create-post/"+form.author.value+"/"+lang,
		"onsuccess":function(response){
			location.pathname = response;
		}
	});
}
function SavePost(){
	var coverForm = doc.querySelector("#heading");
	var dt=coverForm['created-date'].value.split(/\D+/);
	var tm=coverForm['created-time'].value.split(/\D+/);
		d = new Date(Date.parse(dt[1]+"/"+dt[0]+"/"+dt[2]));
		d.setHours(tm[0]||0);
		d.setMinutes(tm[1]||0);
		d.setSeconds(tm[2]||0);
	var params = {
		"PageID":coverForm.PageID.value || 0,
		"ID":coverForm.postID.value || 0,
		"language":coverForm.language.value,
		"published":coverForm.published.checked ? "Published" : "Not published",
		"preview":coverForm.querySelector("#preview").contentWindow.document.getImage(),
		"header":coverForm.header.value.replace(/"/g,"″"),
		"subheader":coverForm.subheader.value.replace(/"/g,"″"),
		"template":coverForm.template.value,
		"created":Math.floor(d.getTime()/1000),
		"tid":coverForm.tid.value || 2,
		"keywords":coverForm.keywords.value || ""
	}
	var box = modalBox('{}', "blogger/savebox", function(){	box.drop(); }, true);
	box.onopen = function(){
		var log = box.window.querySelector(".log");
		XHR.push({
			"addressee":"/blogger/actions/save-heading",
			"body":JSON.stringify(params),
			"onsuccess":function(response){
				var answer = JSON.parse(response);
				for(var key in answer){
					log.appendChild(doc.create("div", "<tt><b>"+key+"</b>: "+answer[key]+"</tt>"));
				}
				if(answer['PageID']){
					coverForm.PageID.value = answer['PageID'];
					coverForm.postID.value = answer['ID'];
					box.window.onreset = function(){
						box.drop();
						var path = location.pathname.split(/\//);
						location.pathname = "/blogger/"+(path[2] || params['language'])+"/"+(path[3] || 1)+"/"+answer['ID']+"/"+params['language'];
					}
					var content = doc.querySelector("#environment>iframe.HTMLDesigner").contentWindow.document.getValue();
					saveContent(function(response){
						log.innerHTML += "<hr size='1'>";
						if(isNaN(response)){
							log.appendChild(doc.create("h3", "<tt>Content - <span class='red'>Failed save</span></tt>"));
						}else{
							log.appendChild(doc.create("h3", "<tt>Content - <span class='green'>Saved</span></tt>"));
							if(params['template']=="ampfree"){
								XHR.push({
									"addressee":"/blogger/actions/drop-amp/"+answer['PageID']
								});	
							}else convertToAMP(function(response){
								if(isNaN(response)){
									log.appendChild(doc.create("h3", "Google AMP - <span class='red'>Failed save</span>"));
								}else log.appendChild(doc.create("h3", "👌Google AMP - <span class='green'>Saved</span>"));
								box.align();
							}, answer['PageID'], content);

							btns = box.window.querySelectorAll(".box-footer>button");
							btns[0].disabled = false;
							btns[1].disabled = false;
							btns[1].onclick = function(){
								setTimeout(function(){ cached(answer['PageID']); }, 200);
							}
						}
					}, answer['PageID'], content);
				}else{
					box.window.querySelector(".box-footer>button").disabled = false;
					box.window.onreset = function(){
						box.drop();
					}
				}
			}
		});
	}
	return false;
}
function saveContent(onSave, PageID,content){
	if(PageID){
		XHR.push({
			"Content-Type":"text/html",
			"addressee":"/blogger/actions/save-content/"+PageID,
			"body":content,
			"onsuccess":onSave
		});
	}
}

function convertToAMP(onSave, PageID, content){
	content = doc.create("div",content);

	content.querySelectorAll("img").forEach(function(img, i){
		let amp = doc.create("amp-img","",{
			src:img.src,
			width:img.naturalWidth,
			height:img.naturalHeight,
			layout:"responsive"
		})
		img.parentNode.replaceChild(amp, img);
	});
	content.querySelectorAll("video").forEach(function(vid, i){
		let amp = doc.create("amp-video","",{
			src:vid.src,
			width:vid.videoWidth,
			height:vid.videoHeight,
			layout:"responsive"
		})
		vid.parentNode.replaceChild(amp, vid);
	});
	content.querySelectorAll(".video>iframe").forEach(function(ifm){
		let amp = doc.create("amp-youtube","",{
			"data-videoid":ifm.src.split(/\//).pop(),
			"width":"480",
			"height":"270",
			"layout":"responsive"
		})
		ifm.parentNode.replaceChild(amp, ifm);
	});
	content.querySelectorAll(".embed>iframe").forEach(function(ifm){
		let amp = doc.create("amp-iframe","",{
			"src":ifm.src,
			"width":ifm.width,
			"height":ifm.height,
			"layout":"responsive"
		})
		ifm.parentNode.replaceChild(amp, ifm);
	});
	content.querySelectorAll("*[contenteditable]").forEach(function(obj){
		obj.removeAttribute("contenteditable");
	});
	content.querySelectorAll(".adsense").forEach(function(obj){
		obj.parentNode.removeChild(obj);
	});
	content.querySelectorAll("*[style]").forEach(function(obj){
		obj.removeAttribute("style");
	});
	XHR.push({
		"Content-Type":"text/html",
		"addressee":"/blogger/actions/save-amp/"+PageID,
		"body":content.innerHTML,
		"onsuccess":onSave
	});	
}

function cached(id){
	id = id || doc.querySelector("#heading").PageID.value;
	var box = new Box('{}', "blogger/cachingbox", true);
		box.onsubmit = function(form){
			var inp = form.querySelectorAll("input[name='subdomain']");
			var params = {"folders":{},"preview":""};
			for(var i=inp.length; i--;){
				if(inp[i].checked){
					params['folders'][inp[i].value] = form[inp[i].value].value;
				}
			}
			if(form.preview){
				params['preview'] = form.preview.value.trim();
			}
			XHR.push({
				"addressee":"/blogger/actions/render/"+id,
				"body":JSON.stringify(params),
				"onsuccess":function(response){
					box.window.querySelector(".log").innerHTML = response;
					btns = box.window.querySelectorAll(".box-footer>button");
					btns[1].textContent = "Ok";
					btns[0].parentNode.removeChild(btns[0]);
				}
			});
			return false;
		}
}
function removePost(){
	var id = doc.querySelector("#heading").PageID.value;
	if(id){
		confirmBox("remove post", function(){
			XHR.push({
				"Content-Type":"text/plain",
				"addressee":"/blogger/actions/remove/"+id,
				"onsuccess":function(response){
					var path = location.pathname.split(/\//);
					delete(path[path.length-1]);
					location.pathname = path.join("/");
				}
			});
		});
	}else alertBox("post not selected");
	
}

/***********************************************************/

var imgBox=function(btn){
	var box = new Box('{}', "editor/actions/module/video", false);
	box.onsubmit = function(form){
		var uri = box.getModuleContent(form);
		if(uri){
			btn.parentNode.replaceChild( doc.create("input","",{name:"preview",value:uri,style:"padding:4px 15px"}), btn);
			boxList[box.window.id].drop();
		}
	}
}