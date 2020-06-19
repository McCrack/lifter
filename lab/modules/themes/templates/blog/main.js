var stream;
var Page = { id:0, url:"", shares:0, comments:0, section:0, published:4294967295 }
var citizen = { App:"self",	ID:0, Name:"", Email:"n/a", options:{ gender:"undefined"} }
var timers = {
	LoginInvite:30000,
	analytics:300000
}
var analytics;

var ToolSlider = function(){
	var tmr, scrltmr;
	var toolslider = this;
	var bar = doc.querySelector("#toolbar");
	var slides = bar.querySelectorAll(".tool-slide");
	var point = bar.parentNode.querySelectorAll(".slide-point");
	point[0].onclick = point[1].onclick = function(){
		toolslider.next(Number(this.dataset.dir));
	}
	toolslider.next = function(dir){
		dir = dir || 1;
		var sld = Number(bar.dataset.current)+dir;
		if(sld<0){
			toolslider.setSlide(0);
		}else if(sld>(slides.length-1)){
			toolslider.setSlide(slides.length-1);
		}else toolslider.setSlide(sld);
	}
	toolslider.setSlide = function(sld){
		var speed, offset = (slides[sld].offsetTop-bar.offsetTop);
		tmr = setInterval(function(){
			speed = (offset-bar.scrollTop)/4;
			if((offset - bar.scrollTop)>4){
				bar.scrollTop += speed;
			}else if((bar.scrollTop - offset) > 10){
				bar.scrollTop += speed;
			}else{
				bar.scrollTop = offset;
				clearInterval( tmr );
			}
		},20);
	}
	bar.onscroll=function(){
		clearTimeout(scrltmr);
		scrltmr = setTimeout(function(){
			bar.dataset.current = ((bar.scrollTop+10)/bar.offsetHeight)^0;
		}, 800);
	}
	toolslider.setSlide(0);
}

/***********************************************/

var Stream = function(default_node){
	var stream = this;
	var buffer = [];
	stream.seek = 0;
	default_node = default_node || "subfeed";
	stream.feed = doc.querySelector("#feed");
	stream.subfeed = doc.querySelector("#sidebar");
	stream.bottom = false;
	stream.bufferization = function(onLoad){
		if(stream.bottom){
			return false;
		}else stream.bottom = true;
		XHR.request("/xhr/feed/json/"+Page.published+"/"+stream.seek+"/"+Page.section, onLoad || function(xhr){
			var response = JSON.parse(xhr.response);
			if(response.length) stream.bottom = false;
			stream.seek += response.length;
			buffer = buffer.concat(response);
		}, '{}');
	}
	stream.getStickers = function(node){
		if(stream.bottom){ return false; }
		if(buffer.length<20){ stream.bufferization(); }
		var item;
		var endFeed = false;
		if(node==="feed"){
			for(var j=2; j--;){
				var stickers = [];
				if(buffer.length){
					item = buffer.shift();
					stickers.push(stream.createSticker(item, "minipost"));
					for(var i=4; i--;){
						if(buffer.length){
							item = buffer.shift()
							stickers.push(stream.createSticker(item, "sticker"));
						}else{
							endFeed = true;
							break;
						}
					}
				}else endFeed = true;
				stream.feed.insertAdjacentHTML("beforeEnd", stickers.join(" "));
			}
			if(!endFeed){
				
			}
		}else{
			for(var j=2; j--;){
				if(buffer.length){
					var stickers = [];
					item = buffer.shift();
					stickers.push(stream.createSticker(item, "minipost"));
					for(var i=4; i--;){
						if(buffer.length){
							item = buffer.shift();
							stickers.push("<a class='sticker' href='/"+item['ID']+"/"+item['header'].translite("-", true)+"' draggable='false'><figure><img src='"+item['preview']+"' align='right' hspace='5'><figcaption class='header'>"+item['header']+"</figcaption></figure></a>");
						}else{
							endFeed = true;
							break;
						}
					}
					stream.subfeed.insertAdjacentHTML("beforeEnd", stickers.join(" "));
				}
			}
		}
	}
	stream.createSticker = function(item, className){
		className = className || "minipost";
		return "<a class='"+className+"' href='/"+item['ID']+"/"+item['header'].translite("-", true)+"' draggable='false'><figure><img src='"+item['preview']+"'>"+
		"<figcaption class='header'>"+item['header']+"</figcaption>"+
		"<figcaption><div class='subheader'>"+item['subheader']+"</div><div class='options'>"+date("d M, Y", item['created']*1000)+"</div></figcaption></figure></a>";
	}
	stream.bufferization(function(xhr){
		var response = JSON.parse(xhr.response);
		if(response.length) stream.bottom = false;
		stream.seek += response.length;
		buffer = buffer.concat(response);
		stream.getStickers(default_node);
	});
}

/***********************************************/

function CheckLoginStatus(response){
	if(response.status === "connected"){
		FB.api("/"+Page.url, "GET", {access_token:response.authResponse.accessToken}, function(response){
			Page.shares = response.share.share_count;
			Page.comments = response.share.comment_count;
			var likebtn = doc.querySelectorAll(".share.fb");
			for(let i=likebtn.length; i--;){
				likebtn[i].innerHTML = "<span class='like-counter'>"+Page.shares+"</span>";
			}
		});
		FB.api("/me?fields=name,picture,email,gender", "get", function(response){
			citizen = {
				App:"facebook",
				ID:response.id,
				Name:response.name,
				Email:response.email,
				options:{ gender:response.gender }
			}
			doc.querySelector("#level-2.tool-slide").appendChild(doc.create("img","",{
				style:"height:38px;border-radius:3px;cursor:pointer",
				hspace:"6",
				vspace:"2",
				align:"top",
				onclick:"fb_logout()",
				src:response.picture.data.url
			}));
		});
	}else{
		doc.querySelector("#level-2.tool-slide").appendChild(doc.create("a","Login",{
			class:"login fb",
			onclick:"fb_login(false)"
		}));
		setTimeout(ShowInvite, timers.LoginInvite);
	}
}
function fb_logout(){
	FB.logout(function(response){
		location.reload();
	});
}
function fb_login(box){
	FB.login(function(response){
		FB.api("/me?fields=id,name,gender,email,picture", "get", function(response){
			if(box)	box.parentNode.removeChild(box);
			var btn = doc.querySelector("#toolbar .fb.login");
			btn.parentNode.replaceChild(doc.create("img","",{
				style:"height:38px;border-radius:3px;cursor:pointer",
				hspace:"6",
				vspace:"2",
				align:"top",
				onclick:"fb_logout()",
				src:response.picture.data.url
			}), btn);
			citizen = {
				App:"facebook",
				ID:response.id,
				Name:response.name,
				Email:(response.email || "n/a"),
				options:{ gender:response.gender }
			}
			XHR.request("/xhr/community/login", function(){}, JSON.stringify(params));
		});
	},{scope: 'public_profile,email'});
	return false;
}
function ShowInvite(){
	XHR.request("/xhr/community/invitebox", function(xhr){
		var substrate = doc.create("div", xhr.response, {id:"substrate"});
		doc.body.appendChild(substrate);
		
		var box = substrate.querySelector(".box");
		box.querySelector(".box-body").style.maxHeight=(doc.height-100)+"px";
		FB.XFBML.parse(substrate, function(){
			substrate.style.opacity = 1.0;
			substrate.style.paddingTop = "0px";
			box.style.top = "calc(50% - "+(box.offsetHeight/2)+"px)";
			box.style.left = "calc(50% - "+(box.offsetWidth/2)+"px)";
		});
	}, '{}');
}

var Analytics = function(){
    var analytics = this;
		analytics.flag = true;
		analytics.timer = Date.now();
	window.onbeforeunload = function(){
		if(analytics.flag){
			analytics.flag = false;
			var params = {
				"PageID":Page.id,
				"input":analytics.timer,
				"output":Date.now(),
				"shares":Page.shares,
				"comments":Page.comments,
				"App":citizen.App,
				"CitizenID":citizen.ID,
				"Name":citizen.Name,
				"Email":citizen.Email,
				"options":citizen.options
			}
			var XHR = new XMLHttpRequest();
				XHR.open("POST", "/xhr/community/analytics/"+(session.open() ? 1 : 0)+"/"+citizen.ID, false);
				XHR.setRequestHeader("Content-Type", "application/json");
				XHR.send(JSON.stringify(params));
		}
	}
}

/* Shares, likes and sends *************/

function share_fb(){	// Facebook
	FB.ui({
		method	: "share",
		href	: window.location.href,
	}, function(response){ });
	return false;
}
function share_gp(){	// Google +
	window.open("https://plus.google.com/share?url="+location.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=650,width=500");
	return false;
}
function share_vk(){
	window.open("http://vk.com/share.php?url="+location.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=650,width=500");
}
function share_twitter(){	// Twitter
	window.open("https://twitter.com/share?url="+location.href+"&text="+doc.querySelector("meta[property='og:title']").content, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=550,width=500");
	return false;
}
function share_ok(){
	window.open("http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st._surl="+window.location.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=450,width=600");
}
function send_viber(){		// Viber
	window.open("viber://forward?text="+location.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=320");
	return false;
}
function send_telegram(){	// Telegram
	window.open("tg://msg?url="+location.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=320");
	return false;
}
function send_whatsapp(){	// WhatsApp
	window.open("whatsapp://send?text="+location.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=320");
	return false;
}
function send_fb(){
	FB.ui({
		method:"send",
		link:location.href,
		display:"iframe"
	});
}
function like_fb(){
	// Функция требует розширенного publish_actions!!!
	FB.api("me/og.likes", "post", {"object": Page.url }, function(response){
		
	});
}

var change_theme_timeout;
function changeThemes(form){
	clearTimeout(change_theme_timeout);
	change_theme_timeout = setTimeout(function(){
		var mask = 0;
		var inp = form.querySelectorAll("input");
		for(var i=inp.length; i--;){
			if(inp[i].checked){
				mask |= inp[i].value;
			}
		}
		var path = location.pathname.split(/\//);
		path[2] = mask;
		location.pathname = path.join("/");
	}, 1000);
}