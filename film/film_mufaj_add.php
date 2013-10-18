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
var film=false,mufaj=false;
$().ready(function() {
	function formatItem(row) {
		return row[0] + " (<strong>id: " + row[1] + "<\/strong>)";
	}
	$("#filmMegnev").autocomplete("film_list_film.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#filmMegnev").result(function(event, data, formatted) {
		if (data) { $("#filmAzon").val(data[1]); film=true;}
		else film=false;
	});
	$("#mufajMegnev").autocomplete("film_list_mufaj.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#mufajMegnev").result(function(event, data, formatted) {
		if (data) { $("#mufajAzon").val(data[1]); mufaj=true;}
		else mufaj=false;
	});
	$("form").submit(function(){return film&&mufaj;});
});
/*]]>*/
</script>
<title>Műfaj filmhezadása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,10); ?>
<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" >
<table style="margin-left:auto; margin-right:auto">
<tr>
<td>Film: </td>
<td>
<input type="text" id="filmMegnev" />
<input type="hidden" name="film" id="filmAzon"/>
</td>
</tr>
<tr>
<td>Műfaj: </td>
<td>
<input type="text" id="mufajMegnev" />
<input type="hidden" name="mufaj" id="mufajAzon"/>
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
	print ($film->addMufaj($_POST))?"A műfaj a filmhez hozzáadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>