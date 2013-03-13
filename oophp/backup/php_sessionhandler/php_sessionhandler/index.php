<?php
include 'class.php';
$teszt = new Teszt();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="style.css" rel="stylesheet" type="text/css" />
<?php $teszt->echoJS(); ?>
<title>Teszt</title>
</head>
<body>
<?php $teszt->echoForm(); ?>
<pre>
<?php
$teszt->echoLogin();
$teszt->sessionCount();
echo 'Session ID csereideje: '.$teszt->session->getChangetime().' másodperc'.PHP_EOL;
echo 'Session élettartama: '.$teszt->session->getLifetime(0).' másodperc'.PHP_EOL;
echo 'Új session élettartama: '.$teszt->session->getLifetime(1).' másodperc'.PHP_EOL;
echo 'Aktuális session élettartama: '.$teszt->session->getSessLifetime().' másodperc'.PHP_EOL;
echo 'Aktuális session lejárata: '.$teszt->getSessEndtime().PHP_EOL;
echo 'Hátralévő idő: <span id="counter">-</span>'.PHP_EOL;
echo $teszt->loginError ? PHP_EOL.'Új munkamenetbe nem lehet bejelentkezni.'.PHP_EOL : ''; //most nincs hibakód, mert egyértelmű a hiba
echo $teszt->session->isExpired() ? PHP_EOL.'Új munkamenetet kapott, mert az előző lejárt.'.PHP_EOL.PHP_EOL : PHP_EOL;
echo 'Munkamenet-azonosító:'.PHP_EOL.session_id().PHP_EOL.PHP_EOL;
echo 'Látogatók száma: <span id="visitors">'.$teszt->session->getSessCount().'</span>'.PHP_EOL;
echo 'Vendégek száma: <span id="guests">'.$teszt->session->getGuestCount().'</span>'.PHP_EOL;
echo 'Bejelentkezett látogatók száma: <span id="loginedVisitors">'.$teszt->session->getLoginCount().'</span>'.PHP_EOL;
echo 'Bejelentkezett felhasználók száma: <span id="loginedUsers">'.$teszt->session->getDistinctLoginCount().'</span>'.PHP_EOL.PHP_EOL;
echo '<span id="refreshButtonTag"></span>';
echo '<span id="list">'.PHP_EOL;
$teszt->echoUsers();
echo '</span>'.PHP_EOL;
?>
</pre>
</body>
</html>
