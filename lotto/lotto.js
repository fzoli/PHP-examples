$(document).ready(function(){ //ha betöltött az oldal ...
	
	refreshTable(); // ... ez az eljárás azonnal lefut ...
	
	/*
	// Ferinek jó móka...
	setInterval(function() {
		refreshTable();
	}, 1);
	*/
	
	// ... és a függvények is oldalbetöltés után jönnek létre:

	function getError() {
		return "<tr><td colspan=\"2\">XML hiba!</td></tr>";
	}

	function getString() {
		var string = "";
		$.ajax({
			type: "GET", //TODO: csak hogy könnyű legyen bemutatni a működést, POST kellene (és a PHP-ban is át kellene írni a feltételt)
			url: "lotto.php",
			data: "xml=1",
			dataType: "xml",
			cache: false, //cache kikapcsolása (hogy minden kérésre friss adat jöjjön)
			async:false, //blokkolja a böngészőt, amíg nincs válasz
			error: function() { //ha nem tölthető be az XML, akkor hibaüzenet
				string = getError();
			},
			success: function(xml) {
				$(xml).find('huzas').each(function() { //string hozzáfűzés
					string +=
						"<tr>" +
						"<td class=\"index\">" +
						$(this).attr("index") +
						"</td>" +
						"<td class=\"szam\">" +
						$(this).attr("szam") +
						"</td>" +
						"</tr>";
  				});
			}
		});
		if (string == "") string = getError(); //ha nem találtunk egy elemet se, akkor hibás az xml - hibaüzenet
		return string;
	}

	function refreshTable() {
		var elso = true;
		var string = getString(); //xml-ből táblázatba illeszthető string készítése
		$("table#lotto tr").each(function() { //az első sor kivételével minden sor törlése
			if (elso)
				elso = false;
			else 
				$(this).remove();
		});
		$("table#lotto tr").parent().append(string); //az xml-ből létrehozott string hozzáadása a táblázathoz
		$("table#lotto tr").last().attr("class", "last-child"); //az utolsó sornak class="last-child" attribútum beállítás
	}
	
	$("#refresh").click(function(){
		refreshTable();
		return false;	//azért kell, hogy blokkolja a form elküldését.
						//Természetesen fölösleges lenne, ha a html-ben sima gomb lenne és nem submit :)
	});

});