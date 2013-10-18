<?php
include "class.php";
header("Content-type: image/png");
$film = new Film($filmDbName);
if (isset($_GET['azon'])) {
if ($_GET['azon']==1) $link='files/filmek/snapshot/6da7542795519827b7a4754d244ffca2.jpg';
else $link='files/filmek/snapshot/039619838ebe95c46d27e963c897c7cd.jpg';
$kep = imagecreatefromjpeg($link);
imagepng($kep);
}
?>