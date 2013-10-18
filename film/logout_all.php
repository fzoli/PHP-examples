<?php
include "class.php";
$session=new Session();
$session->initialize($userDbName);
session_start();
if(isset($_SESSION['oldal_cim'])) $cim=$_SESSION['oldal_cim'];
else $cim="index.php";
$session->logoutAll();
session_destroy();
header("location: ".$cim);
?>