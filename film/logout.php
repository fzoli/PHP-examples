<?php
include "class.php";
$session=new Session();
$session->initialize($userDbName);
session_start();
if(isset($_SESSION['oldal_cim'])) $cim=$_SESSION['oldal_cim'];
else $cim="index.php";
$_SESSION['azon']=false;
$_SESSION['hash']=md5(date("YmjHis",time()).rand());
unset($_SESSION['kulcs']);
unset($_SESSION['oldal_cim']);
unset($_SESSION['utolso_keres']);
$sess=$_SESSION;
session_destroy();
session_start();
$_SESSION=$sess;
session_write_close();
header("location: ".$cim);
?>