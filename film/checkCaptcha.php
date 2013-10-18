<?php
$ok=false;
include "class.php";
$page = new Page($userDbName);
if ((bool)$_SESSION['azon']) { header('location: index.php'); exit(); }
$post=(!empty($_POST));
if(!isset($_SESSION['captchaOk'])) $_SESSION['captchaOk']=false;
if($_SESSION['bizt']==2) {
if ($post) {
  $_SESSION['captchaOk']=$page->checkCaptcha($page->getPost('captcha',''));
}
if ($_SESSION['captchaOk']) {
	header('Location: registration.php');
	unset($_SESSION['captcha']);
	exit();
}
$ok=$_SESSION['captchaOk'];
}
header ('Content-Type: text/html; charset=utf-8');
$page->createCaptcha();
$uzenet=$ok?'Ügyes :)':'Nem egyező kód!';
if($_SESSION['bizt']!=2) $uzenet='Cookie hiba!';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="lib/jquery.js"></script>
<script type="text/javascript" src="lib/tooltip/tooltip.js"></script>
<script type="text/javascript" src="lib/maskedinput.js"></script>
<script type="text/javascript" src="reg.js"></script>
<link href="reg.css" rel="stylesheet" type="text/css" />
<title>Regisztráció</title>
</head>
<body>
<table id="regExternal">
  <tr>
    <td>
      <form id="captchaForm" action="<?php  print $_SERVER['PHP_SELF']; ?>" method="post" >
      <table id="regInternal">
        <?php if ($post) if ($ok) { ?><tr>
          <td colspan="2" id="ok" class="toCenter">
            <?php print $uzenet; ?>
          </td>
        </tr>
        <?php } else { ?><tr>
          <td colspan="2" id="nemOk" class="toCenter">
            <?php print $uzenet; ?>
          </td>
        </tr><?php } print "\n"; ?>
        <tr>
          <td colspan="2" class="toCenter">
            Kérem, írja be a képen látható kódot!
          </td>
        </tr>
        <tr>
          <td colspan="2" class="toCenter">
            <img id="captchaImage" src="captcha.php" alt="Captcha" />
          </td>
        </tr>
        <tr>
          <td colspan="2" class="toCenter">
            <input type="text" class="input" id="captchaInput" name="captcha" />
          </td>
        </tr>
        <tr>
          <td colspan="2" class="toCenter">
            <input class="button" type="submit" id="submit" disabled="disabled" value="Ellenőrzés" />
          </td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
</table>
</body>
</html>