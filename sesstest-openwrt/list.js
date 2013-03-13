$(document).ready(function() {

	var goAjax = true;

	function refreshList() {
		var phpfile = 'index.php';

		if (goAjax) {
			goAjax = false;
			$.ajax({
				type: "POST",
				url: phpfile,
				data: "xml=sessinfo",
				dataType: "xml",
				cache: false,
				async:true,
				success: function(xml) {
					var guests = $(xml).find('response').attr('guests');
					var visitors = $(xml).find('response').attr('visitors');
					var loginedVisitors = Math.abs(guests - visitors);

					var start = "\r\nBejelentkezett felhasználók:\r\n";
					var list = "";
					var loginedUsers = 0;
					$(xml).find('user').each(function(){
						list += $(this).attr('name');
						list += ' (';
						list += $(this).attr('count');
						list += ' helyen)\r\n';
						loginedUsers++;
					});
				
					$('span#visitors').html(visitors);
					$('span#guests').html(guests);
					$('span#loginedVisitors').html(loginedVisitors);
					$('span#loginedUsers').html(loginedUsers);
					$('span#list').html((loginedUsers > 0) ? start + list : list);

					goAjax = true;
				}
			});
		}
	}

	setInterval(function() {
		refreshList();
	}, 20000);

	$('span#refreshButtonTag').html('<input type="button" id="refreshList" value="Lista frissítése" />');

	$('input#refreshList').click(function() {
		refreshList();
	});

	

});
