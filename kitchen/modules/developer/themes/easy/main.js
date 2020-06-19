var Page = { id:0, url:"", shares:0, comments:0, section:0 }
var citizen = { App:"self",	ID:0, Name:"", Email:"n/a", options:{ gender:"undefined"} }
var timers = {
	LoginInvite:30000,
	analytics:300000
}
var analytics;
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
		//setTimeout(ShowInvite, timers.LoginInvite);
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
			XHR.push({
				"protect":false,
				"addressee":"/xhr/community/login",
				"body":JSON.stringify(params)
			});
		});
	},{scope: 'public_profile,email'});
	return false;
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