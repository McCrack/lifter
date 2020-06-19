
/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	standby.leftbar = "staff-list";
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
}

/*********************************************************/

function saveUser(form){
	var id = form.uid.value || 0;
	XHR.push({
		"addressee":"/staff/actions/save/"+id,
		"body":JSON.stringify({
			"id":id,
			"login":form.login.value,
			"passwd":form.password.value,
			"email":form.email.value,
			"name":form.user.value,
			"group":form.group.value,
			"departament":form.departament.value
		}),
		"onsuccess":function(response){
			isNaN(response) ? alert(response) : location.pathname = "/staff/"+response;
		}
	});
	return false;
}
function deleteUser(id){
	XHR.push({
		"Content-Type":"text/plain",
		"addressee":"/staff/actions/delete/"+id,
		"onsuccess":function(response){
			isNaN(response) ? alert(response) : location.pathname="/staff";
		}
	});
	return false;
}