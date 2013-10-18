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
var f=false;
$().ready(function() {
	function formatItem(row) {
		return row[0] + " (<strong>id: " + row[1] + "<\/strong>)";
	}
	$("#fMegnev").autocomplete("film_list_film.php", {
		selectFirst: false,
		formatItem: formatItem
	});
	$("#fMegnev").result(function(event, data, formatted) {
		if (data) { $("#fAzon").val(data[1]); f=true;}
		else f=false;
	});
	$("form").submit(function(){return f&&(document.getElementById("file").value.length>0);});
});
/*]]>*/
</script>
<title>Film pillanatkép hozzáadása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,16); ?>
<form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"] ?>" method="post">
<table style="margin-left:auto; margin-right:auto">
<tr>
<td>Film: </td>
<td>
<input type="text" id="fMegnev" />
<input type="hidden" name="film" id="fAzon"/>
</td>
</tr>
<tr>
<tr>
<td>Kép: </td>
<td>
<input type="file" name="file" />
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
	print ($film->addSnapshot($_POST['film'],$_FILES['file']))?"Hozzáadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>