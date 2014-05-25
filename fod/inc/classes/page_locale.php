<?php

class PageLocale extends ManagerDemander {
    
    private $host, $host_vars, $def;
    
    private $locales = null, $locale = null;
    
    public function __construct($manager) {
        parent::__construct($manager);
        $this->host = strtolower($_SERVER['HTTP_HOST']);
        $this->host_vars = explode('.', $this->host);
        $this->def = PageManager::loadConfig('lng', array('lng_code', 'lng_name', 'loc_code', 'loc_name', 'domain'));
    }
    
    public function getLanguage() {
        return $this->getLocale()->lngCode;
    }
    
    public function getLocation() {
        return $this->getLocale()->locCode;
    }
    
    public function getLocale() {
        if ($this->locale != null) return $this->locale;
        $locales = $this->getLocales();
        foreach ($locales as $code => $locale) {
            if (in_array($this->host, $locale->domains)) {
                return $locale;
            }
            if ($this->host_vars[0] == $code) {
                return $locale;
            }
        }
        $prefLngs = self::getPreferedLanguages();
        foreach ($prefLngs as $code => $priority) {
            $key = strtolower($code);
            if (array_key_exists($key, $locales)) {
                return $locales[$key];
            }
        }
        $locale_keys = array_keys($locales);
        return $this->locale = $locales[$locale_keys[0]];
    }
    
    public function getLocales() {
        if ($this->locales != null) return $this->locales;
        $locales = array();
        foreach ($this->dbh()->query('SELECT * FROM locale_info ORDER BY code') as $row) {
            $code = strtolower($row['code']);
            if (!array_key_exists($code, $locales)) {
                $locales[$code] = self::createLocale($row['lng_code'], $row['loc_code'], $row['lng_name'], $row['loc_name']);
            }
            $domain = strtolower($row['domain']);
            if ($domain) $locales[$code]->domains[] = $domain;
        }
        if (count($locales) == 0) {
            $locales[] = self::createLocale($this->def['lng_code'], $this->def['loc_code'], $this->def['lng_name'], $this->def['loc_name']);
        }
        return $this->locales = $locales;
    }
    
    private static function createLocale($lngCode, $locCode, $lngName, $locName) {
        $locale = new stdClass();
        $locale->lngCode = strtolower($lngCode);
        $locale->locCode = strtolower($locCode);
        $locale->lngName = $lngName;
        $locale->locName = $locName;
        $locale->domains = array();
        return $locale;
    }
    
    private static function getPreferedLanguages() {
        $langs = array();
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // a string feldarabolása (nyelvek és q faktorok)
            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
            if (count($lang_parse[1])) {
                // lista készítése (pl. "en" => 0.8)
                $langs = array_combine($lang_parse[1], $lang_parse[4]);

                // q faktor nélküliek alapértelmezett értéke 1
                foreach ($langs as $lang => $val) {
                    if ($val === '') $langs[$lang] = 1;
                }

                // szám szerinti rendezés
                arsort($langs, SORT_NUMERIC);
            }
        }
        return $langs;
    }
    
}

?>