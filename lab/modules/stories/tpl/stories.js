/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	standby.leftbar = "post-feed";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
	
	doc.querySelectorAll("#heading>img").forEach(function(obj){
		reauth();
		var frame = doc.create("iframe","",{ "id":"preview", "src":"/uploader/image-frame", "class":"uploader-frame "+obj.className, "height":obj.getAttribute("height")});
		frame.onload = function(){ frame.contentWindow.document.setImage(obj.src); }
		obj.parentNode.replaceChild(frame, obj);
	});
}


function addSlide(){
	var box = new Box('{}', "stories/bgbox", false);
		box.onsubmit = function(form){
			box.getData(form).forEach(function(bg){
				var slide = doc.create("figure");
				if(bg.type=="image"){
					slide.appendChild( doc.create("img","",{"src":bg.src,"class":"background"}) );
				}else if(bg.type=="video"){
					slide.appendChild( doc.create("video","",{"src":bg.src,"class":"background","type":bg.type+"/"+bg.mime}) );
				}
				var caption = doc.create("figcaption","",{"class":"center"});
				var header = doc.create("h1","",{"contenteditable":"true"})
				var text = doc.create("div","",{"contenteditable":"true"});
				header.onfocus = text.onfocus = function(){
					field = this;
				}
				header.onpaste = text.onpaste = function(event){
					pasteCaption(event);
				}
				caption.appendChild(header);
				caption.appendChild(text);

				slide.appendChild( caption );
				var slideshow = doc.querySelector("#slideshow");
				slideshow.appendChild(slide);
				slideshow.amount++;
			});
			box.drop();
		}
}
function pasteCaption(event){
	event.preventDefault();
	document.execCommand("insertHTML", false, event.clipboardData.getData("text").trim().replace(/\n+/g,""));
}

function setStickerBgColor(color){
	if(field) field.style.backgroundColor = color;
}
function setStickerTxtColor(color){
	if(field) field.style.color = color;
}
function removeSlide(color){
	var slideshow = doc.querySelector("#slideshow");
	var slide = slideshow.querySelectorAll("figure")[slideshow.current];
	slideshow.removeChild(slide);

	slideshow.amount--;
	slideshow.current--;
	if(slideshow.current<0) slideshow.current = 0;
	slideshow.parentNode.querySelector("#slide-num").innerHTML = slideshow.current + 1;
}

/*********************************************************/


function reloadFeed(lang, page){
	if(isNaN(page)) return false;
	var path = location.pathname.split(/\//);
		path[2] = lang;
		path[3] = page;
	XHR.push({
		"addressee":"/stories/actions/reload/"+lang+"/"+page,
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

function CreatePost(){
	var lang = location.pathname.split(/\//)[2] || "";
	var form = doc.querySelector("#heading");
	XHR.push({
		"Content-Type":"text/plain",
		"addressee":"/stories/actions/create-post/"+form.author.value+"/"+lang,
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
		"category":coverForm.category.value,
		"preview":coverForm.querySelector("#preview").contentWindow.document.getImage(),
		"header":coverForm.header.value,
		"subheader":coverForm.subheader.value,
		"UserID":coverForm.author.value || 0,
		"created":Math.floor(d.getTime()/1000),
		"tid":coverForm.tid.value || 2,
		"subtemplate":coverForm.template.value,
		"keywords":coverForm.keywords.value.translite() || "",
		"story":getContent()
	}
	var box = modalBox('{}', "blogger/savebox", function(){	box.drop(); }, true);
	box.onopen = function(){
		var log = box.window.querySelector(".log");
		XHR.push({
			"addressee":"/stories/actions/save",
			"body":JSON.encode(params),
			"onsuccess":function(response){
				var answer = JSON.parse(response);
				for(var key in answer){
					log.appendChild(doc.create("div", "<tt><b>"+key+"</b>: "+answer[key]+"</tt>"));
				}
				if(answer['PageID']){
					coverForm.PageID.value = answer['PageID'];
					coverForm.postID.value = answer['ID'];
					box.window.onsubmit=function(event){
						event.preventDefault();
						box.drop();
						var path = location.pathname.split(/\//);
						location.pathname = "/stories/"+(path[2] || params['language'])+"/"+(path[3] || 1)+"/"+answer['ID']+"/"+params['language'];
					}
				}
			}
		});
	}
	return false;
}
function getContent(onSave, PageID){
	var story = [];
	var container = document.querySelector("#slideshow");
	container.querySelectorAll("figure").forEach(function(item,i){
		var card = {};
		var background = item.querySelector(".background");
		if(background.nodeName=="IMG"){
			card['bgtype'] = "image";
		}else if(background.nodeName=="VIDEO"){
			card['bgtype'] = "video";
			card['mime'] = background.getAttribute("type");
		}
		card['background'] = background.src;

		var caption = item.querySelector("figcaption");
		var header = caption.querySelector("h1");
		var text = caption.querySelector("div");
		if(header.textContent.trim()){
			card['caption'] = header.textContent.trim();
			card['caption-bg-color'] = header.getCss("background-color");
			card['caption-text-color'] = header.getCss("color");
		}
		if(text.textContent.trim()){
			card['text'] = text.textContent.trim();
			card['text-bg-color'] = text.getCss("background-color");
			card['text-color'] = text.getCss("color");
		}
		card['align'] = caption.className;
		
		card['txtalign'] = caption.getCss("text-align");
		story.push(card);
	});
	return story;
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