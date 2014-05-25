<?php
//TODO:
// - tartalomgeneráló osztály a hosszú index.php helyett
// - kód egységesítés, pl. a menü generálás két szintje vagy a forráskódban a formázó szóközös behúzásoknak paraméter a függvénybe
// - az adott oldalnak megfelelő más nyelvű url lekérésének lehetőségét megírni a nyelvválasztóhoz és az alternate link teghez és a sitemap bővítése <xhtml:link rel="alternate" hreflang="de-ch" href="http://www.site.ch/1.html" /> tegekkel minden <loc> teg után
// - ha lesz session, a már megírt biztonságos, adatbázisos modul integrálása
    require_once 'inc/classes/page.php';
    $MANAGER = new PageManager();
    $DETAILS = $MANAGER->details();
    $ROOT_PAGES = $DETAILS->getRootPages();
    if (count($ROOT_PAGES) > 0 && strlen($_SERVER['REQUEST_URI']) <= 1) {
        header('Location: ' . array_shift(array_keys($ROOT_PAGES)));
        exit;
    }
    $DICT = $MANAGER->dictionary();
    $PAGES = $DETAILS->getPages();
    $PAGE_FILE = 'inc/pages/'.$DETAILS->lang().'/' . $_SERVER['REQUEST_URI'] . '.php';
    if (!file_exists($PAGE_FILE)) {
        header('HTTP/1.0 404 Not Found');
        $PAGE_FILE = 'inc/pages/'.$DETAILS->lang().'/404.php';
    }
    else {
        header('Content-Type: text/html; charset=utf-8');
    }
    $URL_VARS = explode('/', $_SERVER['REQUEST_URI']);
    $URL = $_SERVER['REQUEST_URI'];
    $URL_ROOT = '/' . $URL_VARS[1];
    $URL_ROOT2 = '/' . $URL_VARS[1] . '/' . $URL_VARS[2];
    $HAS_ATTR = array_key_exists($URL, $PAGES);
?>
<!DOCTYPE html>
<html lang="<?php echo $DETAILS->lang() ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <?php
        $path = '';
        $title = $DETAILS->getProperty('title');
        foreach ($URL_VARS as $var) {
            if (!$var) continue;
            $path .= '/' . $var;
            if (isset($PAGES[$path]) && isset($PAGES[$path]['name']) && trim($PAGES[$path]['name'])) {
                $title = $PAGES[$path]['name'] . ($title ? ' - ' . $title : '');
            }
        }
        echo '<title>' . $title . '</title>' . PHP_EOL;
        if ($HAS_ATTR) {
            if (isset($PAGES[$URL]['description']) && trim($PAGES[$URL]['description'])) echo '    <meta name="description" content="' . $PAGES[$URL]['description'] . '">' . PHP_EOL;
            if (isset($PAGES[$URL]['keywords']) && trim($PAGES[$URL]['keywords'])) echo '    <meta name="keywords" content="' . $PAGES[$URL]['keywords'] . '">' . PHP_EOL;
        }
        $PAGE_INDEX = array_search($_SERVER['REQUEST_URI'], array_keys($PAGES));
        $EN_DETAILS = new PageDetails($MANAGER, $DETAILS->lang() == 'hu' ? 'en' : 'hu');
        $EN_PAGES = $EN_DETAILS->getPages();
        $EN_KEYS = array_keys($EN_PAGES);
        $EN_URL = $EN_KEYS[$PAGE_INDEX];
    ?>
    <link rel="stylesheet" href="/site-style.css">
    <link rel="author" href="https://plus.google.com/112312243351248835348" />
    <link rel="alternate" hreflang="<?php echo $DETAILS->lang() == 'hu' ? 'en' : 'hu' ?>" href="<?php echo 'http://'.($DETAILS->lang() == 'hu' ? 'hugihairsalon.no-ip.biz' . $EN_URL : 'hugifodraszat.no-ip.biz' . $EN_URL) ?>" />
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        ga('create', 'UA-46691431-1', '<?php echo $_SERVER['HTTP_HOST'] ?>');
        ga('send', 'pageview');
    </script>
</head>
<body>
    <div id="shadow_pos"><div id="shadow_bg"></div></div>
    <div id="container">
        <div id="header">
            <div id="header_title"><?php echo $DICT->val('header_text'); ?></div>
            <ul id="mainmenu">
                <?php
                    $menu = '';
                    foreach ($ROOT_PAGES as $url => $attrs) {
                        if (!$attrs['visible']) continue;
                        $menu .= '<li' . ($URL_ROOT == $url ? ' class="currentmenu"' : '') . '>';
                        $menu .= PHP_EOL . '    ';
                        $menu .= '<a href="' . ($attrs['empty'] ? '#' : $url) . '">' . $attrs['name'] . '</a>';
                        $childPages = $DETAILS->getChildPages($url);
                        if (count($childPages) > 0) {
                            $menu .= PHP_EOL . '    ';
                            $menu .= '<ul class="submenu">' . PHP_EOL;
                            foreach ($childPages as $childUrl => $childAttrs) {
                                if (!$childAttrs['visible']) continue;
                                $menu .= '        <li' . ($URL_ROOT2 == $childUrl ? ' class="currentmenu"' : '') . '>';
                                $menu .= PHP_EOL . '    ';
                                $menu .= '        <a href="' . ($childAttrs['empty'] ? '#' : $childUrl) . '">' . $childAttrs['name'] . '</a>'.PHP_EOL;
                                $menu .= '        </li>'.PHP_EOL;
                            }
                            $menu .= '    </ul>';
                        }
                        $menu .= PHP_EOL;
                        $menu .= '</li>' . PHP_EOL;
                    }
                    echo trim(str_replace(PHP_EOL, PHP_EOL . '                ', $menu)) . PHP_EOL;
                ?>
            </ul>
        </div>
        <div id="content">
            <?php echo trim(preg_replace('~[\r\n]+~', PHP_EOL . '            ', file_get_contents($PAGE_FILE))) . PHP_EOL; ?>
        </div>
        <div id="footer">
            <ul id="lng_chooser">
                <li><a href="http://hugifodraszat.no-ip.biz<?php echo $DETAILS->lang() == 'en' ? $EN_URL : $URL ?>">Magyar</a></li>
                <li><a href="http://hugihairsalon.no-ip.biz<?php echo $DETAILS->lang() == 'hu' ? $EN_URL : $URL ?>">English</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
