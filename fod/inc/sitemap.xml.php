<?php
define('MAX_PR', 100);
define('MIN_PR', 50);
require_once 'classes/page.php';
$root = 'http://' . $_SERVER['HTTP_HOST'];
$manager = new PageManager();
$details = $manager->details();
$pages = $details->getPages();
$pages['/sitemap.xml'] = array();
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="'.$root.'/sitemap.xsl"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;
$depth = 1;
foreach ($pages as $url => $attrs) {
    $d = $details->getPageLevel($url) + 1;
    if ($d > $depth) $depth = $d;
}
foreach ($pages as $url => $attrs) {
    $pageFile = 'pages/'.$details->lang() . $url . '.php';
    // a priorításokra példa:
    // lv   %     számítás
    // 0 -> 100 = 100
    // 1 -> 75  = 100 - ((100 - 50) / (3 − 1))
    // 2 -> 50  = 100 - ((100 - 50) / (3 − 2))
    // a képlet tehát: MAX_PR - ((MAX_PR - MIN_PR) / ($depth−X)) AHOL X > 0 ÉS X < $depth
    $level = $details->getPageLevel($url);
    $priority = ($level > 0 ? MAX_PR - ((MAX_PR - MIN_PR) / ($depth - $level)) : MAX_PR) / 100.0;
    echo '  <url>
    <loc>'.$root.$url.'</loc>
    <lastmod>'.date('Y-m-d', file_exists($pageFile) ? filemtime($pageFile) : time()).'</lastmod>
    <changefreq>daily</changefreq>
    <priority>'.str_replace(',', '.', $priority).'</priority>
  </url>' . PHP_EOL;
}
echo '</urlset>' . PHP_EOL;
?>
