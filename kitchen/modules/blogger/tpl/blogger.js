
/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	standby.leftbar = "post-feed";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
	
	(function(obj){
		reauth();
		var frame = doc.create("iframe","",{ "id":"image-preview", "src":"/uploader/image-frame", "class":"uploader-frame", "height":obj.getAttribute("height")});
		frame.onload = function(){ frame.contentWindow.document.setImage(obj.src); }
		obj.parentNode.replaceChild(frame, obj);
	})(doc.querySelector('#image-preview'));

	setTimeout(function(){
		(function(obj){
			reauth();
			var frame = doc.create("iframe","",{ "id":"video-preview", "src":"/uploader/video-frame", "class":"uploader-frame", "height":obj.getAttribute("height")});
			frame.onload = function(){
				frame.contentWindow.document.setImage(obj.src);
			}
			
			obj.parentNode.replaceChild(frame, obj);
		})(doc.querySelector('#video-preview'));
	},1000);
}

/****************************************************************/


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

/****************************************************************/

function CreatePost(){
	var lang = location.pathname.split(/\//)[2] || "";
	var form = doc.querySelector("#heading");
	XHR.push({
		"Content-Type":"text/plain",
		"addressee":"/blogger/actions/create-post/"+form.author.value+"/"+lang,
		"onsuccess":function(response){
			response = JSON.parse(response);

			var path = JSON.parse(window.localStorage.uploader);
				path['subdomain'] = "img";
				path['img'] = "../img/data/"+response['year']+"/"+response['month']+"/"+response['id'];
				window.localStorage.uploader = JSON.encode(path);

			location.pathname = "/blogger/"+response['language']+"/1/"+response['id']+"/"+response['language'];
		}
	});
}

/****************************************************************/

function SavePost(coverForm){
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
		"ads":coverForm.ads.checked ? "YES" : "NO",
		"published":coverForm.published.checked ? "Published" : "Not published",
		"category":coverForm.category.value,
		"preview":coverForm.querySelector("#image-preview").contentWindow.document.getImage(),
		"video":coverForm.querySelector("#video-preview").contentWindow.document.getVideo().src,
		"header":coverForm.header.value.trim().replace(/"/g,"″"),
		"subheader":coverForm.subheader.value.trim().replace(/"/g,"″"),
		"UserID":coverForm.author.value || 0,
		"created":Math.floor(d.getTime()/1000),
		"tid":coverForm.tid.value || 2,
		"subtemplate":coverForm.template.value,
		"keywords":(function(){
			var words = [];
			coverForm.keywords.value.split(/,/).forEach(function(item){
				words.push(item.translite());
			});
			return words.join(",");
		})()
	}
	var box = modalBox('{}', "blogger/savebox", function(){	box.drop(); }, true);
	box.onopen = function(){
		var log = box.window.querySelector(".log");
		XHR.push({
			"addressee":"/blogger/actions/save-heading",
			"body":JSON.encode(params),
			"onsuccess":function(response){
				var answer = JSON.parse(response);
				for(var key in answer.log){
					log.appendChild(doc.create("div", "<tt><b>"+key+"</b>: "+answer.log[key]+"</tt>"));
				}
				if(answer.log['PageID']){
					coverForm.PageID.value = answer.log['PageID'];
					coverForm.postID.value = answer.log['ID'];
					box.window.onsubmit=function(event){
						event.preventDefault();
						box.drop();
						var path = location.pathname.split(/\//);
						location.pathname = "/blogger/"+(path[2] || params['language'])+"/"+(path[3] || 1)+"/"+answer.log['ID']+"/"+params['language'];
					}
					log.innerHTML += "<input value='"+answer.url+"' onfocus='copyURL(this)' class='url-field'>";
					
					var content = doc.querySelector("#environment>iframe.HTMLDesigner").contentWindow.document.getValue();
					
					/******* Save Content *******/
					saveContent(function(response){
						if(isNaN(response)){
							log.appendChild(doc.create("h3", "<b>Content</b> - <span class='red'>Failed save</span>"));
						}else{
							log.appendChild(doc.create("h3", "👌<b>Content</b> - <span class='green'>Saved</span>"));
							
							if(coverForm.amp.checked){ /*********** Google AMP ***********/
								convertToAMP(function(response){
									if(isNaN(response)){
										log.appendChild(doc.create("h3", "Google AMP - <span class='red'>Failed save</span>"));
									}else log.appendChild(doc.create("h3", "👌Google AMP - <span class='green'>Saved</span>"));
									box.align();
								}, answer.log['PageID'], content);
							}else XHR.push({ "addressee":"/blogger/actions/drop-amp/"+answer.log['PageID'] });

							if(coverForm.ina.checked){ /**** Facebook Instant Articles ****/
								convertToInA(function(response){
									if(isNaN(response)){
										log.appendChild(doc.create("h3", "Facebook Instant Articles - <span class='red'>Failed save</span>"));
									}else log.appendChild(doc.create("h3", "👌Facebook Instant Articles - <span class='green'>Saved</span>"));
									box.align();
								}, answer.log['PageID'], content, coverForm);
							}else XHR.push({ "addressee":"/blogger/actions/drop-ina/"+answer.log['PageID'] });
						}
					}, answer.log['PageID'], content);
				}
			}
		});
	}
	return false;
}
function saveContent(onSave, PageID, content){
	if(PageID){
		XHR.push({
			"Content-Type":"text/html",
			"addressee":"/blogger/actions/save-content/"+PageID,
			"body":content.trim(),
			"onsuccess":onSave
		});
	}
}
function convertToInA(onSave, PageID, content, form){
	content = doc.create("div",content);
	content.querySelectorAll(".adsense").forEach(function(item){ item.parentNode.removeChild(item); });
	content.querySelectorAll("h3").forEach(function(item){ item.parentNode.replaceChild(doc.create("h2", item.textContent), item); });
	content.querySelectorAll("h4").forEach(function(item){
		item.parentNode.replaceChild(doc.create("p", "<b>"+item.textContent+"</b>"), item);
	});
	content.querySelectorAll("img").forEach(function(item){
		if(item.parentNode.nodeName!="FIGURE"){
			var figure = doc.create("figure");
			item.insertAfter(figure);
			figure.appendChild(item);
		}
	});
	content.querySelectorAll(".video>iframe").forEach(function(frm){
		frm.width = "480";
		frm.height = "270";
	});
	content.querySelectorAll("video").forEach(function(item){
		if(item.parentNode.nodeName!="FIGURE"){
			item.removeAttribute("controls")
			figure = doc.create("figure");
			item.insertAfter(figure);
			figure.appendChild(item);
		}
	});

	XHR.push({
		"Content-Type":"text/html",
		"addressee":"/blogger/actions/save-ina/"+PageID+"/"+(form['video-cover'].checked ? "video" : "image"),
		"body":content.innerHTML,
		"onsuccess":onSave
	});
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
	content.querySelectorAll("*[contenteditable]").forEach(function(obj){
		obj.removeAttribute("contenteditable");
	});
	XHR.push({
		"Content-Type":"text/html",
		"addressee":"/blogger/actions/save-amp/"+PageID,
		"body":content.innerHTML,
		"onsuccess":onSave
	});	
}

/****************************************************************/

function removePost(){
	var id = doc.querySelector("#heading").PageID.value;
	if(id){
		confirmBox("remove post", function(){
			XHR.push({
				"Content-Type":"text/plain",
				"addressee":"/blogger/actions/remove/"+id,
				"onsuccess":function(response){
					var path = location.pathname.split(/\//);
					location.pathname = path[0]+"/"+path[1]+"/"+path[2]+"/"+path[3];
				}
			});
		});
	}else alertBox("post not selected");	
}

/****************************************************************/

function copyURL(field){
	field.select();
	document.execCommand('copy');
}