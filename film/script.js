//var H = $.noConflict(true);

(function() {

$(document).ready(function() {

	getCookie = function(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}
	if (($("#showLoginButton").attr("value")=="Bejelentkezés") && (getCookie("fzoltan_sessid")!=null)) setTimeout(location.reload(true),100);

	$("#submit").attr('disabled',false);
	$("#reg").attr('rev',"width: 750px; height: 350px; scrolling: no;");
	textReplacement($('#azon'));
	textReplacement($('#jelszo'));

	function hibaBe(id) {
		$('#'+id).css('background-color','red').css('color','white');
	}
	
	function hibaKi(id) {
		$('#'+id).css('background-color','rgb(30,144,255)').css('color','black');
	}
	
	function jelszoHash() {
		$("#hash").attr('value',hex_md5(hex_md5(String($("#jelszo").attr('value')))+fuszer));
	}

	function login() {
		if (!checkAzon()) hibaBe('azon');
		if (checkJelszo()) jelszoHash(fuszer);
		else hibaBe('jelszo');
		return checkAzon() && checkJelszo();
	} 

	function checkAzon() {
		var ok=/^[A-Za-z]{1,1}[A-Za-z0-9]{2,14}$/;
		return ok.test($("#azon").attr('value'));
	}

	function checkJelszo() {
		var ok=/^[a-zA-Z0-9]{6,15}$/;
		return ok.test($("#jelszo").attr('value'));
	}

	$(".button").click(function(){
		history.go();
	});

	$("#login").submit(function(){
		return login();
	});
	
	$(".input").change(function(){
		if(checkAzon()) hibaKi('azon');
		if(checkJelszo()) hibaKi('jelszo');
	});

function textReplacement(input) {
 var originalvalue = input.val();
 input.focus( function(){
  if( $.trim(input.val()) == originalvalue ){ input.val(''); }
 });
 input.blur( function(){
  if( $.trim(input.val()) == '' ){ input.val(originalvalue); }
 });
}

});

})();