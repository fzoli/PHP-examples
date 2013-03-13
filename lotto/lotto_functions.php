<?php
$gombok = array();

function gombLetrehoz() { //tömb feltöltése 1 - 90 számokkal
	global $gombok;
	$temp = array();
	for ($i=1; $i <= 90; ++$i)
		array_push($temp, $i);
	//echo "Íme a feltöltött tömb:\r"; print_r($temp); //debug
	$gombok = $temp;
}

function gombUjrarendez() { //tömb feltöltése a maradék kihúzható számokkal
	global $gombok;
	$temp = array();
	foreach ($gombok as $g)
		if ($g > 0)
			array_push($temp, $g);
	//echo "Íme az újragenerált tömb:\r"; print_r($temp); //debug
	$gombok = $temp;
}

function gombKivalaszt() { // Egy gömb kiválasztása a gomb tömbből
	global $gombok;
	$szam = 0;
	$db = count($gombok);
	if ($db > 0) {
		$gomb = mt_rand(0, $db - 1);
		//echo 'rand: '.$gomb."\r";
		$szam = $gombok[$gomb];
		$gombok[$gomb] = 0;
		//echo 'A kiválasztott szám: '.$szam."\r"; //debug
		gombUjrarendez();
	}
	return $szam;
}

function lotto() { //lottó-húzás, tömb generálás
	gombLetrehoz();
	$temp = array();
	for ($i = 0; $i < 5; ++$i)
		array_push($temp, gombKivalaszt());
	return $temp;
}

function lotto_table_echo() { //lottó-húzás megjelenítése táblában
	$szamok = lotto();
	echo '<table id="lotto">'."\r";
	echo "	<tr>\r	<td class=\"index\">Index</td>\r	<td class=\"szam\">Szám</td>\r	</tr>\r";
	$darab = count($szamok);
	for($i=0; $i<$darab; ++$i) {
		$last = ($i == $darab-1) ? 'class="last-child"' : '';
		echo "	<tr $last>\r";
		echo '	<td class="index">'.($i+1)."</td>\r";
		echo '	<td class="szam">'.$szamok[$i]."</td>\r";
		echo "	</tr>\r";
	}
	echo "	</table>\r";
}

function lotto_xml_echo() { //xml string előállítása a generált számokkal
	/*
	//mivel a routeremen nem teljes értékű a PHP, ez az egész bekommentelt rész kuka...
	//és természetesen procedurális nyelvben sem támogatott -.-
	header("Content-type: text/xml; charset=utf-8");
	$szamok = lotto();
	$doc = new DOMDocument('1.0', 'utf-8');
	$lotto = $doc->createElement('lotto');
	$lotto = $doc->appendChild($lotto);
	for($i = 0; $i < count($szamok); ++$i) {
		$huzas = $doc->createElement('huzas');
		$huzas->setAttribute('index', $i + 1);
		$huzas->setAttribute('szam', $szamok[$i]);
		$lotto->appendChild($huzas);
	}
	echo $doc->saveXML();
	*/
	//így már megy a lighttpd alatt is:
	header("Content-type: text/xml; charset=utf-8");
	$szamok = lotto();
	echo '<?xml version="1.0" encoding="utf-8"?>';
	echo '<lotto>';
	for($i = 0; $i < count($szamok); ++$i)
		echo '<huzas index="'.($i+1).'" szam="'.$szamok[$i].'"/>';
	echo '</lotto>';
}

function lotto_html_echo() { //html string előállítása a generált számokkal
	header('Content-Type: text/html; charset=utf-8'); ?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="lotto.css" rel="stylesheet" type="text/css" />
	<title>Lottó - PHP</title>
	</head>
	<body>
	<?php lotto_table_echo(); ?>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<p>
	<input id="refresh" type="submit" value="Új sorsolás" />
	</p>
	</form>
	</body>
	</html>
<?php } ?>