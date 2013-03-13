<?php
require_once('lotto_functions.php');
if (!isset($_GET['xml']))
	lotto_html_echo();
else
	lotto_xml_echo();
?>