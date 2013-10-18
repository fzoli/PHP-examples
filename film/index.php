<?php
include "class.php";
header ('Content-Type: text/html; charset=utf-8');
$page = new Page($userDbName);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php $page->printPageHead(""); ?>
<title>Index</title>
</head>
<body>
<?php $page->printPageOpen(); ?>
            <div class="frame">
              <div class="title">2010. június 20.</div>
              <div class="message">Végre minden tantárgyat letudtam erre a szemeszterre :)</div>
            </div>
            <div class="toCenter">
              A bejelentkezéshez cookie és JavaScript szükséges!
              <br />
              Minimum felbontás: 800x600
            </div>
<?php $page->printPageClose(); ?>
</body>
</html>