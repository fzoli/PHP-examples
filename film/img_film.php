<?php
include "class.php";
header("Content-type: image/png");
$film = new Film($filmDbName);
if (isset($_GET['azon'])&&isset($_GET['big'])) {
$link='files/filmek/borito/efdd1a1c62642b639916b1349827416c.jpg';
$kep = imagecreatefromjpeg($link);
if ($_GET['big']) imagepng($kep);
else {
$kep2 = imagecreatetruecolor(60, 90);
$meret=getimagesize($link);
$ideign=(($meret['0']-$meret['1'])/2);
imagecopyresampled($kep2, $kep, 0, 0, $ideign, 0, 60, 90, $meret['0']-(2*$ideign), $meret['1']);
imagepng($kep2);
}
}
?>