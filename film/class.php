<?php
error_reporting(E_ALL);
$userDbName="user";
$filmDbName="film";

class Sql {
  private $dbname, $conn;

	public function Sql($dbname) {
		$this->dbname=$dbname;
	}

	public function sqlConnect() {
		include 'sql_config.php';
		$this->conn=mysql_connect('localhost', $dbuser, $dbpass, true) or die('Sikertelen MySQL kapcsolódás!');
		mysql_query("SET NAMES utf8",$this->conn);
		mysql_select_db($this->dbname,$this->conn);
		return mysql_select_db($this->dbname,$this->conn);
	}

	public function sqlDisconnect() {
		$ok=mysql_close($this->conn);
		$this->conn=null;
		return $ok;
	}

	public function getConn() {
		return $this->conn;
	}

	public function getDbName() {
		return $this->dbname;
	}

}

class Session {
  private static $rand,$valid,$expire,$sql,$isCookieSet;

	public function initialize($dbname) {
		if (self::$sql!=null  && self::$sql->getConn()!=null) self::$sql->sqlDisconnect();
		self::$sql=new Sql($dbname);
		ini_set('session.name','fzoltan_sessid');
		ini_set('session.use_only_cookies','1');
		ini_set('session.use_trans_sid','0');
		//ini_set('session.referrer_check','"domain.tld"');
		ini_set('session.cookie_lifetime','0');
		ini_set('session.gc_maxlifetime','1200');
		self::$expire=ini_get('session.gc_maxlifetime');
		session_set_save_handler(	array(&$this,"open"),
       	                 			array(&$this,"close"),
       	                 			array(&$this,"read"),
       	                 			array(&$this,"write"),
       	                 			array(&$this,"destroy"),
       	                 			array(&$this,"gc")
								);
	}

	private static function getRandList() {		
		$sql='SELECT `rand` FROM `sessions`';
		$sql = mysql_query($sql,self::$sql->getConn());
		$list=array();
		$i=0;
		while($sor = mysql_fetch_assoc($sql)) {
			$list[$i]=$sor['rand'];
			++$i;
		}
		return $list;
	}

	private static function setNewSessId() {
		$list=self::getRandList();
		if (count($list)<=mt_getrandmax()) {
			$rand=self::rand_except($list);
			if ((bool)$rand) {
				$id=base64_encode(bin2hex(mhash(MHASH_SHA256,uniqid($rand,true))));
				$id=substr($id,0,strlen($id)-2);
				session_id($id);
				setcookie(session_name(),$id,ini_get('session.cookie_lifetime'),"/");
				self::$rand=$rand;
				return $id;
			}
		}
		die("Megtelt az oldal!");
	}

	private static function rand_except($except) {
		$min=0;
		$max=mt_getrandmax();
		sort($except, SORT_NUMERIC);
		$except_count = count($except);
		$avg_gap = ($max - $min + 1 - $except_count) / ($except_count + 1);
		if ($avg_gap <= 0) return false;
		array_unshift($except, $min - 1);
		array_push($except, $max + 1);
		$except_count += 2;
		for ($i = 1; $i < $except_count; $i++)
			if ($except[$i] - $except[$i - 1] - 1 >= $avg_gap) return mt_rand($except[$i - 1] + 1, $except[$i] - 1);
		return false;
	}

	private static function isSessSet($id) {
		$id=mysql_real_escape_string($id,self::$sql->getConn());
		$sql='SELECT count(*) as "db" FROM `sessions` WHERE `id`="'.$id.'"';
		$sql=mysql_query($sql,self::$sql->getConn());
		$data['db']=0;
		if ($sql) $data=mysql_fetch_assoc($sql);
		return $data['db']!=0;
	}

	public static function setAzon($azon) {
		$azon=mysql_real_escape_string($azon,self::$sql->getConn());
		$sql="UPDATE `sessions` SET `azon` = '".$azon."' WHERE `id` = '".session_id()."';";
		return mysql_query($sql,self::$sql->getConn());
	}

	public static function logoutAll() {
		$sql="SELECT `azon` FROM `sessions` WHERE `id`='".session_id()."'";
		$sql=mysql_query($sql,self::$sql->getConn());
		$data=mysql_fetch_assoc($sql);
		print $sql="DELETE FROM `sessions` WHERE `azon` = '".$data['azon']."'";
		return mysql_query($sql,self::$sql->getConn());
	}

	public static function isCookieSet() {
		return self::$isCookieSet;
	}

	public static function sessRegenId() {
		$oldId=session_id();
		$newId=self::setNewSessId();
		$sql="UPDATE `sessions` SET `id` = '".$newId."',`rand` = '".self::$rand."' WHERE `id` = '".$oldId."';";
		return mysql_query($sql,self::$sql->getConn());
	}

	public static function getLoginDb($azon) {
		$azon=mysql_real_escape_string($azon,self::$sql->getConn());
		$sql="SELECT count(*) AS 'db' FROM `sessions` WHERE `azon`='".$azon."'";
		$sql=mysql_query($sql,self::$sql->getConn());
		$data=mysql_fetch_assoc($sql);
		return $data['db'];
	}

	public static function getLoginedList() {
		$sql="SELECT `azon` FROM (SELECT DISTINCT `azon` FROM `sessions` AS `a` WHERE `azon` is not null) AS `b`";
		$sql=mysql_query($sql,self::$sql->getConn());
		$list=array();
		$i=0;
		while($sor = mysql_fetch_assoc($sql)) {
			$list[$i]=$sor['azon'];
			++$i;
		}
		return $list;
	}

	public static function getNotLoginedNum() {
		$sql="SELECT count(*) AS 'db' FROM `sessions` WHERE `azon` is null AND ".time()."-`expire`<60";
		$sql=mysql_query($sql,self::$sql->getConn());
		$data=mysql_fetch_assoc($sql);
		return $data['db'];
	}

	public static function open() {
		$ok=self::$sql->sqlConnect();
		self::$isCookieSet=(bool)isset($_COOKIE[session_name()]);
		if (!self::$isCookieSet) self::setNewSessId();
		self::$valid=!(self::$isCookieSet xor self::isSessSet(session_id()));
		if (self::$isCookieSet&&(!self::$valid)) {
			self::setNewSessId();
			self::$valid=true;
		}
		self::gc(self::$expire);
		return $ok;
	}

	public static function close() {
		return self::$sql->sqlDisconnect();
	}

	public static function read($id) {
		if (!self::$isCookieSet) return '';
		$data='';
		if (self::$valid) {
			$id=mysql_real_escape_string($id,self::$sql->getConn());
			$sql='SELECT `data` FROM `sessions` WHERE `id`= "'.$id.'"';
			$sql=mysql_query($sql,self::$sql->getConn());
			$sql=mysql_fetch_assoc($sql);
			if ((bool)$sql) $data=$sql['data'];
		}
		return $data;
	}

	public static function write($id,$data) {
		if (!self::$valid || !self::$isCookieSet) return false;
		$id=mysql_real_escape_string($id,self::$sql->getConn());
		$data=mysql_real_escape_string($data,self::$sql->getConn());
		if (self::isSessSet($id)) $sql=sprintf("UPDATE `sessions` SET `data` = '%s',`expire`='%s' WHERE `id`='%s';",$data,time(),$id);
		else $sql=sprintf("INSERT INTO `sessions` (`id`,`rand`,`data`,`expire`) VALUES ('%s','%s','%s','%s');",$id,self::$rand,$data,time());
		return mysql_query($sql,self::$sql->getConn());
	}

	public static function destroy($id) {
		if (!self::$isCookieSet) return false;
		$id=mysql_real_escape_string($id,self::$sql->getConn());
		$sql='DELETE FROM `sessions` WHERE `id` = "'.$id.'"';
		return mysql_query($sql,self::$sql->getConn());
	}

	public static function gc($expire) {
		$expire=mysql_real_escape_string($expire,self::$sql->getConn());
		$sql='DELETE FROM `sessions` WHERE `expire`+'.$expire.' < '.time().';';
		return mysql_query($sql,self::$sql->getConn());
	}

}

class Date {
  private $year,$month,$day;

	public function Date($year,$month,$day) {
		$this->year=(int)$year;
		$this->month=(int)$month;
		$this->day=(int)$day;
	}

	public function check() {
		return $this->checkDate()&&$this->checkMin()&&$this->checkMax();
	}

	private function checkDate() {
		return checkdate($this->month,$this->day,$this->year);
	}

	private function checkMax() {
		$date=strtotime($this->year.'-'.$this->month.'-'.$this->day);
		$today=strtotime(date("Y-n-d",time()));
		return $date<=$today;
	}

	private function checkMin() {
		$min=(int)date("Y",time())-120;
		return $this->year>=$min;
	}

}

class Page {
  private static $sql, $sess;
  public static $user;

	public function __construct($userDbName) {
		self::$user=new User($userDbName);
		self::startSession($userDbName);
		if((bool)$_SESSION['azon']) {
			if(self::$user->check()) {
				$loginDb=self::$sess->getLoginDb($_SESSION['azon']);
				if ($loginDb>1) $_SESSION['warning']=$loginDb." helyen is be van jelentkezve!";
				$_SESSION['oldal_cim']=$_SERVER['SCRIPT_NAME'];
			}
			else {
				$_SESSION['azon']=false;
				self::$sess->setAzon('');
				$_SESSION['hash']=md5(date("YmjHis",time()).rand());
			}
			$_SESSION['utolso_keres']=self::$user->setUtolsoKeres();
		}
	}

	public function __destruct() {
		$_SESSION['warning']=false;
		session_write_close();
	}

	public static function getSqlConn() {
		return self::$sql->getConn();
	}

	private static function startSession($dbname) {
		self::$sess=new Session();
		self::$sess->initialize($dbname);
		session_start();
		if(!(bool)sizeof($_SESSION)) {
			$_SESSION['bizt']=0;
			$_SESSION['azon']=false;
			$_SESSION['warning']=false;
			$_SESSION['hash']=md5(date("YmjHis",time()).rand());
		}
		if($_SESSION['bizt']<2) ++$_SESSION['bizt'];
		if ($_SESSION['azon']) {
			$utolso_keres=strtotime(self::$user->getUtolsoKeres($_SESSION['azon']));
			$most=time();
			$eltelt=$most-$utolso_keres;
			if ($eltelt>=10) self::$sess->sessRegenId();
		}
	}

	public static function printPost($mi,$def) {
		if (isset($_POST[$mi])) print htmlspecialchars($_POST[$mi]);
		else print htmlspecialchars($def);
	}

	public static function getPost($mi,$def) {
		if (isset($_POST[$mi])) return htmlspecialchars($_POST[$mi]);
		else return htmlspecialchars($def);
	}

	public static function printSelected($name,$value) {
		if (isset($_POST[$name]))
		  if ($_POST[$name]==$value) print 'selected="selected"';
	}

	public static function createCaptcha() {
		$_SESSION['captcha']=substr(md5(date("YmjHis",time()).rand()),0,5);
	}
	
	public static function checkCaptcha($captcha) {
		return $captcha==strtolower($_SESSION['captcha']);
	}

	public static function printFelhasznalo($jogElnev) {
		$felhasznalo=self::$user->getUserList($jogElnev);
		$i=0;
		foreach($felhasznalo as $azon) {
			++$i;
			print $azon;
			if ($i!=count($felhasznalo)) print ', ';
		}
	}

	public static function printPageHead($hely) {
		print '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="'.$hely.'lib/lytebox/lytebox.js"></script>
<script type="text/javascript" src="'.$hely.'lib/hex_md5.js"></script>
<script type="text/javascript" src="'.$hely.'lib/jquery.js"></script>
<script type="text/javascript" src="'.$hely.'script.js"></script>
<link rel="stylesheet" href="'.$hely.'lib/lytebox/lytebox.css" type="text/css" media="screen" />
<link href="'.$hely.'style.css" rel="stylesheet" type="text/css" />'."\n";
	}

	public static function printFilmEditMenu($akt1,$akt2) {
		if (!self::$user->isJog('elnev','root')) return false;
		--$akt1;
		$osztaly="inaktiv";
		$text=array(
			array("Hozzáadás","Személy","Lista","Film","Képek","Hangok","Feliratok","Stáb","Szerep","Lemez","Műfaj","Példány","Kép","Hang","Felirat","Extra","Snapshot"),
			array("Módosítás","Személy","Lista","Film","Képek","Hangok","Feliratok","Stáb","Szerep","Lemez","Műfaj","Példány","Kép","Hang","Felirat","Extra","Snapshot"),
			array("Törlés","Személy","Lista","Film","Képek","Hangok","Feliratok","Stáb","Szerep","Lemez","Műfaj","Példány","Kép","Hang","Felirat","Extra","Snapshot")
		);
		$link=array(
			array("#","film_szemely_add.php","film_lista_add.php","film_film_add.php","film_kepek_add.php","film_hangok_add.php","film_feliratok_add.php","film_stab_add.php","film_szerep_add.php","film_lemez_add.php","film_mufaj_add.php","film_peldany_add.php","film_kep_add.php","film_hang_add.php","film_felirat_add.php","film_extra_add.php","film_snapshot_add.php"),
			array("#","film_szemely_set.php","#","#","#","#","#","#","#","#","#","#","#","#","#","#","#"),
			array("#","film_szemely_del.php","#","#","#","#","#","#","#","#","#","#","#","#","#","#","#")
		);
		print "<div id=\"menu\">\n<ul>\n";
		for($i=0;$i<count($text);++$i) {
			if ($akt1==$i) $osztaly="aktiv";
			print "<li>\n<a class=\"".$osztaly."\" href=\"".$link[$i][0]."\">".$text[$i][0]."</a>\n";
			$osztaly="inaktiv";
			if (count($text[$i])>1) {
				print "<ul>\n";
				for($j=1;$j<count($text[$i]);++$j) {
					if (($akt1==$i) && ($akt2==$j)) $osztaly="aktiv";
					print "<li>\n"."<a class=\"".$osztaly."\" href=\"".$link[$i][$j]."\">".$text[$i][$j]."</a>\n"."</li>\n";
					$osztaly="inaktiv";
      			}
      			print "</ul>\n";
    		}
    		print "</li>\n";
		}
		print "</ul>\n</div>\n<br />\n";
	}

	public static function printPageOpen() {
		$isCookieSet=self::$sess->isCookieSet();
		if ((!(bool)$_SESSION['azon']) && isset($_POST['azon'],$_POST['jelszo'])) {
		  $_POST['azon']=htmlspecialchars($_POST['azon']);
		  $_POST['jelszo']=htmlspecialchars($_POST['jelszo']);
		  $ok=self::$user->login($_POST['azon'],$_POST['jelszo']);
		}
		extract($_SESSION);
		if((bool)$azon) {
			extract(self::$user->getUserData());
			extract(self::$user->getUserJog());
		}
		else print
'<script type="text/javascript">
/*<![CDATA[*/
var fuszer="'.$hash.'";
/*]]>*/
</script>'; print'
<table id="external">
  <tr>
    <td rowspan="3" id="left"></td>
    <td id="top"></td>
    <td rowspan="3" id="right"></td>
  </tr>
  <tr>
    <td id="middle">
      <table id="internal">
        <tr>
          <td id="logo">Farkas Zoltán weboldala</td>
        </tr>
        <tr>
          <td class="menu">
            <table class="menu">
              <tr>
                <td><a href="index.php">Index</a></td>
                <td><a href="status.php">Státusz</a></td>
                <td><a href="filmek.php">Filmek</a></td>
              </tr>
            </table>
          </td>
        </tr>
        '; if ((bool)$warning) print '<tr>
          <td id="warning">'.$warning.'</td>
        </tr>
        '; print '<tr>
          <td class="user">
            <form id="login" action="'.$_SERVER['SCRIPT_NAME'].'" method="post">'; if ((bool)$azon) print '<table class="user">';
			   else print '<table class="userout">'; print '
              <tr>
                ';
				if ((bool)$azon) print '<td class="toLeft">Üdvözöllek: '.$nev.' ('.$elnev.')';
				else print '<td class="toLeft">Üdvözöllek!';
				print'</td>'."\n                ";
				if(!(bool)$azon) {
				  if($isCookieSet) {
				    print '<td class="toLeft"><input type="text" value="Felhasználónév" name="azon" id="azon" class="input" /></td>'."\n                ";
				    print '<td class="toRight"><input type="hidden" name="jelszo" id="hash" /><input type="password" value="Jelszó" id="jelszo" class="input" /></td>';
				  }
				  else {
				    print '<td class="toCenter"><input id="showLoginButton" class="button" type="button" value="Bejelentkezés" /></td>';
				  }
				}
				print'
                <td class="toRight">';
				  if((bool)$azon) {
				    if ($change) print '<a href="admin_menu.php"><img src="files/icon_admin.png" width="30px" height="30px" title="Adminisztrálás" alt="Adminisztrálás" /></a>';
				  	print '<a href="user_settings.php"><img src="files/icon_set.png" width="30px" height="30px" title="Beállítások" alt="Beállítások" /></a>';
					print '<a href="logout.php"><img src="files/icon_logout.png" width="30px" height="30px" title="Kijelentkezés" alt="Kijelentkezés" /></a>';
				  }
				  else {
				  	print '<a href="registration.php" id="reg" rel="lyteframe"><img src="files/icon_reg.png" width="30px" height="30px" title="Regisztrálás" alt="Regisztrálás" /></a>';
					if($isCookieSet) print '<input type="image" src="files/icon_login.png" id="submit" disabled="disabled" title="Bejelentkezés" alt="Bejelentkezés" />';
				  }
				print '</td>'."\n              "; print'</tr>
            </table></form>
          </td>
        </tr>
        <tr>
          <td id="page">'."\n";
	}

	public static function printPageClose() {
		print
'          </td>
        </tr>
        <tr>
          <td class="info">
            <table class="info">
              <tr>
                <td class="toCenter">© 2010 - '.date('Y',time()).'</td>
                <td class="toCenter">
                  <!--[if !IE]> -->
                    <a href="http://validator.w3.org/check?uri=referer" onclick="window.open(this.href);return false">
                      <img src="files/valid-xhtml11.png" alt="Valid XHTML 1.1" />
                    </a>
                    &nbsp;
                    <a href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3" onclick="window.open(this.href);return false">
                      <img src="files/valid-css3.png" alt="Valid CSS 3" />
                    </a>
                  <!-- <![endif]-->
                  <!--[if IE]>
                    <a href="http://validator.w3.org/check?uri=referer">
                      <img src="files/valid-xhtml11.png" alt="Valid XHTML 1.1" />
                    </a>
                    &nbsp;
                    <a href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3">
                      <img src="files/valid-css3.png" alt="Valid CSS 3"/>
                    </a>
                  <![endif]-->
                </td>
                <td class="toCenter">Készítette: Farkas Zoltán</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td id="bottom"></td>
  </tr>
</table>'."\n";
	}

}

class User {
  private static $sql, $sess;

	public function __construct($dbname) {
		self::$sql=new Sql($dbname);
		self::$sql->sqlConnect();
		self::$sess=new Session();
	}

	public function __destruct() {
		self::$sql->sqlDisconnect();
	}

	public static function login($azon,$jelszo) {
		if ((strtotime(date("Y-m-d",strtotime(self::getUtolsoKeres($azon))))) < (strtotime(date("Y-m-d",time())))) self::setProbalkozas($azon);
		$probalkozas=self::getProbalkozas($azon);
		if (is_string($probalkozas) && (int)$probalkozas<=0) {
			$_SESSION['warning']="Nincs több belépési lehetősége mára!";
			return false;
		}
		$ok=(md5(self::getUserJelszo($azon).$_SESSION['hash'])==$jelszo);
		if ($ok) {
			unset($_SESSION['captcha']); unset($_SESSION['captchaOk']);
			$_SESSION['azon']=mysql_real_escape_string($azon,self::$sql->getConn());
			self::$sess->setAzon($_SESSION['azon']);
			$_SESSION['utolso_keres']=self::setUtolsoKeres();
			$_SESSION['kulcs']=md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$_SERVER['HTTP_HOST']);
			if(self::check()) {
			  unset($_SESSION['hash']);
			}
			else {$_SESSION['azon']=false;self::$sess->setAzon('');}
		}
		else if ($_SESSION['bizt']==2) {
				$_SESSION['warning']="Nem létező felhasználónév vagy hibás jelszó!";
				if (self::getProbalkozas($azon)>0) self::decProbalkozas($azon);
			 }
			 else {
			 	$_SESSION['warning']="Elévült munkamenet!";
			 }
		if ($ok) {
			$_SESSION['oldal_cim']=$_SERVER['SCRIPT_NAME'];
			self::$sess->sessRegenId();
		}
		return $ok;
	}

	public static function check() {
		$data=self::getUserData();
		extract($data);
		$return=true;
		$ok=((strtotime(date("Y-m-d H:i:s",time()))) <= (strtotime($utolso_keres)+600));
		if (!$ok) $_SESSION['warning']="10 perc tétlenség miatt ki lett jelentkeztetve!";
		$return=($return && $ok);
		$ok=(((int)$probalkozas)>0);
		if (!$ok) $_SESSION['warning']="Nincs több belépési lehetősége mára!";
		$ok=((strtotime(date("Y-m-d H:i:s",time()))) >= (strtotime($bann_lejar)));
		$maradt=(strtotime($bann_lejar)-strtotime(date("Y-m-d H:i:s",time())));
		if ($maradt>31536000) $maradt="Több mint 1 év";
		if ((!$ok)&&($maradt<=31536000)) $_SESSION['warning']="Bannolt felhasználó! ".$maradt." mp maradt.";
		if ((!$ok)&&($maradt=="Több mint 1 év")) $_SESSION['warning']="Bannolt felhasználó! ".$maradt." maradt.";
		$return=($return && $ok);
		$ok=($tiltott=="0");
		if (!$ok) $_SESSION['warning']="Bannolt felhasználó! Végleg...";
		$return=($return && $ok);
		$ok=($_SESSION['kulcs'] == md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$_SERVER['HTTP_HOST']));
		if (!$ok) $_SESSION['warning']="Nem szép dolog feltörni más munkamenetét.";
		$return=($return && $ok);
		return $return;
	}

	public static function getUserData() {
		$sql='SELECT * FROM `user` WHERE `azon`="'.$_SESSION['azon'].'"';
		$sql=mysql_query($sql,self::$sql->getConn());
		$data=mysql_fetch_assoc($sql);
		unset($data['jelszo']);
		return $data;
	}

	private static function decProbalkozas($azon) {
		$azon=mysql_real_escape_string($azon,self::$sql->getConn());
		$sql='UPDATE `user` SET `probalkozas` = `probalkozas`-1 WHERE `user`.`azon` = "'.$azon.'"';
		return mysql_query($sql,self::$sql->getConn());
	}

	public static function setUtolsoKeres() {
		$time=date("Y-m-d H:i:s",time());
		$sql='UPDATE `user` SET `utolso_keres` = "'.$time.'" WHERE `user`.`azon` = "'.$_SESSION['azon'].'"';
		$ok=mysql_query($sql,self::$sql->getConn());
		if(!$ok) return false;
		return $time;
	}

	private static function getUserJelszo($azon) {
		$azon=mysql_real_escape_string($azon,self::$sql->getConn());
		$sql='SELECT `jelszo` FROM `user` WHERE `azon`="'.$azon.'"';
		$sql=mysql_query($sql,self::$sql->getConn());
		$data=mysql_fetch_assoc($sql);
		return $data['jelszo'];
	}

	public static function setProbalkozas($azon) {
		$azon=mysql_real_escape_string($azon,self::$sql->getConn());
		$sql='UPDATE `user` SET `probalkozas` = 10 WHERE `user`.`azon` = "'.$azon.'"';
		return mysql_query($sql,self::$sql->getConn());
	}

	public static function getUtolsoKeres($azon) {
		$azon=mysql_real_escape_string($azon,self::$sql->getConn());
		$sql='SELECT `utolso_keres` FROM `user` WHERE `azon`="'.$azon.'"';
		$sql=mysql_query($sql,self::$sql->getConn());
		$data=mysql_fetch_assoc($sql);
		return $data['utolso_keres'];
	}

	private static function getProbalkozas($azon) {
		$azon=mysql_real_escape_string($azon,self::$sql->getConn());
		$sql='SELECT `probalkozas` FROM `user` WHERE `azon`="'.$azon.'"';
		$sql=mysql_query($sql,self::$sql->getConn());
		$data=mysql_fetch_assoc($sql);
		if ($data) return $data['probalkozas'];
		return false;
	}

	public static function getUserJog() {
		$sql='SELECT * FROM jog WHERE azon = ( SELECT jog FROM user WHERE azon = "'.$_SESSION['azon'].'" )';
		$sql=mysql_query($sql,self::$sql->getConn());
		$data=mysql_fetch_assoc($sql);
		unset($data['azon']);
		return $data;
	}

	public static function regUser($post) {
		if (self::regCheck($post)) {
			foreach($post as $index => $ertek) {
				$post[$index]=mysql_real_escape_string($ertek,self::$sql->getConn());
			}
			$post['nem']=(bool)$post['nem']?'1':'0';
			$post['email_publikus']=(bool)$post['email_publikus']?'1':'0';
			$sql=sprintf("INSERT INTO `user` (`azon`,`jelszo`,`nev`,`sz_datum`,`nem`,`email_cim`,`email_publikus`,`info`) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s');",$post['azon'],$post['jelszo'],$post['nev'],$post['sz_datum'],$post['nem'],$post['email'],$post['email_publikus'],$post['info']);
			return (bool)mysql_query($sql,self::$sql->getConn())?false:'Létező felhasználó!';
		}
		else return "Hibásan kitöltött űrlap!";
	}
	
	public static function isJog($jog,$ertek) {
		if ((bool)$_SESSION['azon']) {
			extract(self::getUserJog());
			if (isset(${$jog})) return ${$jog}==$ertek;
			else return !(bool)$ertek;
		}
		else return !(bool)$ertek;
	}

	public static function getUserList($jogElnev) {
		if (is_int($jogElnev)) {
			if ((int)$jogElnev==(int)0) {
				return self::$sess->getNotLoginedNum();
			}
			if ((int)$jogElnev==(int)1) {
				return count(self::$sess->getLoginedList())+self::$sess->getNotLoginedNum();
			}
		}
		$list=self::$sess->getLoginedList();
		$return=array();
		$i=0;
		foreach ($list as $index => $ertek) {
			$sql='SELECT `elnev` FROM jog WHERE azon = ( SELECT jog FROM user WHERE azon = "'.$ertek.'" )';
			$sql=mysql_query($sql,self::$sql->getConn());
			$sql=mysql_fetch_assoc($sql);
			$data=$sql['elnev'];
			if ($data==$jogElnev) $return[$i]=$ertek;
			++$i;
		}
		return $return;
	}

	private static function regCheck($post) {
		return self::checkAzon($post['azon'])&&self::checkRegJelszo($post)&&self::checkNev($post['nev'])&&self::checkEmail($post['email'])&&self::checkSzDatum($post['sz_datum']);
	}
	
	private static function checkAzon($str) {
		return (bool)preg_match('/^[A-Za-z]{1,1}[A-Za-z0-9]{2,14}$/',$str);
	}
	
	private static function checkJelszo($str) {
		return (bool)preg_match('/^[a-z0-9]{32,32}$/',$str);
	}
	
	private static function checkRegJelszo($post) {
		return (self::checkJelszo($post['jelszo']) && $post['jelszo']==$post['jelszo2']);
	}
	
	private static function checkNev($str) {
		return (bool)mb_ereg_match('^[A-ZÁ-Űa-zá-ű]{1,1}[A-ZÁ-Űa-zá-ű\.\ \-]{1,38}[a-zá-ű]{1,1}$',$str);
	}
	
	private static function checkEmail($str) {
		return (bool)preg_match('/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:biz|cat|com|edu|gov|int|mil|net|org|pro|tel|aero|arpa|asia|coop|info|jobs|mobi|name|museum|travel|hrvatska|a[cdefgilmnoqrstuwxz]|b[abdefghijmnorstvwyz]|c[acdfghiklmnoruvxyz]|d[ejkmoz]|e[ceghrstu]|f[ijkmorx]|g[abdefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnprwyz]|l[abcfikrstuvy]|m[acdeghklmnopqrstuvwxyz]|n[acefgilopruz]|o[m]|p[aefghklmnprstwy]|q[a]|r[eosuw]|s[abcdeghijklmnortuvyz]|t[cdfghjklmnoprtvwz]|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amrw])\b/i', $str);
	}
	
	private static function checkSzDatum($str) {
		$ok=(bool)preg_match('/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}$/',$str);
		if ($ok) {
			$ev=substr($str,0,4);
			$ho=substr($str,5,2);
			$nap=substr($str,8,2);
			$datum=new Date($ev,$ho,$nap);
			$ok=$datum->check();
		}
		return $ok;
	}
	
}

class Eletkor {

	private static function szokoev($ev) {
		if ((($ev%4==0) && ($ev%100!=0)) || (($ev%100==0) && ($ev%400==0))) {return true;}
		else {return false;}
	}

	public static function getEletkor($datum) {
	   if (!(bool)preg_match('/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}$/',$datum)) return false;
	   $kezdEv=substr($datum,0,4);
	   $kezdHo=substr($datum,5,2);
	   $kezdNap=substr($datum,8,2);
	   $vegEv=date("Y",time());
	   $vegHo=date("n",time());
	   $vegNap=date("j",time());
	   $honapHossz=array(31,31,28,31,30,31,30,31,31,30,31,30,31);
	   $ev=-1;
	   $ho=0;
	   $nap=0;
	   if ($kezdEv<=$vegEv) {
	     if (($kezdHo<=$vegHo) && ($kezdNap<=$vegNap)) {
	      $ev=$vegEv-$kezdEv;
	      $ho=$vegHo-$kezdHo;
	      $nap=$vegNap-$kezdNap;
	     }
	     if (($kezdHo<$vegHo) && ($kezdNap>$vegNap)) {
	       $ev=$vegEv-$kezdEv;
	       $ho=$vegHo-$kezdHo-1;
	       if (self::szokoev($ev+$kezdEv)) $honapHossz[2]++;
	       $nap=$honapHossz[$kezdHo+$ho]-$kezdNap+$vegNap;
	     }
	     if (($kezdHo>$vegHo) && ($kezdNap<=$vegNap) && ($kezdEv!=$vegEv)) {
	      $ev=$vegEv-$kezdEv-1;
	      $ho=12+$vegHo-$kezdHo;
	      $nap=$vegNap-$kezdNap;
	     }
 	    if (($kezdHo>=$vegHo) && ($kezdNap>$vegNap) && ($kezdEv!=$vegEv)) {
	       $ev=$vegEv-$kezdEv-1;
	       $ho=12+$vegHo-$kezdHo-1;
	       if (self::szokoev($ev+$kezdEv+1)) $honapHossz[2]++;
	       $nap=($honapHossz[($kezdHo-(12-$ho))]-$kezdNap)+$vegNap;
	     }
	   }
	   $return['year']=$ev;
	   $return['month']=$ho;
	   $return['day']=$nap;
	   return $return;
	}

}

class Film {
  private $sql;

	public function __construct($dbname) {
		$this->sql=new Sql($dbname);
		$this->sql->sqlConnect();
	}

	public function __destruct() {
		$this->sql->sqlDisconnect();
	}

	public function printXmlSzemely($azon) {
	//id alapján egy adott személyről minden infó
		$sql='SELECT * FROM `szemely` WHERE `azon`="'.$azon.'"';
		$sql=mysql_query($sql,$this->sql->getConn());
		$sql=mysql_fetch_assoc($sql);
		$nem=$sql['nem']?'férfi':'nő';
		$szd=($sql['szul_datum']!=0)?$sql['szul_datum']:'-';
		print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
  <szemely>
    <nev><![CDATA[".$sql['nev']."]]></nev>
	<nem>".$nem."</nem>
	<szul_datum><![CDATA[".$szd."]]></szul_datum>
  </szemely>";
	}

	public function printXmlFilm($data) {
	//egy adott filmről minden adat megjelenítése
		print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
  <film>
    <nemzetiseg><![CDATA[amerikai]]></nemzetiseg>
    <hossz><![CDATA[166]]></hossz>
    <korhatar><![CDATA[16]]></korhatar>
    <leiras><![CDATA[Egy deréktól lefelé megbénult háborús veterán a távoli Pandorára utazik. A bolygó lakói, a Na'vik az emberhez hasonló faj - de nyelvük és kultúrájuk felfoghatatlanul különbözik a miénktől. Ebben a gyönyörű és halálos veszélyeket rejtő világban a földieknek nagyon nagy árat kell fizetniük a túlélésért.
De nagyon nagy lehetőséghez is jutnak: a régi agyuk megőrzésével új testet ölthetnek, és az új testben, egy idegen lény szemével figyelhetik magukat és a körülöttük lévő felfoghatatlan, vad világot.
A veterán azonban más céllal érkezett. Az új test új, titkos feladatot is jelent számára, amit mindenáron végre kell hajtania.]]></leiras>
	<mufaj>sci-fi</mufaj>
	<ertekeles>4</ertekeles>
    <stab>
	  <szemely azon=\"1\"><![CDATA[James Cameron]]></szemely>
	  <munka><![CDATA[Rendező]]></munka>
	</stab>
	<stab>
	  <szemely azon=\"1\"><![CDATA[James Cameron]]></szemely>
	  <munka><![CDATA[Forgatókönyvíró]]></munka>
	</stab>
	<stab>
	  <szemely azon=\"3\"><![CDATA[Simon Franglen]]></szemely>
	  <munka><![CDATA[Zene]]></munka>
	</stab>
	<stab>
	  <szemely azon=\"2\"><![CDATA[Mauro Fiore]]></szemely>
	  <munka><![CDATA[Operatőr]]></munka>
	</stab>
	<stab>
	  <szemely azon=\"4\"><![CDATA[James Horner]]></szemely>
	  <munka><![CDATA[Zene]]></munka>
	</stab>
	<szerep>
	  <szemely azon=\"10\"><![CDATA[Sam Worthington]]></szemely>
	  <szerep><![CDATA[Jake Sully]]></szerep>
	  <szinkron azon=\"19\"><![CDATA[Széles Tamás]]></szinkron>
	</szerep>
	<szerep>
	  <szemely azon=\"11\"><![CDATA[Sigourney Weaver]]></szemely>
	  <szerep><![CDATA[Dr. Grace Augustine]]></szerep>
	  <szinkron azon=\"20\"><![CDATA[Menszátor Magdolna]]></szinkron>
	</szerep>
	<lemez>
	  <tipus><![CDATA[DVD]]></tipus>
	  <menu><![CDATA[1]]></menu>
	  <lemezek_szama><![CDATA[1]]></lemezek_szama>
	  <film_beszerzes><![CDATA[rippelt]]></film_beszerzes>
	  <bovitett><![CDATA[0]]></bovitett>
	  <kep>
	    <keparany><![CDATA[4:3]]></keparany>
		<felbontas><![CDATA[720 x 576]]></felbontas>
		<szines><![CDATA[1]]></szines>
		<harom_d><![CDATA[0]]></harom_d>
	  </kep>
	  <hang>
	    <nyelv><![CDATA[magyar]]></nyelv>
		<csatorna><![CDATA[5.1]]></csatorna>
		<kodolas><![CDATA[AC3]]></kodolas>
	  </hang>
	  <hang>
	    <nyelv><![CDATA[angol]]></nyelv>
		<csatorna><![CDATA[5.1]]></csatorna>
		<kodolas><![CDATA[AC3]]></kodolas>
	  </hang>
	  <felirat>
	    <nyelv><![CDATA[magyar]]></nyelv>
		<nema><![CDATA[0]]></nema>
		<kommentar><![CDATA[0]]></kommentar>
	  </felirat>
	  <peldany azon=\"1\">
	    <allapot><![CDATA[bent]]></allapot>
		<eredeti><![CDATA[0]]></eredeti>
		<csomag><![CDATA[1]]></csomag>
		<sorszam><![CDATA[1]]></sorszam>
	  </peldany>
	  <peldany azon=\"2\">
	    <allapot><![CDATA[bent]]></allapot>
		<eredeti><![CDATA[0]]></eredeti>
		<csomag><![CDATA[1]]></csomag>
		<sorszam><![CDATA[2]]></sorszam>
	  </peldany>
	</lemez>
	<lemez>
	  <tipus><![CDATA[Blu-Ray]]></tipus>
	  <menu><![CDATA[1]]></menu>
	  <lemezek_szama><![CDATA[1]]></lemezek_szama>
	  <film_beszerzes><![CDATA[rippelt]]></film_beszerzes>
	  <bovitett><![CDATA[0]]></bovitett>
	  <kep>
	    <keparany><![CDATA[4:3]]></keparany>
		<felbontas><![CDATA[720 x 576]]></felbontas>
		<szines><![CDATA[1]]></szines>
		<harom_d><![CDATA[0]]></harom_d>
	  </kep>
	  <hang>
	    <nyelv><![CDATA[magyar]]></nyelv>
		<csatorna><![CDATA[5.1]]></csatorna>
		<kodolas><![CDATA[AC3]]></kodolas>
	  </hang>
	  <hang>
	    <nyelv><![CDATA[angol]]></nyelv>
		<csatorna><![CDATA[5.1]]></csatorna>
		<kodolas><![CDATA[AC3]]></kodolas>
	  </hang>
	  <felirat>
	    <nyelv><![CDATA[magyar]]></nyelv>
		<nema><![CDATA[0]]></nema>
		<kommentar><![CDATA[0]]></kommentar>
	  </felirat>
	  <peldany azon=\"3\">
	    <allapot><![CDATA[bent]]></allapot>
		<eredeti><![CDATA[0]]></eredeti>
	  </peldany>
	</lemez>
  </film>";
	}

	private function getFilmlistaMaxOldal($data) {
		$mind='-1" OR "x"="x';
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
			if ($data[$index]=="-1") $data[$index]=$mind;
		}
		extract($data);
		$sql='SELECT DISTINCT count(*) AS "db" FROM `lemez` WHERE `azon` IN (SELECT DISTINCT `lemez` FROM `hang` WHERE (SELECT `nyelv` FROM `hangok` WHERE `azon`=`hang`)="'
		     .$feltSzinkron.'" AND `lemez` IN (SELECT `azon` FROM `lemez` WHERE `tipus`="'.$feltTipus.'" AND `azon` IN (SELECT `film` FROM `mufaj` WHERE `film` IN
			 (SELECT `azon` FROM `film` WHERE INSTR(`cim`,"'.$feltCim.'") OR INSTR(`angol_cim`,"'.$feltCim.'")) AND `mufaj` = "'.$feltMufaj.'")) GROUP BY `lemez`)
			 GROUP BY `azon`,`film`;';
		$sql=mysql_query($sql,$this->sql->getConn());
		$sql=mysql_fetch_assoc($sql);
		return ((int)($sql['db']/$elem))+1;
	}

	public function printXmlEmpty() {
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><empty />";
	}

	public function printXmlMaxoldal($data) {
		$maxOldal=$this->getFilmlistaMaxOldal($data);
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<maxoldal val=\"".$maxOldal."\" />";
	}

	public function printXmlFilmlista($data) {
		$mind='-1" OR "x"="x';
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
			if ($data[$index]=="-1") $data[$index]=$mind;
		}
		extract($data);
		if (!($elem>=1 && $elem<=100)) $elem="10";
		if (!($oldal>=1 && $oldal<=$this->getFilmlistaMaxOldal($data))) $oldal="1";
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<lista>
  <film azon=\"1\">
    <cim><![CDATA[Avatár]]></cim>
    <angol_cim><![CDATA[Avatar]]></angol_cim>
    <gyart_ev><![CDATA[2009]]></gyart_ev>
    <letrehozva><![CDATA[2010-07-19 14:06:31]]></letrehozva>
	<snapshot><![CDATA[1]]></snapshot>
	<snapshot><![CDATA[2]]></snapshot>
  </film>
  <film azon=\"2\">
    <cim><![CDATA[Avatár]]></cim>
    <angol_cim><![CDATA[Avatar]]></angol_cim>
    <gyart_ev><![CDATA[2009]]></gyart_ev>
    <letrehozva><![CDATA[2010-07-19 14:06:31]]></letrehozva>
	<snapshot><![CDATA[1]]></snapshot>
	<snapshot><![CDATA[2]]></snapshot>
  </film>
</lista>";
	}

	public static function printFilmHead() {
		print '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="lib/film/jquery.autocomplete.css" />
<link rel="stylesheet" type="text/css" href="lib/film/thickbox.css" />
<link href="style.css" rel="stylesheet" type="text/css" />
<link href="files/menu/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="lib/film/jquery.js"></script>
<script type="text/javascript" src="lib/film/jquery.bgiframe.min.js"></script>
<script type="text/javascript" src="lib/film/jquery.ajaxQueue.js"></script>
<script type="text/javascript" src="lib/film/thickbox-compressed.js"></script>
<script type="text/javascript" src="lib/film/jquery.autocomplete.js"></script>';
	}

	public function printFilmFeltetelMenu() {
		print '            ';
        print '<form id="film_felt" action="'.$_SERVER['SCRIPT_NAME'].'" method="post">
              <table id="film_feltetel">
                <tr>
                  <td>Filmcím: <input id="film_cim" style="width: 100px;" type="text" /></td>
                  <td>';$this->printFilmFeltetel($this->getLista('lemeztípus'),'típus','tipus_felt');print '</td>
                  <td>';$this->printFilmFeltetel($this->getMinListaHangok(),'szinkron','szinkron_felt');print '</td>
                  <td>';$this->printFilmFeltetel($this->getLista('műfaj'),'műfaj','mufaj_felt'); print '</td>
                  <td><input id="film_feltetelButton" type="submit" value="Beállítás" /></td>
                </tr>
              </table>
            </form>'."\n";
	}

	private function printFilmFeltetel($lista,$ossz,$id) {
		print '<select class="film_feltetel" id="'.$id.'">';
		$selected='selected="selected"';
		print '<option value="-1" '.$selected.' >Összes '.$ossz.'</option>';
		$selected='';
		foreach($lista as $index => $ertek) {
			print '<option value="'.$index.'"'.$selected.'>'.$ertek.'</option>';
		}
		print '</select>';
	}

	public function addSzemely($nev,$nem,$sz_datum) {
		$nev=mysql_real_escape_string($nev,$this->sql->getConn());
		$nem=mysql_real_escape_string($nem,$this->sql->getConn());
		$sz_datum=mysql_real_escape_string($sz_datum,$this->sql->getConn());
		$sql="INSERT INTO `szemely` (`nev` , `nem` , `szul_datum`) VALUES ('".$nev."','".$nem."','".$sz_datum."')";
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addKategoria($megnev) {
		$megnev=mysql_real_escape_string($megnev,$this->sql->getConn());
		$sql="INSERT INTO `kategoria` (`azon` , `megnev`) VALUES (NULL , '".$megnev."')";
		return mysql_query($sql,$this->sql->getConn());
	}

	public function setKategoria($azon,$megnev) {
		$azon=mysql_real_escape_string($azon,$this->sql->getConn());
		$megnev=mysql_real_escape_string($megnev,$this->sql->getConn());
		$sql="UPDATE `kategoria` SET `megnev` =  '".$megnev."' WHERE `azon`=".$azon;
		return mysql_query($sql,$this->sql->getConn());
	}

	public function getKategoriaList() {
		$sql='SELECT `azon`,`megnev` FROM `kategoria`';
		$sql = mysql_query($sql,$this->sql->getConn());
		$list=array();
		while($sor = mysql_fetch_assoc($sql)) {
			$list[$sor['azon']]=$sor['megnev'];
		}
		return $list;
	}

	public function delKategoria($azon) {
		$azon=mysql_real_escape_string($azon,$this->sql->getConn());
		$sql="DELETE FROM `kategoria` WHERE `azon` = ".$azon;
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addLista($megnev,$kat) {
		$megnev=mysql_real_escape_string($megnev,$this->sql->getConn());
		$kat=mysql_real_escape_string($kat,$this->sql->getConn());
		$sql="INSERT INTO `lista` (`azon` , `megnev` , `kategoria`) VALUES (NULL , '".$megnev."' , ".$kat.")";
		return mysql_query($sql,$this->sql->getConn());
	}

	public function setListaMegnev($azon,$megnev) {
		$azon=mysql_real_escape_string($azon,$this->sql->getConn());
		$megnev=mysql_real_escape_string($megnev,$this->sql->getConn());
		$sql="UPDATE `lista` SET `megnev` =  '".$megnev."' WHERE `azon`=".$azon;
		return mysql_query($sql,$this->sql->getConn());
	}

	public function setListaKat($azon,$kat) {
		$azon=mysql_real_escape_string($azon,$this->sql->getConn());
		$kat=mysql_real_escape_string($kat,$this->sql->getConn());
		$sql="UPDATE `lista` SET `kat` =  '".$kat."' WHERE `lista`.`azon`=".$azon;
		return mysql_query($sql,$this->sql->getConn());
	}

	public function getListaList() {
		$sql='SELECT `azon`,`megnev` FROM `lista`';
		$sql = mysql_query($sql,$this->sql->getConn());
		$list=array();
		while($sor = mysql_fetch_assoc($sql)) {
			$list[$sor['azon']]=$sor['megnev'];
		}
		return $list;
	}

	public function getLista($mit) {
		$mit=mysql_real_escape_string($mit,$this->sql->getConn());
		$sql='SELECT `azon`,`megnev` FROM `lista` WHERE `kategoria` IN (SELECT `azon` FROM `kategoria` WHERE `megnev`="'.$mit.'")';
		$sql = mysql_query($sql,$this->sql->getConn());
		$i=0;
		$nyelv=array();
		while($sor = mysql_fetch_assoc($sql)) {
			$nyelv[$sor['azon']]=$sor['megnev'];
			++$i;
		}
		return $nyelv;
	}

	public function delLista($azon) {
		$azon=mysql_real_escape_string($azon,$this->sql->getConn());
		$sql="DELETE FROM `lista` WHERE `lista`.`azon` = ".$azon;
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addFilm($data,$borito) {
		if ($borito['size']>0) {
			if (preg_match('/(bmp|jpe?g|png)$/i',$borito['name'])) {
				$file=md5(time().rand()).'.'.substr($borito['name'],strrpos($borito['name'],'.')+1);
				move_uploaded_file($borito['tmp_name'],"files/filmek/borito/".$file);
			}
			else $file="";
		}
		else $file="";
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		$sql=sprintf("INSERT INTO `film` (`cim` , `angol_cim` , `nemzetiseg` , `hossz` , `gyart_ev` , `korhatar` , `leiras` , `borito`) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s')",$cim,$angol_cim,$nemzetiseg,$hossz,$gyart_ev,$korhatar,$leiras,$file);
		$ok=mysql_query($sql,$this->sql->getConn());
		if (!$ok && $file!="") unlink("files/filmek/borito/".$file);
		return $ok;
	}

	public function addSnapshot($film,$kep) {
		if ($kep['size']>0) {
			if (preg_match('/(bmp|jpe?g|png)$/i',$kep['name'])) {
				$file=md5(time().rand()).'.'.substr($kep['name'],strrpos($kep['name'],'.')+1);
				move_uploaded_file($kep['tmp_name'],"files/filmek/snapshot/".$file);
				$film=mysql_real_escape_string($film,$this->sql->getConn());
				$sql=sprintf("INSERT INTO `snapshot` (`film` , `file`) VALUES ('%s','%s')",$film,$file);
				$ok=mysql_query($sql,$this->sql->getConn());
				if (!$ok) unlink("files/filmek/snapshot/".$file);
				return $ok;
			}
		}
		return false;
	}

	public function addLemez($data) {
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		$menu=isset($menu)?'1':'0';
		$bovitett=isset($bovitett)?'1':'0';
		$sql=sprintf("INSERT INTO `lemez` (`film` , `tipus` , `lemez_db` , `menu` , `film_beszerzes` , `bovitett`) VALUES ('%s','%s','%s','%s','%s','%s')",$film,$tipus,$lemez_db,$menu,$film_beszerzes,$bovitett);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addHangok($nyelv,$csatorna,$kodolas) {
		$nyelv=mysql_real_escape_string($nyelv,$this->sql->getConn());
		$csatorna=mysql_real_escape_string($csatorna,$this->sql->getConn());
		$kodolas=mysql_real_escape_string($kodolas,$this->sql->getConn());
		$sql=sprintf("INSERT INTO `hangok` (`nyelv` , `csatorna` , `kodolas`) VALUES ('%s','%s','%s')",$nyelv,$csatorna,$kodolas);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addKepek($data) {
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		$szines=isset($szines)?'1':'0';
		$harom_d=isset($harom_d)?'1':'0';
		$sql=sprintf("INSERT INTO `kepek` (`keparany` , `felbontas` , `szines` , `3D`) VALUES ('%s','%s','%s','%s')",$keparany,$felbontas,$szines,$harom_d);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addFeliratok($data) {
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		$nema=isset($nema)?'1':'0';
		$kommentar=isset($kommentar)?'1':'0';
		$sql=sprintf("INSERT INTO `feliratok` (`nyelv` , `nema` , `kommentar`) VALUES ('%s','%s','%s')",$nyelv,$nema,$kommentar);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addStab($data) {
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		$sql=sprintf("INSERT INTO `stab` (`film` , `szemely` , `munka`) VALUES ('%s','%s','%s')",$film,$szemely,$munka);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addSzerep($data) {
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		if ($szinkron!="null") $sql=sprintf("INSERT INTO `szerep` (`film` , `szemely` , `szerep` , `szinkron`) VALUES ('%s','%s','%s','%s')",$film,$szemely,$munka,$szinkron);
		else $sql=sprintf("INSERT INTO `szerep` (`film` , `szemely` , `szerep`) VALUES ('%s','%s','%s')",$film,$szemely,$munka);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addMufaj($data) {
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		$sql=sprintf("INSERT INTO `mufaj` (`film` , `mufaj`) VALUES ('%s','%s')",$film,$mufaj);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addPeldany($data) {
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		$eredeti=isset($eredeti)?'1':'0';
		if ($azon!="") $sql=sprintf("INSERT INTO `peldany` (`azon` , `lemez` , `lemez_sorszam` , `allapot` , `eredeti`) VALUES ('%s','%s','%s','%s','%s')",$azon,$lemez,$lemez_ssz,$allapot,$eredeti);
		else $sql=sprintf("INSERT INTO `peldany` (`lemez` , `lemez_sorszam` , `allapot` , `eredeti`) VALUES ('%s','%s','%s','%s')",$lemez,$lemez_ssz,$allapot,$eredeti);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addKep($data) {
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		$sql=sprintf("INSERT INTO `kep` (`lemez` , `kep` , `nezet_db`) VALUES ('%s','%s','%s')",$lemez,$kep,$nezet_db);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addHang($data) {
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		$sql=sprintf("INSERT INTO `hang` (`lemez` , `hang`) VALUES ('%s','%s')",$lemez,$hang);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addExtra($data) {
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		$sql=sprintf("INSERT INTO `extra` (`lemez` , `extra`) VALUES ('%s','%s')",$lemez,$extra);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function addFelirat($data) {
		foreach($data as $index => $ertek) {
			$data[$index]=mysql_real_escape_string($ertek,$this->sql->getConn());
		}
		extract($data);
		$sql=sprintf("INSERT INTO `felirat` (`lemez` , `felirat`) VALUES ('%s','%s')",$lemez,$felirat);
		return mysql_query($sql,$this->sql->getConn());
	}

	public function getListaLemez() {
		$sql='SELECT * FROM `lemez`';
		$sql = mysql_query($sql,$this->sql->getConn());
		$i=0;
		$lemez=array();
		while($sor = mysql_fetch_assoc($sql)) {
			$lemez[$i]=$sor;
			++$i;
		}
		$return=array();
		foreach($lemez as $index => $ertek) {
			if ($ertek['bovitett']) $bovitett="bővített";
			else $bovitett="";
			$i=" [";
			$j="]";
			$k="";
			if ($ertek['bovitett']) $k=", ";
			$sql='SELECT `cim` FROM `film` WHERE `azon`="'.$ertek['film'].'"';
			$sql = mysql_query($sql,$this->sql->getConn());
			$cim = mysql_fetch_assoc($sql);
			$cim = $cim['cim'];
			$return[$ertek['azon']]=$cim.$i.$ertek['film_beszerzes'].$k.$bovitett.$j;
		}
		return $return;
	}

	public function getListaHangok() {
		$sql='SELECT * FROM `hangok`';
		$sql = mysql_query($sql,$this->sql->getConn());
		$i=0;
		$hang=array();
		while($sor = mysql_fetch_assoc($sql)) {
			$hang[$i]=$sor;
			++$i;
		}
		$return=array();
		foreach($hang as $index => $ertek) {
			$sql='SELECT `megnev` FROM `lista` WHERE `azon`="'.$ertek['nyelv'].'"';
			$sql = mysql_query($sql,$this->sql->getConn());
			$nyelv = mysql_fetch_assoc($sql);
			$nyelv = $nyelv['megnev'];
			$sql='SELECT `megnev` FROM `lista` WHERE `azon`="'.$ertek['csatorna'].'"';
			$sql = mysql_query($sql,$this->sql->getConn());
			$csatorna = mysql_fetch_assoc($sql);
			$csatorna = $csatorna['megnev'];
			$sql='SELECT `megnev` FROM `lista` WHERE `azon`="'.$ertek['kodolas'].'"';
			$sql = mysql_query($sql,$this->sql->getConn());
			$kodolas = mysql_fetch_assoc($sql);
			$kodolas = $kodolas['megnev'];
			$return[$ertek['azon']]=$nyelv.' '.$kodolas.' '.$csatorna;
		}
		return $return;
	}

	public function getMinListaHangok() {
		$sql='SELECT DISTINCT `nyelv` FROM `hangok` GROUP BY `nyelv`';
		$sql = mysql_query($sql,$this->sql->getConn());
		$i=0;
		$hang=array();
		while($sor = mysql_fetch_assoc($sql)) {
			$hang[$i]=$sor;
			++$i;
		}
		$return=array();
		foreach($hang as $index => $ertek) {
			$sql='SELECT `megnev` FROM `lista` WHERE `azon`="'.$ertek['nyelv'].'"';
			$sql = mysql_query($sql,$this->sql->getConn());
			$nyelv = mysql_fetch_assoc($sql);
			$nyelv = $nyelv['megnev'];
			$return[$ertek['nyelv']]=$nyelv;
		}
		return $return;
	}
	
	public function getListaKepek() {
		$sql='SELECT * FROM `kepek`';
		$sql = mysql_query($sql,$this->sql->getConn());
		$i=0;
		$kep=array();
		while($sor = mysql_fetch_assoc($sql)) {
			$kep[$i]=$sor;
			++$i;
		}
		$return=array();
		foreach($kep as $index => $ertek) {
			$sql='SELECT `megnev` FROM `lista` WHERE `azon`="'.$ertek['keparany'].'"';
			$sql = mysql_query($sql,$this->sql->getConn());
			$keparany = mysql_fetch_assoc($sql);
			$keparany = $keparany['megnev'];
			$sql='SELECT `megnev` FROM `lista` WHERE `azon`="'.$ertek['felbontas'].'"';
			$sql = mysql_query($sql,$this->sql->getConn());
			$felbontas = mysql_fetch_assoc($sql);
			$felbontas = $felbontas['megnev'];
			if ($ertek['szines']) $szines="színes ";
			else $szines="fekete-fehér ";
			if ($ertek['3D']) $harom_d="3D ";
			else $harom_d='';
			$return[$ertek['azon']]=$szines.$harom_d.$felbontas.' '.$keparany;
		}
		return $return;
	}

	public function getListaFeliratok() {
		$sql='SELECT * FROM `feliratok`';
		$sql = mysql_query($sql,$this->sql->getConn());
		$i=0;
		$felirat=array();
		while($sor = mysql_fetch_assoc($sql)) {
			$felirat[$i]=$sor;
			++$i;
		}
		$return=array();
		foreach($felirat as $index => $ertek) {
			$sql='SELECT `megnev` FROM `lista` WHERE `azon`="'.$ertek['nyelv'].'"';
			$sql = mysql_query($sql,$this->sql->getConn());
			$nyelv = mysql_fetch_assoc($sql);
			$nyelv = $nyelv['megnev'];
			if ($ertek['nema']) $nema="néma";
			else $nema="";
			if ($ertek['kommentar']) $kommentar="kommentár";
			else $kommentar="";
			$k="";
			if ($ertek['kommentar'] || $ertek['nema']) {
				$i=" [";
				$j="]";
				if ($ertek['kommentar']) $k=", ";
			}
			else {
				$i="";
				$j="";
			}
			$return[$ertek['azon']]=$nyelv.$i.$kommentar.$k.$nema.$j;
		}
		return $return;
	}

	public function getListaFilm() {
		$sql='SELECT * FROM `film`';
		$sql = mysql_query($sql,$this->sql->getConn());
		$i=0;
		$film=array();
		while($sor = mysql_fetch_assoc($sql)) {
			$film[$i]=$sor;
			++$i;
		}
		$return=array();
		foreach($film as $index => $ertek) {
			if ($ertek['angol_cim']) {
				$i=" [";
				$j="]";
			}
			else {
				$i="";
				$j="";
			}
			$return[$ertek['azon']]=$ertek['cim'].$i.$ertek['angol_cim'].$j;
		}
		return $return;
	}

	public function getListaSzemely() {
		$sql='SELECT * FROM `szemely`';
		$sql = mysql_query($sql,$this->sql->getConn());
		$i=0;
		$szemely=array();
		while($sor = mysql_fetch_assoc($sql)) {
			$szemely[$i]=$sor;
			++$i;
		}
		$return=array();
		$ek=new Eletkor;
		foreach($szemely as $index => $ertek) {
			$eletkor=$ek->getEletkor($ertek['szul_datum'].'-01-01');
			$eletkor=$eletkor['year'];
			if ($eletkor!=-1 && (string)$eletkor!=date("Y",time())) {
				$i=" [";
				$j="]";
			}
			else {
				$eletkor="";
				$i="";
				$j="";
			}
			$return[$ertek['azon']]=$ertek['nev'].$i.(string)$eletkor.$j;
		}
		return $return;
	}

}

?>
