var form = doc.querySelector("#ads");
var list = form.querySelector("#ads-list");
	list.scrollTop = list.scrollHeight;
	form.onsubmit = function(){
		var field = form.message;
		XHR.push({
			"Content-Type":"text/plain",
			"addressee":"/ads/actions/submit",
			"body":field.value,
			"onsuccess":function(response){
				list.innerHTML = response;
				list.scrollTop = list.scrollHeight;
				field.value = "";
			}
		});
		return false;
	}