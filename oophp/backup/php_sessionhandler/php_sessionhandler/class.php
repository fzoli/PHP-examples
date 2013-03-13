<?php
/*
//Mivel a routeremen lévő PHP-ban nincs DOMDocument osztály, megírtam a minimumot xD
class SelfDOMDocument {
  private $version, $encoding, $children;

	public function __construct($version, $encoding) {
		$this->version = $version;
		$this->encoding = $encoding;
		$this->children = array();
	}

	public function createElement($name) {
		return new SelfElement($name);
	}

	public function appendChild($element) {
		$this->children[] = $element;
	}

	public function saveXML() {
		$xml = '<?xml version="'.$this->version.'" encoding="'.$this->encoding.'"?>'.PHP_EOL;
		foreach ($this->children as $child) {
			$xml .= $this->getElementString($child);
		}
		return $xml;
	}

	private function getElementString($element) {
		$string = '<'.$element->getName();
		foreach ($element->getAttributes() as $name => $value) {
			$string .= ' '.$name.'="'.$value.'"';
		}
		if ($element->isChild()) {
			$string .= '>';
			foreach($element->getChildren() as $child) {
				$string .= $this->getElementString($child);
			}
		}
		else {
			$string .= '/>';
		}
		$string .= $this->getElementCloseString($element);
		return $string;
	}

	private function getElementCloseString($element) {
		$string = '';
		if ($element->getChildren())
			$string .= '</'.$element->getName().'>';
		return $string;
	}

}

//ez is kell a DOMDocument utánzatnak :P
class SelfElement {
  private $name, $attrs, $childs;

	public function __construct($name) {
		$this->name = $name;
		$this->attrs = array();
		$this->children = array();
	}

	public function setAttribute($name, $value) {
		$this->attrs[$name] = $value;
	}

	public function getName() {
		return $this->name;
	}

	public function getAttributes() {
		return $this->attrs;
	}

	public function appendChild($element) {
		$this->children[] = $element;
	}

	public function getChildren() {
		return $this->children;
	}

	public function isChild() {
		return (bool)count($this->children);
	}

}

//hogy ne kelljen az eredeti kódot átírni a routeren
class DOMDocument extends SelfDOMDocument {}
*/

//Létrehozza a kapcsolatot a MySQL adatbázis-szerverrel.
//Webszerveren kívülről bemásolja a paramétereket. Így ha meghal a PHP és a webszerver benthagyja a PHP kódot, nem férnek hozzá a jelszóhoz továbbra sem.
//Kibővítve select illetve execute metódussal
class ConfiguredSql extends PDO {

	public function __construct($dbname, $configPath) {
		include $configPath;
		$dsn = 'mysql:dbname='.$dbname.';host='.$dbhost.';port='.$dbport;
		if (isset($dbsock)) $dsn .= ';unix_socket='.$dbsock;
		try {
			parent::__construct($dsn, $dbuser, $dbpass);
			$this->exec('SET CHARACTER SET utf8');
		}
		catch(PDOException $ex) {
			die('MySQL connection failed: ' . $ex->getMessage());
		}
	}

	private static function isCommandValid($command) {
		return (bool)!preg_match('/[,()\'"]/', $command);
	}

	//a tömbön foreach elven előállít egy stringet SQL paraméternek
	private function createSqlArgs($args) {
		unset($args[0]);
		$return = array();
		foreach($args as $param) {
			$return[] = $this->quote($param);
		}
		return implode(', ', $return);
	}

	//paramétereket átadva a visszatérési értéke az sql függvény visszatérési értéke :)
	public function select($command) {
		if (!self::isCommandValid($command)) return ;
		$args = $this->createSqlArgs(func_get_args());
		$query = 'SELECT `'.$command.'`('.$args.') AS \'val\';';
		$sth = $this->query($query);
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		return $result['val'];
	}

	//paramétereket átadva sql eljárást hajthat végre és ha van eredmény, visszaadja az eredmény(eke)t tartalmazó objektumot
	public function execute($command) {
		if (!self::isCommandValid($command)) return ;
		$args = $this->createSqlArgs(func_get_args());
		$query = 'CALL `'.$command.'`('.$args.');';
		$sth = $this->prepare($query);
		$sth->execute();
		$results = array();
		if ($sth) {
			while($obj = $sth->fetch(PDO::FETCH_OBJ)) {
				$results[] = $obj;
			}
		}
		return new Result($results);
	}

}

//sql eljárással visszaadott select eredményeit tárolja
class Result {
  private $results;

	public function __construct($results) {
		$this->results = $results;
	}

	public function getValue($value) {
		$values = array();
		foreach($this->results as $obj) {
			if (isset($obj->$value))
				$values[] = $obj->$value;
		}
		return $values;
	}

}

class SessSql extends ConfiguredSql {
	
	public function __construct() {
		parent::__construct('user', 'sql_sess_config.php'); //TODO: config fájl biztonságos helyre és útvonal megadása
	}

}

class Session {
  private static $sql, $expired;

	public function __construct() {
		if (is_null(self::$sql)) { //hogy ne legyen gond a többszörös példányosítás
			$args = func_get_args();
			if(isset($args[0])) $start = $args[0];
			else $start = true;
			$this->initialize($start);
		}
	}

	public function __destruct(){
		@session_write_close(); //hogy a bugos php ne zárja le a munkamenetet idő előtt
	}

	public static function open() {
		if (self::needNewSess()) self::setNewSess();
		else self::changeSessIdIfNeed();
		return true;
	}

	public static function close() {
		//MySQLi lezárja az adatbázis kapcsolatot az objektum megszünésekor, ezért itt nem kell tenni semmit
		return true;
	}

	public static function read($id) {
		return (string)self::getSessData();
	}

	public static function write($id, $data) {
		return self::setSessData($data);
	}
	
	public static function destroy($id) {
		return self::delSess();
	}

	//az SQL GC elvégzi a PHP helyett, ezért itt se kell tenni semmit
	public static function gc($expire) {
		return true;
	}

	//a munkamenethez társított felhasználó azonosítóját adja vissza (teszt függvény)
	public static function getLogin() {
		return self::$sql->select('getSessionLogin', session_id());
	}

	public static function getLoginArray() {
		$result = self::$sql->execute('getLogins');
		return $result->getValue('login');
	}

	public static function getUserCount($user) {
		return self::$sql->select('getUserCount', $user);
	}

	//a munkamenethez lehet társítani felhasználót vele (teszt függvény)
	public static function setLogin($value) {
		$success = (boolean)self::$sql->select('setSessionLogin', session_id(), $value);
		if ($success) self::changeSessIdIfNeed(true); //kényszerített session regen, ha sikerült belépni
		return $success;
	}

	public static function delSessLogin() {
		self::$sql->execute('delSessionLogin', session_id());
	}

	public static function getSessCount() {
		return self::incCountIfNeed(self::$sql->select('getSessionCount'));
	}

	public static function getLoginCount() {
		return self::$sql->select('getLoginCount');
	}

	public static function getDistinctLoginCount() {
		return self::$sql->select('getDistinctLoginCount');
	}

	public static function getGuestCount() {
		return self::incCountIfNeed(self::$sql->select('getGuestCount'));
	}

	public static function getChangetime() {
		return self::$sql->select('getChangetime');
	}

	public static function getLifetime($newsess) {
		return self::$sql->select('getLifetime', $newsess ? 1 : 0);
	}

	public static function getSessLifetime() {
		return self::$sql->select('getSessionLifetime', self::getCookieSessId());
	}

	public static function setChangetime($value) {
		if (is_numeric($value)) self::$sql->execute('setChangetime', $value);
		else trigger_error('Parameter of setChangetime method must be numeric');
	}

	public static function setLifetime($value, $newsess) {
		if (is_numeric($value)) self::$sql->execute('setLifetime', $value, $newsess ? 1 : 0);
		else trigger_error('Second parameter of setLifetime method must be numeric');
	}

	//ha a munkamenet lejárt (törlődött a szerverről) illetve, ha session_destroy után session_start függvények lefutottak, igazat ad vissza
	public function isExpired() {
		return self::$expired;
	}

	private static function isSessNew() {
		return self::$sql->select('isSessionNew', self::getCookieSessId());
	}

	//ha nem létezik session süti (még - mert első kérés -, vagy nem is fog - mert süti inaktív), inkrementálás
	private static function incCountIfNeed($count) {
		if (!self::isSessCookieSet() || self::isExpired()) ++$count;
		else if (self::isSessNew()) ++$count;
		return $count;
	}

	private function initialize($start) {
		self::$sql = new SessSql();
		self::$expired = false;
		ini_set('session.name','session_id');
		ini_set('session.use_only_cookies','1');
		ini_set('session.use_trans_sid','0');
		ini_set('session.cookie_lifetime','0');
		session_set_save_handler(	array(&$this,"open"),
						array(&$this,"close"),
						array(&$this,"read"),
						array(&$this,"write"),
						array(&$this,"destroy"),
						array(&$this,"gc")
					);
		if ($start) session_start();
	}

	//egyedi kulcsot generál a munkamenet számára
	private static function createSessKey() {
		return sha1($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_HOST']);
	}

	//a sütiben szereplő érték formátumát ellenőrzi
	private static function isSessIdValid($id) {
		return (bool)preg_match('/^[0-9a-f]{80}$/i', $id);
	}

	//beállítja a munkamenet-azonosítót a sütibe
	private static function setSessId($id) {
		session_id($id);
		setcookie(session_name(), $id, ini_get('session.cookie_lifetime'), "/");
	}

	private static function getSessData() {
		return self::$sql->select('getSessionData', session_id());
	}

	private static function setSessData($data) {
		self::$sql->execute('setSessionData', session_id(), $data);
		return true; //nem történhet hiba. ha az SQL lehal, akkor meg úgy sem jut el idáig
	}

	//megkéri az sql-t hogy cseréljen session id-t, ha kell és adja vissza az új session id-t, amit a php beállít majd
	private static function changeSessIdIfNeed() {
		$args = func_get_args();
		if(isset($args[0]))
			$resp = self::$sql->select('changeSessionId', session_id());
		else $resp = self::$sql->select('changeSessionIdIfNeed', session_id());
		if ($resp) {
			self::setSessId($resp);
		}
	}

	//új session id jön létre az sql-ben, php beállítja a sütit és a sessiont
	private static function setNewSess() {
		$id = self::$sql->select('addSession', self::createSessKey());
		if (!$id) die('Create Session Failed');
		self::setSessId($id);
	}

	//visszatérési értéke megmondja, hogy kell-e új sessiont csinálni
	private static function needNewSess() {
		if(self::isSessCookieSet() && self::isSessIdValid(session_id())) {
			if (self::isSessIdSet()) return false;
			self::$expired=true;
		}
		return true;
	}

	//session_destroy függvénynek, hogy lehessen session-t törölni
	private static function delSess() {
		self::$sql->execute('delSession', session_id());
		self::delSessCookie();
		//süti törlése, hogy következő oldal lekéréskor ne hivatkozzon nem létező ID-re (ne legyen lejárt session üzenet)
		return true; //ez is garantált
	}

	//ahhoz kell tudni, hogy a sütiben megadott sessId létezik-e az sql-ben, mert ha nem, új id-t kell generálnia az sql-nek
	//illetve javascriptnek xml-ben választ adni, hogy törlődött-e a session
	public static function isSessIdSet() {
		return self::$sql->select('isSessionIdValid', self::getCookieSessId(), self::createSessKey());
	}

	private static function isSessCookieSet() {
		return isset($_COOKIE[session_name()]);
	}

	private static function getCookieSessId() {
		if (self::isSessCookieSet() && self::isSessIdValid($_COOKIE[session_name()])) return $_COOKIE[session_name()];
	}

	private static function delSessCookie() {
		setcookie(session_name(), '', 0, "/");
		unset($_COOKIE[session_name()]);
	}

}

class Teszt {
  public $session, $loginError;

	public function __construct() {
		error_reporting(E_ALL);
		$this->loginError = false;
		$this->doXmlIfNeed();
		ob_start();
		$this->session = new Session();
		header ('Content-Type: text/html; charset=utf-8');
		//$this->session->setLifetime(600, 0); //példa élettartam módosításra
	}

	public function echoLogin() {
		if ($this->session->getLogin() != null) {
			echo 'Login: '.$this->session->getLogin().PHP_EOL;
		}
	}

	public function sessionCount() {
		if (!isset($_SESSION['count'])) $_SESSION['count'] = 0;
		else ++$_SESSION['count'];
		echo '$_SESSION["count"]: '.$this->toHTML($_SESSION['count']).PHP_EOL;
	}

	private function echoSubmit($value) {
		echo '<input name="action" type="submit" value="'.$value.'" />'.PHP_EOL;
	}

	private function isLoginValid() {
		return (bool)preg_match('/^[A-Za-z]{1}[A-Za-z0-9.-_]{0,13}[A-Za-z0-9]{0,1}$/', $_POST['login']);
	}

	public function echoForm() {
		$actions = array('Bejelentkezés', 'Kijelentkezés', 'Frissítés', 'Új munkamenet');
		if (isset($_POST['action']))
		switch ($_POST['action']) {
			case $actions[0]:
					if ($this->isLoginValid())
						$this->loginError = !$this->session->setLogin($_POST['login']);
				break;
			case $actions[1]:
					$this->session->delSessLogin();
				break;
			case $actions[3]:
					session_destroy();
					session_start();
		}
		echo '<table>'.PHP_EOL.'<tr>';
		if (!$this->session->getLogin()) {
			echo '<td>'.PHP_EOL;
			echo '<form id ="loginForm" method="post" action="'.$_SERVER['SCRIPT_NAME'].'">'.PHP_EOL;
			echo '<p>'.PHP_EOL;
			echo 'Login: <input id="login" name="login" type="text" />'.PHP_EOL;
			echo $this->echoSubmit($actions[0]);
			echo '</p>'.PHP_EOL;
			echo '</form>'.PHP_EOL;
			echo '</td>'.PHP_EOL;
		}
		echo '<td>'.PHP_EOL;
		echo '<form method="post" action="'.$_SERVER['SCRIPT_NAME'].'">'.PHP_EOL;
		echo '<p>'.PHP_EOL;
		if ($this->session->getLogin()) $this->echoSubmit($actions[1]);
		$this->echoSubmit($actions[2]);
		$this->echoSubmit($actions[3]);
		echo '</p>'.PHP_EOL;
		echo '</form>'.PHP_EOL;
		echo '</td>'.PHP_EOL.'</tr>'.PHP_EOL.'</table>'.PHP_EOL;
	}

	private function doXmlIfNeed() {
		if (isset($_REQUEST['xml'])) { //élesben POST lenne, de GET-tel könnyű bemutatni
			header ('Content-Type: text/xml; charset=utf-8');
			$sess = new Session(false); //így nem hajtódik végre a session_start()
			$xml = new DOMDocument('1.0', 'UTF-8');
			$root = $xml->createElement('response');
			$xml->appendChild($root);
			switch ($_REQUEST['xml']) {
				case 'lifetime':
					$root->setAttribute('lifetime', $sess->getSessLifetime());
					break;
				case 'isSessSet':
					$root->setAttribute('set', $sess->isSessIdSet() ? '1' : '0');
					break;
				case 'sessinfo':
					$root->setAttribute('guests', $sess->getGuestCount());
					$root->setAttribute('visitors', $sess->getSessCount());
					foreach($sess->getLoginArray() as $login) {
						$element = $xml->createElement('user');
						$root->appendChild($element);
						$element->setAttribute('name', $login);
						$element->setAttribute('count', $sess->getUserCount($login));
					}
			}
			echo $xml->saveXML();
			exit();
		}
	}

	public function echoJS() {
		$this->echoScript('jquery.js');
		$this->echoScript('jquery_cookie.js');
		$this->echoScript('countdown.js');
		$this->echoScript('validator.js');
		$this->echoScript('list.js');
	}

	public function getSessEndtime() {
		$date = new DateTime();
		$lifetime = $this->session->getSessLifetime();
		$date->add(new DateInterval('PT'.$lifetime.'S'));
		return $date->format("H:i:s");
	}

	private function echoScript($src) {
		echo '<script type="text/javascript" src="'.$src.'"></script>'.PHP_EOL;
	}

	public function echoUsers() {
		$users = $this->session->getLoginArray();
		if (count($users)) echo 'Bejelentkezett felhasználók:'.PHP_EOL;
		foreach($users as $user) {
			echo $this->toHTML($user).' ('.$this->session->getUserCount($user).' helyen)'.PHP_EOL;
		}
	}

	public function toHTML($arg) {
		if (is_array($arg)) {
			foreach ($arg as $index => $value) {
				$arg[$index] = htmlspecialchars($value);
			}
			return $arg;
		}
		else {
			return htmlspecialchars($arg);
		}
	}

}

?>
