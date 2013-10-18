(function() {

var Cookie = function() {

	this.get = function(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}

	this.set = function(name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	}

	this.del = function(name) {
		createCookie(name,"",-1);
	}

	this.isset = function(name) {
		return this.get(name)!=null;
	}

}

var FilmFeltetel = function() {
  this.change=false; this.cim=""; this.tipus=Number(-1); this.szinkron=Number(-1); this.mufaj=Number(-1);
  this.cookie=new Cookie();

	this.setCim = function(cim) {
		this.cookie.set("filmfelt_cim",cim);
		if (cim!=this.cim) this.change=true;
		this.cim=cim;
	}

	this.setTipus = function(tipus) {
		this.cookie.set("filmfelt_tipus",tipus);
		if (tipus!=this.tipus) this.change=true;
		this.tipus=tipus;
	}
	
	this.setSzinkron = function(szinkron) {
		this.cookie.set("filmfelt_szinkron",szinkron);
		if (szinkron!=this.szinkron) this.change=true;
		this.szinkron=szinkron;
	}
	
	this.setMufaj = function(mufaj) {
		this.cookie.set("filmfelt_mufaj",mufaj);
		if (mufaj!=this.mufaj) this.change=true;
		this.mufaj=mufaj;
	}

	this.getCim = function() {
		if (this.cookie.isset("filmfelt_cim")) this.cim=this.cookie.get("filmfelt_cim");
		else this.setCim(this.cim);
		return this.cim;
	}

	this.getTipus = function() {
		if (this.cookie.isset("filmfelt_tipus")) this.tipus=this.cookie.get("filmfelt_tipus");
		else this.setTipus(this.tipus);
		return this.tipus;
	}
	
	this.getSzinkron = function() {
		if (this.cookie.isset("filmfelt_szinkron")) this.szinkron=this.cookie.get("filmfelt_szinkron");
		else this.setSzinkron(this.szinkron);
		return this.szinkron;
	}
	
	this.getMufaj = function() {
		if (this.cookie.isset("filmfelt_mufaj")) this.mufaj=this.cookie.get("filmfelt_mufaj");
		else this.setMufaj(this.mufaj);
		return this.mufaj;
	}

	this.set = function() {
		this.setCim($("#film_cim").attr('value'));
		this.setTipus($("#tipus_felt").attr('value'));
		this.setSzinkron($("#szinkron_felt").attr('value'));
		this.setMufaj($("#mufaj_felt").attr('value'));
	}

	this.get = function() {
		$("#film_cim").attr('value',this.getCim());
		$("#tipus_felt").attr('value',this.getTipus());
		$("#szinkron_felt").attr('value',this.getSzinkron());
		$("#mufaj_felt").attr('value',this.getMufaj());
	}
	
	this.changed = function() {
		var change=this.change;
		this.change=false;
		return change;
	}

}

var FilmOldal = function() {
  this.change=false; this.elemChange=false; this.oldal = 1; this.elem=10; this.maxOldal=-1;
  this.filmFeltetel=null;
  this.cookie = new Cookie();

	this.setFilmFeltetel = function(ff) {
		this.filmFeltetel=ff;
	}

	this.setElem = function(elem) {
		if (Number(elem)>=1 && Number(elem)<=100) {
			this.cookie.set("film_aktelem",elem);
			if (elem!=this.elem) {this.change=true; this.elemChange=true;}
			this.elem=elem;
			return true;
		}
		else {
			$("#elemSet").attr('value',this.elem);
			return false;
		}
	}

	this.getElem = function() {
		if (this.cookie.isset("film_aktelem")) this.elem=this.cookie.get("film_aktelem");
		else this.setElem(this.elem);
		return this.elem;
	}

	this.getMaxOldal=function() {
		var maxOldal;
		if (this.maxOldal==-1) {
			$.ajax({
				type: "POST",
				url: "xml_filmlista.php",
				data: "getMaxOldal=1&elem="+this.getElem()+"&feltCim="+this.filmFeltetel.getCim()+
				      "&feltTipus="+this.filmFeltetel.getTipus()+"&feltSzinkron="+this.filmFeltetel.getSzinkron()+"&feltMufaj="+this.filmFeltetel.getMufaj(),
				dataType: "xml",
				cache: false,
				async:false,
				success: function(xml) {
					maxOldal=Number($(xml).find('maxoldal').attr('val'));
				}
			});
			this.maxOldal=maxOldal;
		}
		return this.maxOldal;
	}

	this.setOldal = function(oldal) {
		if (oldal>this.getMaxOldal()) this.setOldal(this.getMaxOldal());
		if (Number(oldal)>=1 && Number(oldal)<=Number(this.getMaxOldal())) {
			this.cookie.set("film_aktoldal",oldal);
			if (oldal!=this.oldal) this.change=true;
			this.oldal=oldal;
			$("#oldalSet").attr('value',this.oldal);
			return true;
		}
		else {
			$("#oldalSet").attr('value',this.oldal);
			return false;
		}
	}

	this.getOldal = function() {
		if (this.cookie.isset("film_aktoldal")) {
			this.oldal=this.cookie.get("film_aktoldal");
			var maxoldal=this.getMaxOldal();
			if (this.oldal>maxoldal) this.setOldal(maxoldal);
			if (this.oldal<0) this.setOldal(1);
		}
		else this.setOldal(this.oldal);
		return this.oldal;
	}

	this.show = function() {
		var elem='<table id="filmOldal">' +
                     '<tr>' +
					   '<td class="toLeft">Találatok száma oldalanként: <input type="text" id="elemSet" value="'+this.getElem()+'" /></td>' +
                       '<td id="film_oldalVissza"><img style="vertical-align:middle; height:25px;" src="files/filmek/back.png" alt="<" /></td>' +
					   '<td><input type="text" id="oldalSet" value="'+this.getOldal()+'" /></td>' +
					   '<td id="film_oldalElore"><img style="vertical-align:middle; height:25px;" src="files/filmek/next.png" alt=">" /></td>' +
                     '</tr>' +
                 '</table>';
		$("#oldalSelect").html(elem);
	}

	this.set = function() {
		return this.setOldal($("#oldalSet").attr('value')) && this.setElem($("#elemSet").attr('value'));
	}

	this.changed=function() {
		var change=this.change;
		this.change=false;
		return change;
	}
	
	this.elemChanged = function() {
		var change=this.elemChange;
		this.elemChange=false;
		return change;
	}

}

var FilmInfo = function() {
  this.azon=0;

	this.displayLemezInfo = function(div,id,xml) {
		$(div).find("#aktName").html('<table><tr><td>Lemez információ</td></tr></table>');
		elem="Folyt. köv.";
		$(div).find(".aktInfoDiv").html(elem);
		$(div).show();
	}

	this.displayStabInfo = function(div,id) {
		$(div).find("#aktName").html('<table><tr><td>Személy információ</td></tr></table>');
		var elem="";
		$.ajax({
			type: "POST",
			url: "xml_filmlista.php",
			data: "szemely="+id,
			dataType: "xml",
			cache: false,
			async:true,
			success: function(xml) {
				elem='<table style="width:100%;">' +
				       '<tr><td style="font-weight:800;">Név:</td><td>'+$(xml).find('nev').text()+'</td></tr>' +
					   '<tr><td style="font-weight:800;">Nem:</td><td>'+$(xml).find('nem').text()+'</td></tr>' +
					   '<tr><td style="font-weight:800;">Születési év:</td><td>'+$(xml).find('szul_datum').text()+'</td></tr>' +
					 '</table>';
				$(div).find(".aktInfoDiv").html(elem);
				$(div).show();
			}
		});
	}

	this.displayInfo = function() {
		var teg=$("tr #moreFilmInfo"+this.azon).children();
		var filmAzon=this.azon;
		var displayStabInfo=this.displayStabInfo;
		var displayLemezInfo=this.displayLemezInfo;
		if (teg.html()=='') {
			$.ajax({
				type: "POST",
				url: "xml_filmlista.php",
				data: "getFilm="+filmAzon,
				dataType: "xml",
				cache: false,
				async:false,
				success: function(xml) {
					var i=0;
					var mufaj=Array();
					$(xml).find('mufaj').each(function(){
						mufaj[i]=$(this).text();
						i++;
					});
					var mufajStr='';
					for (i=0;i<mufaj.length;i++) {
						mufajStr+=mufaj[i];
						if (i<mufaj.length-1) mufajStr+=", ";
					}
					var munka=Array();
					i=0;
					$(xml).find('stab').each(function(){
						munka[i]=$(this).find('munka').text();
						i++;
					});
					var stabMunka=Array();
					var j=0;
					var k=0;
					var egyezik=false;
					for (i=0;i<munka.length;i++) {
						egyezik=false;
						for (j=i+1;j<munka.length;j++) {
							if (munka[i]==munka[j]) egyezik=true;
						}
						if (!egyezik) {
							stabMunka[k]=munka[i];
							k++;
						}
					}
					stabMunka.sort();
					var db=0;
					munka="";
					for (i=0;i<stabMunka.length;i++) {
						munka+='<table><tr><td style="vertical-align:top;font-weight:800;">'+stabMunka[i]+':</td><td>';
						db=0;
						$(xml).find('stab').each(function(){
							if (stabMunka[i]==$(this).find('munka').text()) db++;
						});
						j=0;
						$(xml).find('stab').each(function(){
							if (stabMunka[i]==$(this).find('munka').text()) {
								j++;
								munka+='<span class="stab'+filmAzon+'" id="'+$(this).find('szemely').attr('azon')+'">'+$(this).find('szemely').text()+'</span>';
								if (j<db) munka+="<br />";
							}
						});
						munka+='</td></tr></table>';
					}
					db=0;
					$(xml).find('szerep').each(function(){
						if($(this).parent().parent().parent().text()=="") db++;
					});
					i=0;
					var szerep="";
					if (db>0) szerep+='<table><tr><td><span style="font-weight:800;">Szereplők:</span><br />';
					$(xml).find('szerep').each(function(){
						if($(this).parent().parent().parent().text()=="") {
							i++;
							szerep+='<span class="stab'+filmAzon+'" id="'+$(this).find('szemely').attr('azon')+'">'+$(this).find('szemely').text()+'</span> ' +
							        '('+$(this).find('szerep').text()+')';
							if(i<db) szerep+='<br />';
						}
					});
					if (db>0) szerep+='</td></tr></table>';
					db=0;
					$(xml).find('szinkron').each(function(){
						db++;
					});
					var szinkron="";
					if (db>0) szinkron+='<table><tr><td><span style="font-weight:800;">Szinkronhangok:</span><br />';
					i=0;
					$(xml).find('szinkron').each(function(){
						i++;
						szinkron+='<span class="stab'+filmAzon+'" id="'+$(this).parent().find('szinkron').attr('azon')+'">'+$(this).parent().find('szinkron').text()+'</span> ' +
						          '('+$(this).parent().find('szerep').text()+')';
						if(i<db) szinkron+="<br />";
					});
					if (db>0) szinkron+='</td></tr></table>';
					var hossz="";
					if ($(xml).find('hossz').text()!=0) hossz="<br />"+$(xml).find('hossz').text()+" perc";
					teg.css('text-align','left');
					db=0;
					i=0;
					var lemtip=Array();
					var peldanyDb=0;
					$(xml).find('peldany').each(function(){
						peldanyDb++;
					});
					$(xml).find('lemez').each(function(){
						db++;
						lemtip[i]=$(this).find('tipus').text();
						i++;
					});
					var lemeztipusok=Array();
					k=0;
					for (i=0;i<lemtip.length;i++) {
						egyezik=false;
						for (j=i+1;j<lemtip.length;j++) {
							if (lemtip[i]==lemtip[j]) egyezik=true;
						}
						if (!egyezik) {
							lemeztipusok[k]=lemtip[i];
							k++;
						}
					}
					lemeztipusok.sort();
					var lemezStr='<div style="margin-left:20px; margin-bottom:10px;">';
					if (db==0) lemezStr+="Ehhez a filmhez nem tartozik lemez.";
					else lemezStr+='Ez a film elérhető <span style="font-weight:800;">';
					for (i=0;i<lemeztipusok.length;i++) {
						lemezStr+=lemeztipusok[i];
						if (i<lemeztipusok.length-1) lemezStr+=', ';
					}
					if (db!=0) lemezStr+='</span> formátumban.<div style="margin-top:10px;">';
					if (peldanyDb>0) lemezStr+='<span style="font-weight:800;">Lemezek:</span> ';
					$(xml).find('peldany').each(function(){
						if (Number($(this).find('sorszam').text())<=1)
						lemezStr+='<span id="'+$(this).attr('azon')+'" class="lemez'+filmAzon+'">'+$(this).attr('azon')+'. </span>';
					});
					lemezStr+="</div></div>";
					var elem="";
					elem='<table class="aktFilmInfo">' +
					       '<tr>' +
						     '<td>' +
							   '<table style="border-spacing: 0px; margin:0px;">' +
						         '<tr>' +
							       '<td style="width:64px;">' +
							         '<img style="width:60px; vertical-align:middle;"' +
									  'src="files/filmek/korhatar/'+$(xml).find('korhatar').text()+'.png" />' +
							       '</td>' +
							       '<td style="font-weight:800;"><table><tr><td>'+$(xml).find('nemzetiseg').text()+' '+mufajStr +
									hossz+'</td></tr></table></td>' +
							     '</tr>' +
								 '<tr><td></td><td>'+munka+
								     szerep + szinkron +
								 '</td></tr>' +
						       '</table>' +
							 '</td>' +
							 '<td style="vertical-align:top;">' +
							   '<div id="infoDiv'+filmAzon+'" style="display:none; border:white 1px dotted;">' +
							     '<table style="width:100%; border-bottom:dotted 1px white;">' +
								   '<tr><td style="font-weight:800;"><span id="aktName"></span></td><td style="width:36px;">' +
								     '<img class="closeInfoDiv'+filmAzon+'" style="vertical-align:center;" src="files/icon_close.png" />' +
								   '</td></tr>' +
								 '</table>' +
								 '<table style="width:100%;"><tr><td>' +
							       '<div class="aktInfoDiv"></div>' +
								 '</td></tr></table>' +
							   '</div>' +
							 '</td>' +
						   '</tr>' +
						   '<tr>' +
						     '<td style="text-align:center;" colspan="2">' +
							   '<div style="margin-left:20px;margin-right:20px;margin-top:10px;margin-bottom:10px;text-align:justify;font-style:italic;">' +
							     $(xml).find('leiras').text() +
							   '</div>' +
							   lemezStr +
							 '</td>' +
						   '</tr>' +
						 '</table>';
					teg.html(elem);
					
					$('.lemez'+filmAzon).click(function() {
			    		displayLemezInfo("#infoDiv"+filmAzon,$(this).attr('id'),xml);
		    		});
					
				}
			});
			$('.closeInfoDiv'+filmAzon+', .stab'+filmAzon+', .lemez'+filmAzon).disableSelection();
			$('.closeInfoDiv'+filmAzon+', .stab'+filmAzon+', .lemez'+filmAzon).css('cursor','pointer');
			$('.stab'+filmAzon+', .lemez'+filmAzon).hover(
				function(){
					$(this).css('color','rgb(178,34,34)');
				},
				function(){
					$(this).css('color','black');
				}
			);

			$('.closeInfoDiv'+filmAzon).click(function() {
				$("#infoDiv"+filmAzon).css('display','none');
			});
			
			$('.stab'+filmAzon).click(function() {
			    displayStabInfo("#infoDiv"+filmAzon,$(this).attr('id'));
		    });

		}
		$("tr #moreFilmInfo"+this.azon).show();
	}

	this.hideInfo = function() {
		$("tr #moreFilmInfo"+this.azon).hide();
	}

	this.show = function(azon) {
		if (this.azon!=0) {
		  this.hideInfo();
		  if (this.azon!=azon) {
			  this.azon=azon;
			  this.displayInfo();
		  }
		  else this.azon=0;
		}
		else {
		  this.azon=azon;
		  this.displayInfo();
		}
	}

}

var FilmLista = function() {
  this.ff=null; this.fo=null; var fi=null; this.fr=null;

	this.setFilmFeltetel=function(ff) {
		this.ff=ff;
	}

	this.setFilmOldal=function(fo) {
		this.fo=fo;
	}

	this.setFilmRendez=function(fr) {
		this.fr=fr;
	}

	this.show=function() {
		scroll(0,0);
		$.ajax({
			type: "POST",
			url: "xml_filmlista.php",
			data: "oldal="+this.fo.getOldal()+"&elem="+this.fo.getElem()+"&rend="+this.fr.getRend()+"&feltCim="+this.ff.getCim()+
			      "&feltTipus="+this.ff.getTipus()+"&feltSzinkron="+this.ff.getSzinkron()+"&feltMufaj="+this.ff.getMufaj(),
			dataType: "xml",
			cache: false,
			async:false,
			success: function(xml) {
				var elem='';
				$(xml).find('film').each(function() {
					var aktfilm=$(this).attr('azon');
					var aktcim=$(this).find('cim').text();
					elem +=  '<tr>' +
							   '<td class="filmBorito">' +
								 '<a href="img_film.php?azon='+aktfilm+'&big=1" rel="lytebox[film'+aktfilm+']" ' +
								 'title="'+aktcim+' borító">' +
								   '<img src="img_film.php?azon='+aktfilm+'&big=0" class="filmBorito" />' +
								 '</a>';
								$(this).find('snapshot').each(function() {
									elem+='<a style="display:none;" rel="lytebox[film'+aktfilm+']" title="'+aktcim+' pillanatkép"' +
									      'href="img_snapshot.php?azon='+$(this).text()+'" />';
								});
					elem +=	   '</td>' +
							   '<td style="text-align:left;width:204px;" class="filmcim" id="'+aktfilm+'">'+aktcim+'</td>' +
							   '<td style="text-align:left;width:204px;">'+$(this).find('angol_cim').text()+'</td>' +
							   '<td style="width:120px;">'+$(this).find('gyart_ev').text()+'</td>' +
							   '<td style="width:125px;">'+$(this).find('letrehozva').text()+'</td>' +
							 '</tr>' +
							 '<tr id="moreFilmInfo'+aktfilm+'" style="display:none;"><td colspan="5"></td></tr>';
					$("#lista").html(elem);
				});
			}
		});

		$('td.filmcim').disableSelection();
		
		fi=new FilmInfo();

		$('td.filmcim').click(function() {
			fi.show($(this).attr('id'));
		});

		$('img.filmBorito').load(function(){
			$(this).fadeIn();
			initLytebox();
		});

	}

}

var FilmRendez = function() {
  this.rend="c_cim"; this.id="cim"; this.csokkeno=true;
  this.cookie=new Cookie();

	this.getRend=function() {	
		if (this.cookie.isset("film_aktrend")) {
			var rend=this.cookie.get("film_aktrend");
			var str=rend.substring(2);
			if ((rend[0]=='c' || rend[0]=='n')&&(rend[1]=='_')&&(str=='cim'||str=='angol_cim'||str=='gyart_ev'||str=='letrehozva')) this.rend=rend;
			else this.setRend(this.rend);
		}
		else this.setRend(this.rend);
		return this.rend;
	}

	this.setRend=function(rend) {
		this.cookie.set("film_aktrend",rend);
		this.rend=rend;
	}

	this.set=function(id) {
		if (id==this.id) this.csokkeno=(!this.csokkeno);
		else this.csokkeno=true;
		this.id=id;
		if (this.csokkeno) this.rend="c_"+this.id;
		else this.rend="n_"+this.id;
		this.setRend(this.rend);
	}

}

	$(document).ready(function() {
							   
		jQuery.fn.extend({ 
        	disableSelection : function() { 
					this.each(function() { 
                        	this.onselectstart = function() { return false; }; 
                    	    this.unselectable = "on"; 
                	        jQuery(this).css('-moz-user-select', 'none'); 
					}); 
        	} 
		});

		$('td.rend').disableSelection();

		var fi=new FilmInfo();
		
		var ff=new FilmFeltetel();
		ff.get();
		
		var fo = new FilmOldal();
		fo.setFilmFeltetel(ff);
		fo.show();
		fo.setOldal(fo.getOldal());

		var fr = new FilmRendez();

		var fl = new FilmLista();
		fl.setFilmFeltetel(ff);
		fl.setFilmOldal(fo);
		fl.setFilmRendez(fr);
		fl.show();

		$('#film_felt').submit(function(){
			ff.set();
			if (ff.changed()) {
				fo.maxOldal=-1;
				fo.setOldal(1);
				fo.setFilmFeltetel(ff);
				fl.setFilmFeltetel(ff);
				fl.show();
			}
			return false;
		});
		
		$('#elemSet,#oldalSet').bind('keydown',function(key){ //nesze IE
			if (key.keyCode==13) {
				if (fo.set()) {
					if (fo.changed()) {
						if (fo.elemChanged()) {fo.maxOldal=-1; fo.setOldal(1); }
						fl.setFilmOldal(fo);
						fl.show();
					}
				}
			}
		});

		$('#elemSet,#oldalSet').bind('change',function(){
			if (fo.set()) {
				if (fo.changed()) {
					if (fo.elemChanged()) {
						fo.maxOldal=-1;
						fo.setOldal(1);
					}
					fl.setFilmOldal(fo);
					fl.show();
				}
			}
		});

		$('#film_oldalVissza').bind('click',function(m){
			var ertek = Number($("#oldalSet").attr('value'));
			ertek--;
			$("#oldalSet").attr('value',ertek);
			if (fo.set()) {
				if (fo.elemChanged()) {
					fo.maxOldal=-1;
					fo.setOldal(1);
				}
				fl.setFilmOldal(fo);
				fl.show();
			}
		});

		$('#film_oldalElore').bind('click',function(m){
			var ertek = Number($("#oldalSet").attr('value'));
			ertek++;
			$("#oldalSet").attr('value',ertek);
			if (fo.set()) {
				if (fo.elemChanged()) {
					fo.maxOldal=-1;
					fo.setOldal(1);
				}
				fl.setFilmOldal(fo);
				fl.show();
			}
		});
		
		$('td.rend').bind('click',function(m){
			fo.setOldal(1);
			fr.set($(this).attr('id'));
			fl.setFilmRendez(fr);
			fl.setFilmOldal(fo);
			fl.show();
		});

	});

})();