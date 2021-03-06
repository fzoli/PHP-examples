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
var nyelv=false;
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
	$("form").submit(function(){return nyelv;});
});
/*]]>*/
</script>
<title>Felirat hozzáadása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,6); ?>
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
<td>Néma: </td>
<td>
<input name="nema" type="checkbox" value="1" id="nema" />
</td>
</tr>
<tr>
<td>Kommentár: </td>
<td>
<input name="kommentar" type="checkbox" value="1" id="kommentar" />
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
	print ($film->addFeliratok($_POST))?"Felirat hozzáadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>