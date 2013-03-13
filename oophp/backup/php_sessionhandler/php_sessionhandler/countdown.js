$(document).ready(function(){

	var reqTime = new Date();
	var phpfile = 'index.php';
	var goAjax = true;

	$.cookie('test', 'test');
	var cookie_enabled = ($.cookie('test') == 'test');
	$.cookie('test', null);

	function counterFormat(date) {
		var minute = date.getUTCMinutes();
		var second = date.getUTCSeconds();
		return minute + ':' + second;
	}

	if (cookie_enabled) {

	$.ajax({
		type: "POST",
		url: phpfile,
		data: "xml=lifetime", //itt nem lehet = előtt, után szóköz...
		dataType: "xml",
		cache: false,
		async:true,
		success: function(xml) {

			var interval = 1000;

			var lifetime = $(xml).find('response').attr('lifetime');
			lifetime *= 1000;

			var expire = new Date(reqTime.getTime() + lifetime);

			function refreshCounter() {
				var countdown = new Date(expire - new Date());
				if (countdown.getTime() > 0) {
					$('#counter').html(counterFormat(countdown));
				}
				else {
					if (countdown.getTime() <= -60000) {
						//nem fog megtörténni, ha nem piszkálják az adatbázist és ha a szerver válaszol időben
						$('#counter').html('A munkamenet már biztosan törlődött.');
					}
					else {
						$('#counter').html('A munkamenet 1 percen belül biztosan törlődik: ' +
							Math.round(60 + (countdown.getTime() / 1000)));
					}
				}
			}	

			setInterval(function() {
				refreshCounter();
			}, interval);

			refreshCounter();

		}
	});

	setInterval(function() {

		if (goAjax) {
			goAjax = false;
			$.ajax({
				type: "POST",
				url: phpfile,
				data: "xml=isSessSet",
				dataType: "xml",
				cache: false,
				async:true,
				success: function(xml) {
					if ($(xml).find('response').attr('set') == '0') {
						//átirányítás, ha már nem létezik a munkamenet
						$(window.location).attr('href', phpfile);
					}
					goAjax = true;
				}
			});
		}

	}, 20000);

	} else $('#loginForm input').attr('disabled', 'disabled');

});
