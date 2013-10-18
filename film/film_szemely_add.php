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
$().ready(function() {
	$("form").submit(function(){return (document.getElementById("nev").value.length>0);});
});
/*]]>*/
</script>
<title>Személy hozzáadása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(1,1); ?>
<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" >
<table style="margin-left:auto; margin-right:auto">
<tr>
<td>Név: </td>
<td><input type="text" name="nev" id="nev" value="<?php $page->printPost('nev',''); ?>" /></td>
</tr>
<tr>
<td>Nem: </td>
<td>
<select name="nem" id="nem">
<option value="1" <?php $page->printSelected('nem','1'); ?>>Férfi</option>
<option value="0" <?php $page->printSelected('nem','0'); ?>>Nő</option>
</select>
</td>
</tr>
<tr>
<td>Szül. év: </td>
<td><input type="text" name="szul_datum" id="szul_datum" value="<?php $page->printPost('szul_datum',''); ?>" /></td>
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
	print ($film->addSzemely($_POST['nev'],$_POST['nem'],$_POST['szul_datum']))?"'".$_POST['nev']."' személy hozzáadva!":'Hiba! A MySQL mondta: '.mysql_error();
}
?>
</div>
<?php $page->printPageClose(); ?>
</body>
</html>