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
var lemez=false,hang=false;
$().ready(function() {
	function formatItem(row) {
		return row[0] + " (<strong>id: " + row[1] + "<\/strong>)";
	}
	$("#lemezMegnev").autocomplete("film_list_lemez.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#lemezMegnev").result(function(event, data, formatted) {
		if (data) { $("#lemezAzon").val(data[1]); lemez=true;}
		else lemez=false;
	});
	$("#hangMegnev").autocomplete("film_list_hangok.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#hangMegnev").result(function(event, data, formatted) {
		if (data) { $("#hangAzon").val(data[1]); hang=true;}
		else hang=false;
	});
	$("form").submit(function(){return lemez&&hang;});
});
/*]]>*/
</script>
<title>Hang lemezhez adása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,13); ?>
<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" >
<table style="margin-left:auto; margin-right:auto">
<tr>
<td>Lemez: </td>
<td>
<input type="text" id="lemezMegnev" />
<input type="hidden" name="lemez" id="lemezAzon"/>
</td>
</tr>
<tr>
<td>Hang: </td>
<td>
<input type="text" id="hangMegnev" />
<input type="hidden" name="hang" id="hangAzon"/>
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
	print ($film->addHang($_POST))?"Hang lemezhezadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>