<?php
include "class.php";
$session=new Session();
$session->initialize($userDbName);
session_start();
if(isset($_SESSION['oldal_cim'])) $cim=$_SESSION['oldal_cim'];
else $cim="index.php";
if (isset($_SESSION['azon']) && $_SESSION['azon']) {
	$user=new User($userDbName);
	$user::setProbalkozas($_SESSION['azon']);
}
header("location: ".$cim);
?>