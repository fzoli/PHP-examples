<?php
include "class.php";
header ('Content-Type: text/html; charset=utf-8');
$page = new Page($userDbName);
$user=$page::$user;
if(!$user->isJog('elnev','root')) { header('location: filmek.php'); exit(); }
$film = new Film($filmDbName);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php $film->printFilmHead(); ?>
<script type="text/javascript">
/*<![CDATA[*/
var nyelv=false,csatorna=false,kodolas=false;
$().ready(function() {
	function formatItem(row) {
		return row[0] + " (<strong>id: " + row[1] + "<\/strong>)";
	}
	$("#nyelvMegnev").autocomplete("film_list_nyelv.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#nyelvMegnev").result(function(event, data, formatted) {
		if (data) { $("#nyelvAzon").val(data[1]); nyelv=true;}
		else nyelv=false;
	});
	$("#csatornaMegnev").autocomplete("film_list_csatorna.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#csatornaMegnev").result(function(event, data, formatted) {
		if (data) { $("#csatornaAzon").val(data[1]); csatorna=true;}
		else csatorna=false;
	});
	$("#kodolasMegnev").autocomplete("film_list_kodolas.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#kodolasMegnev").result(function(event, data, formatted) {
		if (data) { $("#kodolasAzon").val(data[1]); kodolas=true;}
		else kodolas=false;
	});
	$("form").submit(function(){return nyelv&&csatorna&&kodolas;});
});
/*]]>*/
</script>
<title>Hang hozzáadása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,5); ?>
<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" >
<table style="margin-left:auto; margin-right:auto">
<tr>
<td>Nyelv: </td>
<td>
<input type="text" id="nyelvMegnev" />
<input type="hidden" name="nyelv" id="nyelvAzon"/>
</td>
</tr>
<tr>
<td>Csatorna: </td>
<td>
<input type="text" id="csatornaMegnev" />
<input type="hidden" name="csatorna" id="csatornaAzon"/>
</td>
</tr>
<tr>
<td>Kódolás: </td>
<td>
<input type="text" id="kodolasMegnev" />
<input type="hidden" name="kodolas" id="kodolasAzon"/>
</td>
</tr>
<tr>
<td colspan="2" class="toCenter"><input type="submit" value="Hozzáadás" /></td>
</tr>
</table>
</form>
<div class="toCenter">
<?php
if(!empty($_POST)) {
	$film=new Film($filmDbName);
	print ($film->addHangok($_POST['nyelv'],$_POST['csatorna'],$_POST['kodolas']))?"Nyelv hozzáadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>