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
var lemez=false,felirat=false;
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
	$("#feliratMegnev").autocomplete("film_list_feliratok.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#feliratMegnev").result(function(event, data, formatted) {
		if (data) { $("#feliratAzon").val(data[1]); felirat=true;}
		else felirat=false;
	});
	$("form").submit(function(){return lemez&&felirat;});
});
/*]]>*/
</script>
<title>Felirat lemezhez adása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,14); ?>
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
<td>Felirat: </td>
<td>
<input type="text" id="feliratMegnev" />
<input type="hidden" name="felirat" id="feliratAzon"/>
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
	print ($film->addFelirat($_POST))?"Felirat lemezhezadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>