function auth(form){
	var passwd = md5(form.passwd.value);
	session.open();
	session.setItem("login", form.login.value);
	session.setItem("passwd", passwd);
	COOKIE.set("finger", md5(form.login.value+passwd + COOKIE.get("key")), {"path":"/"});
	location.reload();
	return false;
}