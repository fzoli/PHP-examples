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
var kh=false,nemz=false;
$().ready(function() {
	function formatItem(row) {
		return row[0] + " (<strong>id: " + row[1] + "<\/strong>)";
	}
	$("#korhatarMegnev").autocomplete("film_list_korhatar.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#korhatarMegnev").result(function(event, data, formatted) {
		if (data) { $("#korhatarAzon").val(data[1]); kh=true;}
		else kh=false;
	});
	$("#nemzetisegMegnev").autocomplete("film_list_nemzetiseg.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#nemzetisegMegnev").result(function(event, data, formatted) {
		if (data) { $("#nemzetisegAzon").val(data[1]); nemz=true;}
		else nemz=false;
	});
	$("form").submit(function(){return nemz&&kh&&(document.getElementById("cim").value.length>0);});
});
/*]]>*/
</script>
<title>Film hozzáadása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,3); ?>
<form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"] ?>" method="post">
<table style="margin-left:auto; margin-right:auto">
<tr>
<td>Cím: </td>
<td><input class="film_input" type="text" name="cim" id="cim" value="<?php $page->printPost('cim',''); ?>" /></td>
</tr>
<tr>
<td>Angol cím: </td>
<td><input class="film_input" type="text" name="angol_cim" id="angol_cim" value="<?php $page->printPost('angol_cim',''); ?>" /></td>
</tr>
<tr>
<td>Nemzetiség: </td>
<td>
<input class="film_input" type="text" id="nemzetisegMegnev" />
<input type="hidden" name="nemzetiseg" id="nemzetisegAzon"/>
</td>
</tr>
<tr>
<td>Hossz: </td>
<td><input class="film_input" type="text" name="hossz" id="hossz" value="<?php $page->printPost('hossz',''); ?>" /></td>
</tr>
<tr>
<td>Gyártási év: </td>
<td><input class="film_input" type="text" name="gyart_ev" id="gyart_ev" value="<?php $page->printPost('gyart_ev',''); ?>" /></td>
</tr>
<tr>
<td>Korhatár: </td>
<td>
<input class="film_input" type="text" id="korhatarMegnev" />
<input type="hidden" name="korhatar" id="korhatarAzon"/>
</td>
</tr>
<tr>
<td>Leírás: </td>
<td><textarea class="film_input" id="leiras" name="leiras" rows="0" cols="0"><?php $page->printPost('leiras',''); ?></textarea></td>
</tr>
<tr>
<td>Borító: </td>
<td>
<input id="borito" type="file" name="borito" />
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
	print ($film->addFilm($_POST,$_FILES['borito']))?"'".$_POST['cim']."' hozzáadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>