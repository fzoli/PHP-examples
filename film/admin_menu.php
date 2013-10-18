<?php
include "class.php";
header ('Content-Type: text/html; charset=utf-8');
$page = new Page($userDbName);
$user=$page::$user;
if($user->isJog('change','0')) { header('location: index.php'); exit(); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php $page->printPageHead(""); ?>
<title>Adminisztráció</title>
</head>
<body>
<?php $page->printPageOpen(); ?>
            Adminisztráció
<?php $page->printPageClose(); ?>
</body>
</html>