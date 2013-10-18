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
var kat=false;
$().ready(function() {
	function formatItem(row) {
		return row[0] + " (<strong>id: " + row[1] + "<\/strong>)";
	}
	$("#katMegnev").autocomplete("film_list_kategoria.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#katMegnev").result(function(event, data, formatted) {
		if (data) { $("#katAzon").val(data[1]); kat=true;}
		else kat=false;
	});
	$("form").submit(function(){return kat&&(document.getElementById("megnev").value.length>0);});
});
/*]]>*/
</script>
<title>Lista bővítése</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,2); ?>
<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" >
<table style="margin-left:auto; margin-right:auto">
<tr>
<td>Megnevezés: </td>
<td><input type="text" name="megnevezes" id="megnev" /></td>
</tr>
<tr>
<td>Kategória: </td>
<td>
<input type="text" id="katMegnev" />
<input type="hidden" name="kategoria" id="katAzon"/>
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
	print ($film->addLista($_POST['megnevezes'],$_POST['kategoria']))?"'".$_POST['megnevezes']."' megnevezés hozzáadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>