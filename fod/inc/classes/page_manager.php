<?php

class PageManager implements DatabaseDemander {
    
    private $dbh, $locale, $details, $dictionary;
    
    /**
     * Connects to the database and creates the page objects.
     */
    public function __construct() {
        $this->dbh = self::mysqlConnect();
        $this->locale = new PageLocale($this);
        $this->details = new PageDetails($this);
        $this->dictionary = new PageDictionary($this);
    }
    
    /**
     * @return PageLocale The used locale that contains the language and the country
     */
    public function locale() {
        return $this->locale;
    }
    
    /**
     * @return PageDetails The page details from the database
     */
    public function details() {
        return $this->details;
    }
    
    /**
     * @return PageDictionary A dictionary object that uses the database
     */
    public function dictionary() {
        return $this->dictionary;
    }
    
    /**
     * @return PDO The database connection
     */
    public function dbh() {
        return $this->dbh;
    }
    
    public static function loadConfig($name, array $keys) {
        require PageManager::getConfigLocation($name);
        $cfg = array();
        foreach ($keys as $key) {
            $attr = $$key;
            if (!$attr) die('Variable $' . $key . ' is missing from file ' . $name . '.conf.php!');
            $cfg[$key] = $attr;
        }
        return $cfg;
    }
    
    private static function mysqlConnect() {
        require_once self::getConfigLocation('pdo');
        try {
            return new PDO($pdo_dsn, $pdo_user, $pdo_password, array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => $pdo_init_cmd, \PDO::ATTR_PERSISTENT => false, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ));
        }
        catch (PDOException $ex) {
            die($ex->getMessage());
        }
    }
    
    private static function getConfigLocation($name) {
        return $_SERVER['DOCUMENT_ROOT'] . '/inc/'.$name.'.conf.php';
    }
    
}

abstract class ManagerDemander implements DatabaseDemander {
    
    private $manager;
    
    public function __construct($manager) {
        if ($manager == null) die(get_called_class() . ' needs a PageManager object');
        $this->manager = $manager;
    }
    
    /**
     * @return PDO The database connection
     */
    public function dbh() {
        return $this->manager()->dbh();
    }
    
    /**
     * @return string The language code
     */
    public function lang() {
        return $this->manager()->locale()->getLanguage();
    }
    
    /**
     * @return PageManager The manager who created this object
     */
    protected function manager() {
        return $this->manager;
    }
    
}

interface DatabaseDemander {
    public function dbh();
}

?>