<?php
//kiterjeszti a mysqli osztályt, azért hoztam létre, hogy ne kelljen többször megadni az sql connect paramétereit - kivéve az adatbázis nevét
class Sql extends MySQLi {
	
	public function __construct($dbname) {
		ini_set('magic_quotes_gpc', '1');
		include '/var/sql_config.php';
		$this->connect('localhost',$dbuser,$dbpass,$dbname,$dbport);
		if ($this->connect_error) die('Sql kapcsolódás hiba ('.$this->connect_errno.'): '.$this->connect_error);
		$this->set_charset("utf8");
	}

	public function __destruct() {
		$this->close();
	}

}

//felülírja az alapértelmezett PHP munkamenetkezelőjét és helyette sql-t használ. A regen változó megmondja, hogy új session jött-e létre
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

	//ezt kellene megírni úgy, hogy az első paraméter a függvény neve legyen, és akárhány paraméterrel működjön, és áttenni az Sql osztályba, végül megcsinálni a call függvényt is.
	//segítség: func_get_args, func_num_args
	/*
	és a foreach szintaktika:	
	foreach (array_expression as $value)
    	statement
	foreach (array_expression as $key => $value)
    	statement
	*/
	/*
	private static function select($name,$param) {
		$query=self::$sql->query("SELECT ".$name."('".$param."') AS 'val';");
		$query=$query->fetch_assoc();
		return $query['val'];
    }

	private static function select() {
		
	}
*/
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
		if (!$id) die('Create Session Failed');
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

//felhasználókkal kapcsolatos műveletek. pl. login, logout
class User {
  private static $sql;

	public function __construct() {
		self::$sql = new Sql('user');
		echo "<pre>\r";
		if(!isset($_SESSION['login']) && !$_SESSION['login']) {			
			print_r(self::setLogin('fzoli','0165c3d06a4a666f042c120076236568'));
			echo PHP_EOL.'Bejelentkezés megtörtént.'.PHP_EOL;
		}
		else print_r(self::getLogin());
		if (isset($_SESSION['warning'])) print $_SESSION['warning'];
		echo "<pre>\r";
	}

	private static function getErrorMessage($data) {
		if (isset($data['code'])) {
			$code = array('Nem létező felhasználónév.',
						  'Felhasználó bannolva. Hátralévő idő: ',
						  'Nincs több belépési lehetősége. Hátralévő idő: ',
						  'Hibás jelszó. Fennmaradt lehetőség: '
						  );
			$ret=$code[$data['code']];
			if (isset($data['value']))
				if ($data['code']==3) $ret.=$data['value'];
				else if ($data['code']==1 && $data['value']==0) $ret.="örökre";
				     else $ret.=self::getLeftTime($data['value']);
			return $ret;
		}
	}

	private static function getLeftTime($value) {
		try {
			$from = new DateTime();
			$to = new DateTime($value);
		}
		catch(Exception $i) {
			return "örökre";
		}
		$time=$from->diff($to);
		$time=$time->format('%y-%m-%d-%h-%i-%s');
		if ($time=="0-0-0-0-0-0") return "letelt";
		$value=explode("-",$time);
		for ($i=3;$i<=5;++$i)
			$value[$i]=sprintf("%02d",$value[$i]);
		$ret="";
		if ($value[0]) {
			$ret.=$value[0]." év";
			if ($value[1]+$value[2]) $ret.=", ";
		}
		if ($value[1]) {
			$ret.=$value[1]." hónap";
			if ($value[2]) $ret.=", ";
		}
		if ($value[2]) $ret.=$value[2]." nap";
		if ($value[3]+$value[4]+$value[5]) {
			if ($value[0]+$value[1]+$value[2]) $ret.=" és ";
			$ret.=$value[3].":".$value[4].":".$value[5];
		}
		return $ret;
	}

	public static function getLogin() {
		if (isset($_SESSION['login']) && $_SESSION['login']) {
			$q=self::$sql->query("CALL getUser('".session_id()."');");
			$data=$q->fetch_assoc();
			if (isset($data['azon'])) {
				return $data;
			}
			else {
				$_SESSION['error']=self::getErrorMessage($data);
				return false;
			}
		}
		else {
			return null;
		}
	}

	public static function setLogin($azon,$jelszo) {
		$q=self::$sql->query("CALL setUser('".$azon."', '".$jelszo."', '".session_id()."');");
		$data=$q->fetch_assoc();
		if (isset($data['azon'])) {
			$_SESSION['login']=true;
			return $data;
		}
		else {
			$_SESSION['error']=self::getErrorMessage($data);
			return false;
		}
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
		print "Session ID: ".session_id();
	}

}

new Page(); //teszt

?>
