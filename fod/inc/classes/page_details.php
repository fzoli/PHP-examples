<?php

class PageDetails extends ManagerDemander {

    private $lang;
    
    private $rootUrls = null, $props = null;

    private static $urls = array(), $pages = null;
    
    public function __construct($manager, $lang = null) {
        parent::__construct($manager);
        $this->lang = $lang;
        setlocale(LC_ALL, 'hu_HU.UTF8'); // iconv() needs the most complicated language that you use, so hungarian will be good
    }
    
    /**
     * Returns the locale's language code or the overrided value that is specified in the constructor.
     * @return string The language code
     */
    public function lang() {
        return $this->lang == null ? parent::lang() : $this->lang;
    }
    
    /**
     * @return string The requested property
     */
    public function getProperty($key, $def = false) {
        return $this->getProperties() ? $this->getProperties()->$key : $def;
    }
    
    /**
     * @return stdClass The object contains the properties of the site, like title
     */
    public function getProperties() {
        if ($this->props !== null) return $this->props;
        $sth = $this->dbh()->prepare('SELECT * FROM page_prop WHERE lang = ?');
        $sth->bindParam(1, $this->lang(), PDO::PARAM_STR, 2);
        $sth->execute();
        $props = $sth->fetch(PDO::FETCH_OBJ);
        if ($props) unset($props->lang);
        return $this->props = $props;
    }
    
    /**
     * @return array An array that contains the available pages ordered by page id
     */
    public function getPagesById() {
        if (array_key_exists($this->lang(), self::$pages)) return self::$pages[$this->lang()];
        $sth = $this->dbh()->prepare('SELECT * FROM page_lng LEFT JOIN page ON page.id = page_lng.id WHERE page_lng.lang = ? ORDER BY page.id ASC');
        $sth->bindParam(1, $this->lang(), PDO::PARAM_STR, 2);
        $sth->execute();
        $pages = array();
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $row['url'] = $this->createPageUrl($row);
            $id = $row['id'];
            unset($row['id']);
            unset($row['lang']);
            $pages[$id] = $row;
        }
        return self::$pages[$this->lang()] = $pages;
    }

    /**
     * @return array An array that contains the available pages ordered by page url
     */
    public function getPages() {
        if (array_key_exists($this->lang(), self::$urls)) return self::$urls[$this->lang()];
        $urls = array();
        $pages = $this->getPagesById();
        foreach ($pages as $attr) {
            $node = $attr;
            $url = '/' . $node['url'];
            while ($owner = self::arrayHasValue($node, 'owner')) {
                $node = $pages[$owner];
                $url = '/' . $node['url'] . $url;
            }
            $attr['level'] = self::getPageLevel($url);
            $urls[$url] = $attr;
        }
        foreach ($urls as $url => $attr) {
            unset($urls[$url]['owner']);
            unset($urls[$url]['url']);
        }
        return self::$urls[$this->lang()] = $urls;
    }
    
    public function getChildPages($pageUrl) {
        $pages = $this->getPages();
        if (isset($pages[$pageUrl])) {
            $attr = $pages[$pageUrl];
            return $this->getFilteredPages($attr['level'] + 1, $pageUrl);
        }
        return false;
    }
    
    public function getRootPages() {
        if ($this->rootUrls != null) return $this->rootUrls;
        return $this->rootUrls = $this->getFilteredPages(0);
    }
    
    private function getFilteredPages($level, $urlPrefix = null) {
        $filtered_pages = array();
        $pages = $this->getPages();
        if ($urlPrefix != null) {
            $prefixVars = explode('/', $urlPrefix);
        }
        foreach ($pages as $url => $attr) {
            if ($attr['level'] == $level) {
                $match = true;
                if ($urlPrefix != null) {
                    $urlVars = explode('/', $url);
                    for ($i = 0; $i < count($prefixVars) && $i < count($urlVars); ++$i) {
                        $match &= $prefixVars[$i] == $urlVars[$i];
                        if (!$match) break;
                    }
                }
                if ($match) {
                    $filtered_pages[$url] = $attr;
                }
            }
        }
        return $filtered_pages;
    }
    
    public static function getPageLevel($path) {
        return substr_count($path, '/', 1);
    }
    
    private static function arrayHasValue(array $arr, $key) {
        if (array_key_exists($key, $arr)) {
            return trim($arr[$key]);
        }
        return false;
    }
    
    private static function createPageUrl(array $page) {
        return self::toAscii(($url = self::arrayHasValue($page, 'url')) ? $url : $page['name'], false);
    }
    
    private static function toAscii($str, $slash = false) {
        $str = iconv('utf-8','ascii//translit', $str);
        $str = str_replace(' ', '_', $str);
        $str = preg_replace('/[^\\w-'.($slash ? '\\/' : '').']+/', '', $str);
        $str = preg_replace('(_{2,})', '_', $str);
        $str = trim($str, '_');
        return strtolower($str);
    }

}

?>