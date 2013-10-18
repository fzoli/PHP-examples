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
var keparany=false,felbontas=false;
$().ready(function() {
	function formatItem(row) {
		return row[0] + " (<strong>id: " + row[1] + "<\/strong>)";
	}
	$("#keparanyMegnev").autocomplete("film_list_keparany.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#keparanyMegnev").result(function(event, data, formatted) {
		if (data) { $("#keparanyAzon").val(data[1]); keparany=true;}
		else keparany=false;
	});
	$("#felbontasMegnev").autocomplete("film_list_felbontas.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#felbontasMegnev").result(function(event, data, formatted) {
		if (data) { $("#felbontasAzon").val(data[1]); felbontas=true;}
		else felbontas=false;
	});
	$("form").submit(function(){return keparany&&felbontas;});
});
/*]]>*/
</script>
<title>Kép hozzáadása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,4); ?>
<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" >
<table style="margin-left:auto; margin-right:auto">
<tr>
<td>Képarány: </td>
<td>
<input type="text" id="keparanyMegnev" />
<input type="hidden" name="keparany" id="keparanyAzon"/>
</td>
</tr>
<tr>
<td>Felbontás: </td>
<td>
<input type="text" id="felbontasMegnev" />
<input type="hidden" name="felbontas" id="felbontasAzon"/>
</td>
</tr>
<tr>
<td>Színes: </td>
<td>
<input name="szines" type="checkbox" value="1" checked="checked" id="szines" />
</td>
</tr>
<tr>
<td>3D: </td>
<td>
<input name="harom_d" type="checkbox" value="1" id="harom_d" />
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
	print ($film->addKepek($_POST))?"Kép hozzáadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>