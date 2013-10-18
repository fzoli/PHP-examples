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
var film=false,tipus=false,beszerzes=false;
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
	$("#tipusMegnev").autocomplete("film_list_lemeztipus.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#tipusMegnev").result(function(event, data, formatted) {
		if (data) { $("#tipusAzon").val(data[1]); tipus=true;}
		else tipus=false;
	});
	$("#beszerzesMegnev").autocomplete("film_list_filmbeszerzes.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#beszerzesMegnev").result(function(event, data, formatted) {
		if (data) { $("#beszerzesAzon").val(data[1]); beszerzes=true;}
		else beszerzes=false;
	});
	$("form").submit(function(){return film&&tipus&&beszerzes;});
});
/*]]>*/
</script>
<title>Lemez hozzáadása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,9); ?>
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
<td>Típus: </td>
<td>
<input type="text" id="tipusMegnev" />
<input type="hidden" name="tipus" id="tipusAzon"/>
</td>
</tr>
<tr>
<td>Lemezek száma: </td>
<td>
<input type="text" name="lemez_db" value="1" />
</td>
</tr>
<tr>
<td>Beszerzés: </td>
<td>
<input type="text" id="beszerzesMegnev" />
<input type="hidden" name="film_beszerzes" id="beszerzesAzon"/>
</td>
</tr>
<tr>
<td>Menü: </td>
<td>
<input name="menu" type="checkbox" value="1" />
</td>
</tr>
<tr>
<td>Bővített kiadás: </td>
<td>
<input name="bovitett" type="checkbox" value="1" />
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
	print ($film->addLemez($_POST))?"Lemez hozzáadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>