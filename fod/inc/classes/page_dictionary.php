<?php

class PageDictionary extends ManagerDemander {
    
    private $words = array();
    
    function valnl($key, $def = false) {
        $val = $this->val($key, $def);
        if (!$val) return $val;
        return $val . PHP_EOL;
    }
    
    function valsnl($key1, $key2, $text, $text_key = false) {
        $val = $this->vals($key1, $key2, $text, $text_key);
        if (!$val) return $val;
        return $val . PHP_EOL;
    }
    
    function val($key, $def = false) {
        if (array_key_exists($key, $this->words)) return $this->words[$key];
        $sth = $this->dbh()->prepare('SELECT text FROM dictionary WHERE `key` = :key AND lang = :lang');
        $sth->bindParam('lang', $this->lang(), PDO::PARAM_STR, 2);
        $sth->bindParam('key', $key, PDO::PARAM_STR);
        $sth->execute();
        $text = $sth->fetch(PDO::FETCH_COLUMN);
        if (!$text) $text = $def;
        return $this->words[$key] = $text;
    }
    
    function vals($key1, $key2, $text, $text_key = false) {
        if ($text_key) $text = $this->val($text);
        $text1 = $this->val($key1);
        $text2 = $this->val($key2);
        $val = '';
        if ($text1) $val .= $text1 . ' ';
        $val .= $text;
        if ($text2) $val .= ' ' . $text2;
        return $val;
    }
    
}

?>