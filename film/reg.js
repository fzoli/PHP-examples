(function() {

var Datum = function(ev,ho,nap) {
  this.ev=Number(ev); this.ho=Number(ho); this.nap=Number(nap);

	this.check = function() {
		return this.checkDate() && this.checkMin() && this.checkMax();
	}

	this.checkDate = function() {
		return this.ho > 0 && this.ho < 13 && this.ev > 0 && this.ev < 32768 && this.nap > 0 && this.nap <= (new Date(this.ev, this.ho, 0)).getDate();
	}
	
	this.checkMax = function() {
		var datum=(new Date(this.ev,this.ho-1,this.nap)).getTime();
		var ma=(new Date(new Date().getFullYear(),new Date().getMonth(),new Date().getDate())).getTime();
		return datum<=ma;
	}
	
	this.checkMin = function() {
		var minimum=(new Date()).getFullYear()-120;
		return this.ev>=minimum;
	}
	
}

$(document).ready(function() {
	$("#submit").attr('disabled',false);
	textReplacement($('#info'));

	$("#emailKey").click(function(){
		if ($("#email_publikus").attr('value')=='1') {
			$("#email_publikus").attr('value','0');
			$("#emailKey").attr('src','files/icon_lock.png');
		}
		else {
			$("#email_publikus").attr('value','1');
			$("#emailKey").attr('src','files/icon_unlock.png');
		}
	});

	function hibaBe(id) {
		$('#'+id).css('background-color','red').css('color','white');
	}
	
	function hibaKi(id) {
		$('#'+id).css('background-color','rgb(30,144,255)').css('color','black');
	}

	jQuery(function($){
		$.mask.definitions['w']='[qwertzuioplkjhgfdsayxcvbnm0123456789]';
		$.mask.definitions['_']='[qQwWeErRtTzZuUiIoOpPaAsSdDfFgGhHjJkKlLyYxXcCvVbBnNmM0123456789]';
		$.mask.definitions['.']='[qQwWeErRtTzZuUiIoOpPaAsSdDfFgGhHjJkKlLyYxXcCvVbBnNmM]';
		$.mask.definitions['q']='[öÖüÜóÓqQwWeErRtTzZuUiIoOpPőŐúÚaAsSdDfFgGhHjJkKlLéÉáÁűŰíÍyYxXcCvVbBnNmM -.]';
		$("#azon").mask(".__?____________",{placeholder:""});
		$("#jelszo,#jelszo2").mask("______?_________",{placeholder:""});
		$("#nev").mask("qqq?qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq",{placeholder:""});
		$("#sz_datum").mask("9999-99-99");
		$("#captchaInput").mask("wwwww",{placeholder:""});
	});

	$('#captchaForm').submit(function(){
		ok=checkCaptcha();
		if (!ok) hibaBe('captchaInput');
		return ok;
	})

	function checkAzon() {
		var ok=/^[A-Za-z]{1,1}[A-Za-z0-9]{2,14}$/;
		return ok.test($('#azon').attr('value'));
	}

	function checkJelszo() {
		var ok=/^[a-zA-Z0-9]{6,15}$/;
		return ok.test($('#jelszo').attr('value'))&&$('#jelszo').attr('value')==$('#jelszo2').attr('value');
	}

	function checkNev() {
		var ok=/^[A-ZÁ-Űa-zá-ű]{1,1}[A-ZÁ-Űa-zá-ű\.\ \-]{1,38}[a-zá-ű]{1,1}$/;
		return ok.test($('#nev').attr('value'));
	}
	
	function checkSzDatum() {
		var ok=/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}$/;
		var datum=$('#sz_datum').attr('value');
		ok=ok.test(datum);
		if (ok) {
			var ev,ho,nap;
			ev=Number(datum.substr(0,4));
			ho=Number(datum.substr(5,2));
			nap=Number(datum.substr(8,2));
			datum = new Datum(ev,ho,nap);
			ok=datum.check();
		}
		return ok;
	}

	function checkCaptcha() {
		var ok=/^[0-9a-z]{5,5}$/;
		return ok.test($('#captchaInput').attr('value'));
	}

	function checkRegForm() {
		ret=true;
		ok=checkAzon();
		ret=ret&&ok
		if (!ok) hibaBe('azon');
		else hibaKi('azon');
		ok=checkJelszo();
		ret=ret&&ok;
		if (!ok) {hibaBe('jelszo');hibaBe('jelszo2');}
		else {hibaKi('jelszo');hibaKi('jelszo2');}
		ok=checkNev();
		ret=ret&&ok
		if (!ok) hibaBe('nev');
		else hibaKi('nev');
		ok=checkEmail();
		ret=ret&&ok
		if (!ok) hibaBe('email');
		else hibaKi('email');
		ok=checkSzDatum();
		ret=ret&&ok
		if (!ok) hibaBe('sz_datum');
		else hibaKi('sz_datum');
		return ret;
	}

	$('input').change(function(){
		if (checkAzon()) hibaKi('azon');
		if (checkJelszo()) {hibaKi('jelszo');hibaKi('jelszo2');}
		if (checkNev()) hibaKi('nev');
		if (checkEmail()) hibaKi('email');
		if (checkSzDatum()) hibaKi('sz_datum');
	})

	$('#captchaInput').change(function(){
		if (checkCaptcha()) hibaKi('captchaInput');
	})

	$('#regForm').submit(function(){
		$('#jelszoH1').attr('value',hex_md5($('#jelszo').attr('value')));
		$('#jelszoH2').attr('value',hex_md5($('#jelszo2').attr('value')));
		return checkRegForm();
	})

	function textReplacement(input) {
		var def_id = "def_"+input.attr('id');
		var originalvalue = window[def_id];
		input.focus( function(){
			if( $.trim(input.val()) == originalvalue ){ input.val(''); }
		});
		input.blur( function(){
			if( $.trim(input.val()) == '' ){ input.val(originalvalue); }
		});
	}

	$(function(){
		$(".jtip").tooltip({
			track: true,
			delay: 0,
			showURL: false,
			opacity: 1,
			fixPNG: true,
			showBody: " - ",
			extraClass: "pretty fancy",
			top: -15,
			left: 5
		});
	});

	function checkEmail() {
		var ok=/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:biz|cat|com|edu|gov|int|mil|net|org|pro|tel|aero|arpa|asia|coop|info|jobs|mobi|name|museum|travel|hrvatska|a[cdefgilmnoqrstuwxz]|b[abdefghijmnorstvwyz]|c[acdfghiklmnoruvxyz]|d[ejkmoz]|e[ceghrstu]|f[ijkmorx]|g[abdefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnprwyz]|l[abcfikrstuvy]|m[acdeghklmnopqrstuvwxyz]|n[acefgilopruz]|o[m]|p[aefghklmnprstwy]|q[a]|r[eosuw]|s[abcdeghijklmnortuvyz]|t[cdfghjklmnoprtvwz]|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amrw])\b/i;
		return ok.test($('#email').attr('value'));
	}

});

})();