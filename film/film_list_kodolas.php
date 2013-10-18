<?php
include "class.php";
header ('Content-Type: text/html; charset=utf-8');
$page = new Page($userDbName);
$user=$page::$user;
if(!$user->isJog('elnev','root')) { header('location: filmek.php'); exit(); }
if (isset($_GET["q"])) {
	$q = strtolower($_GET["q"]);
	if (!$q) return;
	$film=new Film($filmDbName);
	$items = $film->getLista('kódolás');
	foreach ($items as $value=>$key) {
		if (strpos(strtolower($key), $q) !== false) {
			echo "$key|$value\n";
		}
	}
}
?>