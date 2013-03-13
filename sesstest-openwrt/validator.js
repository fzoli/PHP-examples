$(document).ready(function(){

	var tag = $('input#login');
	var temp_color = tag.css('color');
	var temp_backcolor = tag.css('background-color');

	function isLoginOk() {
		var login = tag.val();
		var ok = /^[A-Za-z]{1}[A-Za-z0-9.-_]{0,13}[A-Za-z0-9]{0,1}$/;
		return ok.test(login);
	}

	function loginRedOn() {
		tag.css('color','white');
		tag.css('background-color','red');
	}

	function loginRedOff() {
		tag.css('color', temp_color);
		tag.css('background-color', temp_backcolor);
	}

	tag.keyup(function(){
		if (isLoginOk()) loginRedOff();
	});

	$('form#loginForm').submit(function() {
		var ok = isLoginOk();
		if (!ok) loginRedOn();
		return ok;
	});

});
