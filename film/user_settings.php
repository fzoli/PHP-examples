<?php
include "class.php";
header ('Content-Type: text/html; charset=utf-8');
$page = new Page($userDbName);
if (!(bool)$_SESSION['azon']) { header('location: index.php'); exit(); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php $page->printPageHead(""); ?>
<title>Profil beállítása</title>
</head>
<body>
<?php $page->printPageOpen(); ?>
            <a href="logout_all.php">Kijelentkezés mindenhonnan</a>
            <br />
            <a href="user_set_probalkozas.php">Belépési lehetőség visszaállítása</a>
<?php $page->printPageClose(); ?>
</body>
</html>