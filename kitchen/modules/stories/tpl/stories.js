/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	standby.leftbar = "post-feed";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
	
	doc.querySelectorAll("#heading>img").forEach(function(obj){
		reauth();
		var frame = doc.create("iframe","",{ "id":obj.className, "src":"/uploader/image-frame", "class":"uploader-frame ", "height":obj.getAttribute("height")});
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
				card = doc.create("figcaption");
				field = doc.create("div", "", {
					"data-animate":"fade-in",
					"data-background":"transparent",
					"style":"background-color:transparent"
				});
				word = doc.create("span","",{
					"contenteditable":"true",
					"data-font":"16px",
					"data-color":"black",
					"data-background":"transparent",
					"style":"font-size:16px;color:black;background-color:transparent"
				});
				word.onfocus = function(){
					word = this;
					field = word.parentNode;
					card = field.parentNode

					options.reset();
					options['animate'].value = field.dataset.animate;
					options['text-align'].value = field.getCss("text-align");
					options['font-size'].value = word.getCss("font-size");
					options['color'].value = word.style.color;
					options['field-bg'].value = word.style.backgroundColor;
					options['block-bg'].value = field.style.backgroundColor;
					options['flex'].value = card.className;
				}
				word.onpaste = function(event){
					pasteCaption(event);
				}
				field.appendChild(word);
				card.appendChild(field);
				card.appendChild(doc.create("span","©",{
					class:"copyright",
					contenteditable:"true"
				}));
				slide.appendChild( card );

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
			response = JSON.parse(response);

			var path = JSON.parse(window.localStorage.uploader);
				path['subdomain'] = "img";
				path['img'] = "../img/data/"+response['year']+"/"+response['month']+"/"+response['id'];
				window.localStorage.uploader = JSON.encode(path);

			location.pathname = "/stories/"+response['language']+"/1/"+response['id']+"/"+response['language'];
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
		"preview":coverForm.querySelector("#landscape").contentWindow.document.getImage(),
		"portrait":coverForm.querySelector("#portrait").contentWindow.document.getImage(),
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
			"addressee":"/stories/actions/save",
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
					
					var container = document.querySelector("#slideshow");
					
					/******* Save Content *******/
					saveContent(function(response){
						if(isNaN(response)){
							log.appendChild(doc.create("h3", "<b>bContent</b> - <span class='red'>Failed save</span>"));
						}else log.appendChild(doc.create("h3", "👌<b>Content</b> - <span class='green'>Saved</span>"));
						
						convertToAMP(function(response){
							if(isNaN(response)){
								log.appendChild(doc.create("h3", "Google AMP - <span class='red'>Failed save</span>"));
							}else log.appendChild(doc.create("h3", "👌Google AMP - <span class='green'>Saved</span>"));
							
							box.align();
						}, answer.log['PageID'], container);

					}, answer.log['PageID'], container);
				}
			}
		});
	}
	return false;
}
function saveContent(onSave, PageID, container){
	if(PageID){
		container = container.cloneNode(true);
		container.querySelectorAll("figure>figcaption>div>span").forEach(function(item){
			item.removeAttribute("contenteditable");
		});
		container.querySelectorAll("script").forEach(function(item){
			item.parentNode.removeChild(item);
		});
		XHR.push({
			"Content-Type":"text/html",
			"addressee":"/blogger/actions/save-content/"+PageID,
			"body":container.innerHTML,
			"onsuccess":onSave
		});
	}
}
function convertToAMP(onSave, PageID, container){
	var story = [];
	container.querySelectorAll("figure").forEach(function(fig, i){
		let card = {"type":"","src":"","width":"","height":"","fields":[],"copyright":""};
		let bg = fig.querySelector("img,video");
		if(bg.nodeName=="IMG"){
			card['type'] = "image";
			card['src'] = bg.src;
			card['width'] = bg.naturalWidth;
			card['height'] = bg.naturalHeight;
		}else if(bg.nodeName=="VIDEO"){
			card['type'] = "video";
			card['src'] = bg.src;
			card['width'] = bg.videoWidth;
			card['height'] = bg.videoHeight;
		}
		card['justify'] = fig.querySelector("figcaption").className || "center";
		fig.querySelectorAll("figcaption>div").forEach(function(field){
			var fields = {
				align:field.className || "left",
				animate:field.dataset.animate || "fly-in-left",
				background:(field.dataset.background+"-bg").toLowerCase(),
				words:[]
			};
			field.querySelectorAll("span").forEach(function(word){
				fields.words.push({
					font:word.dataset.font,
					color:word.dataset.color.toLowerCase(),
					background:(word.dataset.background+"-bg").toLowerCase(),
					content:word.innerHTML.replace(/\n+/g,"").replace(/"/g,"″")
				});
			});;
			card['fields'].push(fields);
		});
		let copyright = fig.querySelector("figcaption>span.copyright");
		if(copyright){
			card['copyright']=copyright.textContent.trim().replace(/\n+/g,"").replace(/"/g,"″");
		}
		story.push(card);
	});

	XHR.push({
		"Content-Type":"text/html",
		"addressee":"/stories/actions/save-amp/"+PageID,
		"body":JSON.encode(story),
		"onsuccess":onSave
	});	
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

function RGBtoHEX(rgb){
    rgb = rgb.replace(/[^0-9,]/g,"").split(/,/);
    for(var i=0; i<3; i++){
    	rgb[i] = ("0" + parseInt(rgb[i],10).toString(16)).slice(-2)
    }
    return "#"+rgb.join("");
}

/****************************************************************/

function copyURL(field){
	field.select();
	document.execCommand('copy');
}