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
var film=false,szemely=false,munka=false;
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
	$("#szemelyMegnev").autocomplete("film_list_szemely.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#szemelyMegnev").result(function(event, data, formatted) {
		if (data) { $("#szemelyAzon").val(data[1]); szemely=true;}
		else szemely=false;
	});
	$("#munkaMegnev").autocomplete("film_list_szerep.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#munkaMegnev").result(function(event, data, formatted) {
		if (data) { $("#munkaAzon").val(data[1]); munka=true;}
		else munka=false;
	});
	$("#szinkronMegnev").autocomplete("film_list_szemely.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#szinkronMegnev").result(function(event, data, formatted) {
		if (data) $("#szinkronAzon").val(data[1]);
	});
	$("form").submit(function(){return film&&szemely&&munka;});
});
/*]]>*/
</script>
<title>Szereplő filmhezadása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,8); ?>
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
<td>Személy: </td>
<td>
<input type="text" id="szemelyMegnev" />
<input type="hidden" name="szemely" id="szemelyAzon"/>
</td>
</tr>
<tr>
<td>Szerep: </td>
<td>
<input type="text" id="munkaMegnev" />
<input type="hidden" name="munka" id="munkaAzon"/>
</td>
</tr>
<tr>
<td>Szinkron: </td>
<td>
<input type="text" id="szinkronMegnev" />
<input type="hidden" name="szinkron" value="null" id="szinkronAzon"/>
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
	print ($film->addSzerep($_POST))?"Személy hozzáadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>