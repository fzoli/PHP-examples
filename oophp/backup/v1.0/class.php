<?php
//kiterjeszti a mysqli osztályt, azért hoztam létre, hogy ne kelljen többször megadni az sql connect paramétereit - kivéve az adatbázis nevét
class Sql extends MySQLi {
	
	public function __construct($dbname) {
		ini_set('magic_quotes_gpc', '1');
		include 'C:/wamp/sql_config.php';
		$this->connect('localhost',$dbuser,$dbpass,$dbname,$dbport);
		if ($this->connect_error) die('Sql kapcsolódás hiba ('.$this->connect_errno.'): '.$this->connect_error);
		$this->set_charset("utf8");
	}

	public function __destruct() {
		$this->close();
	}

}

//felülírja az alapértelmezett PHP munkamenetkezelőjét és helyette sql-t használ. A regen változó megmondja, hogy újra lett-e generálva a session id
class Session {
  private static $sql, $regen;

	public function __construct() {
			if (is_null(self::$sql)) { //hogy ne legyen gond a többszörös példányosítás
				ini_set('session.name','fzoltan_sessid');
				ini_set('session.use_only_cookies','1');
				ini_set('session.use_trans_sid','0');
				ini_set('session.cookie_lifetime','0');
				ini_set('session.referrer_check','domain.tld');
				session_set_save_handler(	array(&$this,"open"),
											array(&$this,"close"),
											array(&$this,"read"),
											array(&$this,"write"),
											array(&$this,"destroy"),
											array(&$this,"gc")
										);
				session_start();
			}
	}

	public function __destruct(){
		@session_write_close(); //hogy a bugos php ne bontson!
	}

	public function getRegen() {
		return self::$regen;
	}

	public static function open() {
		self::$sql = new Sql('user');
		self::$regen = false;
		if (self::needNewSess()) self::setNewSess();
		else self::changeSessIdIfNeed();
		return true;
	}

	public static function close() {
		//nem kell semmit tenni, mert autómatikusan megteszi a PHP az objektum megszünésekor - a kód lefuás után
		return true;
	}

	public static function read($id) {
		return self::getSessData();
	}

	public static function write($id,$data) {
		return self::setSessData($data);
	}
	
	//session törlése
	public static function destroy($id) {
		return self::delSess();
	}

	//az SQL GC elvégzi a PHP helyett
	public static function gc($expire) {
		return true;
	}

	private static function getKey() {
		return sha1($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_HOST']);
	}

	private static function isSessIdValid() {
    	return (bool)preg_match('/^[0-9a-f]{80}$/i', session_id());
	}

	//beállítja a sütit és a php-t az adott ID-re
	private static function setSessId($id) {
		session_id($id);
		setcookie(session_name(),$id,ini_get('session.cookie_lifetime'),"/");
	}

	private static function getSessData() {
		$query=self::$sql->query("SELECT getSessionData('".session_id()."') AS 'val';");
		$query=$query->fetch_assoc();
		$data=$query['val'];
		return (string)$data;
	}

	private static function setSessData($data) {
		return self::$sql->query("CALL setSessionData('".session_id()."', '".$data."');");
	}

	//megkéri az sql-t hogy cseréljen session id-t, ha kell és adja vissza az új session id-t, amit a php beállít majd
	private static function changeSessIdIfNeed() {
		$id=session_id();
		$query=self::$sql->query("SELECT changeSessionIdIfNeed('".$id."') AS 'val';");
		$query=$query->fetch_assoc();
		if ($query['val']){ //ha az sql lecserélte a sessiont (új id-t adott vissza) süti és php beállítása
			$id=$query['val'];
			self::setSessId($id);
		}
	}

	//új session id jön létre az sql-ben, php beállítja a sütit és a sessiont
	private static function setNewSess() {
		$query=self::$sql->query("SELECT addSession('".self::getKey()."') AS 'val';");
		$query=$query->fetch_assoc();
		$id=$query['val'];
		self::setSessId($id);
	}

	//visszatérési értéke megmondja, hogy kell-e új sessiont csinálni - ezzel új sessId is lessz
	private static function needNewSess() {
		if(isset($_COOKIE[session_name()]) && self::isSessIdValid()) {
			if (self::isSessIdSet()) return false;
			self::$regen=true;
		}
		return true;
	}

	//session_destroy függvénynek, hogy lehessen session-t törölni
	private static function delSess() {
		return self::$sql->query("CALL delSession('".session_id()."');");
	}

	//ahhoz kell tudni, hogy a sütiben megadott sessId létezik-e az sql-ben, mert ha nem, új id-t kell generálnia az sql-nek
	private static function isSessIdSet() {
		$query=self::$sql->query("SELECT isSessionIdValid('".session_id()."', '".self::getKey()."') AS 'val';");
		$query=$query->fetch_assoc();
		return $query['val'];
	}

}

class Birthdate {
  private $year, $month, $day, $now;
	
	public function __construct($date) {
		$e=explode('-',$date);
		$this->year=(int)$e[0];
		$this->month=(int)$e[1];
		$this->day=(int)$e[2];
		$this->now=getdate();
	}

	public function check() {
		return $this->checkDate() && $this->checkMax() && $this->checkMin();
	}

	private function checkDate() {
		return checkdate($this->month, $this->day, $this->year);
	}

	private function checkMin() {
		return $this->year >= $this->now['year']-120;
	}

	private function checkMax() {
		$date=mktime(0,0,0,$this->month,$this->day,$this->year);
		$today=mktime(0,0,0,$this->now['mon'],$this->now['mday'],$this->now['year']);
		return $date<=$today;
	}

}

//felhasználókkal kapcsolatos műveletek. pl. login, logout
class User {
  private static $sql;

	public function __construct() {
		self::$sql = new Sql('user');
		$t['azon']='fzoli';
		$t['jelszo']='0165c3d06a4a666f042c120076236568';
		self::setLogin($t);
	}

	private static function getErrorMessage($data) {
		if (isset($data['code'])) {
			$code = array('Nem létező felhasználónév!',
						  'Felhasználó bannolva! Hátralévő idő: ',
						  'Nincs több belépési lehetősége! Hátralévő idő: ',
						  'Hibás jelszó! Fennmaradt lehetőség: '
						  );
			$ret=$code[$data['code']];
			if (isset($data['value']))
				if ($data['code']==3) $ret.=$data['value'];
				else if ($data['code']==1 && $data['value']==0) $ret.="örökre";
				     else $ret.=self::getDateString($data['value']);
			return $ret;
		}
	}

	//ideignlenes megoldás az elkövetkező 20-25 évre (most 2010 van :P)
	//ezt módosítani kell majd sql-ben hogy ne másodpercet adjon vissza hanem a lejárat dátumát és sql-ben lehet növelni a dátumot és persze eltárolni
	//new DateTime("9999-12-31 23:59:59") paranccsal pl lehet dátumot létrehozni
	private static function getDateString($value) {
		if ((int)(time()+$value)!=(time()+$value)) return "örökre";
		if ($value!=0) {
		$time = getdate(time());
		$from = new DateTime();
		$from->setDate($time['year'],$time['mon'],$time['mday']);
		$from->setTime($time['hours'],$time['minutes'],$time['seconds']);
		$time = getdate(time()+(float)$value);
		$to = new DateTime();
		$to->setDate($time['year'],$time['mon'],$time['mday']);
		$to->setTime($time['hours'],$time['minutes'],$time['seconds']);
		$time=$from->diff($to);
		$time=$time->format('%y-%m-%d-%h-%i-%s');
		$value=explode("-",$time);
		if ($value[0]>0) $value[0].=" év, ";
		else $value[0]="";
		if ($value[1]>0) $value[1].=" hónap, ";
		else $value[1]="";
		if ($value[2]>0) $value[2].=" nap és ";
		else $value[2]="";
		$v=":";
		if ($value[3]+$value[4]+$value[5]==0) {
			$v="";
			$value[2]=substr($value[2],0,strlen($value[2])-5);
			for($i=3;$i<=5;++$i)
				$value[$i]="";
		}
		else for($i=3;$i<=5;++$i)
				$value[$i]=sprintf("%02d",$value[$i]);
		}
		else return "letelt";
		return $value[0].$value[1].$value[2].$value[3]."$v".$value[4]."$v".$value[5];
	}

	public static function setLogin($post) {
		//$t1 = time()+microtime();
		$q=self::$sql->query("CALL setUser('".$post['azon']."', '".$post['jelszo']."', '".session_id()."');");
		//$t2 = time()+microtime();
		//echo "<pre>\r";
		//echo $t2-$t1."\r";
		$data=$q->fetch_assoc();
		//print_r($data);
		if (isset($data['azon'])) {
			print 'Bejelentkezett: '.$data['azon'];
			$_SESSION['login']=true;
		}
		else {
			print $_SESSION['error']=self::getErrorMessage($data);
		}
		//echo "\r</pre>";
	}

}

//ő az isten, ő irányít mindent pl. session, user osztályt - szóval egy konténer is
class Page {
  private static $session, $user;

	public function __construct() {
		error_reporting(E_ALL);
		date_default_timezone_set("Europe/Budapest");
		header ('Content-Type: text/html; charset=utf-8');
		self::$session = new Session();
		self::$user = new User();
		print self::$session->getRegen()?'Lejárt munkamenet.<br />':'';
	}

}

new Page(); //teszt

?>