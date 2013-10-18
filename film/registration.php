<?php
$default['info']="Nincs kitöltve.";
include "class.php";
header ('Content-Type: text/html; charset=utf-8');
$page = new Page($userDbName);
if ((bool)$_SESSION['azon']) { header('location: index.php'); exit(); }
if (isset($_SESSION['captchaOk'])) {
	if (!$_SESSION['captchaOk']) {
		header('Location: checkCaptcha.php');
		exit();
	}
}
else {
	header('Location: checkCaptcha.php');
	exit();
}
$post=(!empty($_POST));
$ok=true;
if($post) {
	$user=$page::$user;
	$ok=$user->regUser($_POST);
}
$uzenet=$ok?$ok:'Sikeres regisztráció!';
if (!(bool)$ok) {
	unset($_SESSION['captchaOk']);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="lib/hex_md5.js"></script>
<script type="text/javascript" src="lib/jquery.js"></script>
<script type="text/javascript" src="lib/tooltip/tooltip.js"></script>
<script type="text/javascript" src="lib/maskedinput.js"></script>
<script type="text/javascript" src="reg.js"></script>
<link href="reg.css" rel="stylesheet" type="text/css" />
<link href="lib/tooltip/tooltip.css" rel="stylesheet" />
<title>Regisztráció</title>
</head>
<body>
<script type="text/javascript">
/*<![CDATA[*/
var def_info="<?php print $default['info']; ?>";
/*]]>*/
</script>
<table id="regExternal">
  <tr>
    <td>
      <form id="regForm" action="<?php  print $_SERVER['PHP_SELF']; ?>" method="post" >
      <table id="regInternal">
        <?php if ($post) if (!(bool)$ok) { ?><tr>
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
          <td class="toRight">
            <span class="jtip" title=":A felhasználónév szabályai: - Nem kezdődhet számmal - 3-15 karakter hosszú lehet - Csak az angol ABC betűit és a számokat tartalmazhatja">
              Felhasználónév: 
            </span>
            <br />
            <span class="jtip" title=":A jelszó szabályai: - 6-15 karakter hosszú lehet - Csak az angol ABC betűit és a számokat tartalmazhatja">Jelszó kétszer: </span>
            <br />
            <span class="jtip" title=":A teljes név szabályai: - 3-40 karakter hosszú lehet - A magyar ABC betűit, pontot illetve kötőjelet tartalmazhat - Csak betűvel kezdődhet és kisbetűre végződhet">Teljes név: </span>
            <br />
            <span class="jtip" title=":Az e-mail cím szabályai: - A felhasználónév után a @ jel kötelező - A domainben a pont megléte kötelező - A domainben a pont után csak érvényes tld szerepelhet - Felhasználónévben és domainben megengedett karakterek: - angol ABC betűi, számok, pont, aláhúzás karakter, kötőjel">E-mail cím: </span>
            <br />
            <span class="jtip" title=":A születési dátum szabályai: - Év-Hó-Nap formátum - A dátum nem lehet nagyobb az aktuális napnál - Nem lehet a dátum 120 évnél korábbi">Születési dátum: </span>
          </td>
          <td class="toLeft">
            <input type="text" name="azon" class="input" id="azon" value="<?php $page->printPost('azon',''); ?>" />
            <br />
            <input type="password" class="input" id="jelszo" />
            <input type="password" class="input" id="jelszo2" />
            <input type="hidden" name="jelszo" id="jelszoH1" />
            <input type="hidden" name="jelszo2" id="jelszoH2" />
            <br />
            <input type="text" name="nev" class="input" id="nev" value="<?php $page->printPost('nev',''); ?>" />
            <select name="nem" id="nem">
              <option value="1" <?php $page->printSelected('nem','1'); ?>>Férfi</option>
              <option value="0" <?php $page->printSelected('nem','0'); ?>>Nő</option>
            </select>
            <br />
            <input type="text" name="email" class="input" id="email" value="<?php $page->printPost('email',''); ?>" />
            <img src="files/icon_<?php if($page->getPost('email_publikus','1')) print 'un'; ?>lock.png" alt="Láthatóság" title="Láthatóság" width="19" height="19" id="emailKey" />
            <input type="hidden" name="email_publikus" id="email_publikus" value="<?php $page->printPost('email_publikus','1'); ?>" />
            <br />
            <input type="text" name="sz_datum" class="input" id="sz_datum" value="<?php $page->printPost('sz_datum',''); ?>" />
          </td>
        </tr>
        <tr>
          <td class="toRight">
            <span>Bemutatkozás: </span>
          </td>
          <td>
            <textarea id="info" name="info" rows="0" cols="0"><?php $page->printPost('info',$default['info']); ?></textarea>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="toCenter">
            <input class="button" type="submit" id="submit" disabled="disabled" value="Regisztrálás" />
          </td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
</table>
</body>
</html>