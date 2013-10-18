<?php
include "class.php";
header("Content-Type: application/xml; charset=utf-8");
$film = new Film($filmDbName);
if (isset($_POST['getMaxOldal'])) $film->printXmlMaxoldal($_POST);
else if (isset($_POST['oldal'])) $film->printXmlFilmlista($_POST);
     else if (isset($_POST['getFilm'])) $film->printXmlFilm($_POST);
          else if (isset($_POST['szemely'])) $film->printXmlSzemely($_POST['szemely']);
          else $film->printXmlEmpty();
?>