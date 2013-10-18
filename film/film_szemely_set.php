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
<?php $page->printPageHead(""); ?>
<link href="files/menu/style.css" rel="stylesheet" type="text/css" />
<title>Személy módosítása</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(2,1);?>
<?php $page->printPageClose(); ?>
</body>
</html>