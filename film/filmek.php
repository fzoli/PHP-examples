<?php
include "class.php";
header ('Content-Type: text/html; charset=utf-8');
$page = new Page($userDbName);
$film = new Film($filmDbName);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php $page->printPageHead(""); ?>
<script type="text/javascript" src="files/filmek/script.js"></script>
<link href="files/menu/style.css" rel="stylesheet" type="text/css" />
<title>Filmek</title>
</head>
<body>
<?php $page->printPageOpen(); $page->printFilmEditMenu(0,0); $film->printFilmFeltetelMenu(); ?>
            <div id="divLista">Film lista</div>
            <table id="filmLista">
              <tr>
                <td style="width:64px;">Képek</td>
                <td class="rend" id="cim" style="width:204px;">Magyar cím</td>
                <td class="rend" id="angol_cim" style="width:204px;">Angol cím</td>
                <td class="rend" id="gyart_ev" style="width:120px;">Gyártás éve</td>
                <td class="rend" id="letrehozva" style="width:125px;">Létrehozva</td>
              </tr>
            </table>
            <table id="lista">
              <tr>
                <td style="width:740px">A lista megjelenítéséhez JavaScript szükséges!</td>
              </tr>
            </table>
            <span id="oldalSelect"></span>
<?php $page->printPageClose(); ?>
</body>
</html>