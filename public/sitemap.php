<?php
require dirname(__DIR__).'/src/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach(['','projects/','insights/'] as $p)echo '<url><loc>https://vitaw.co.zw/'.$p.'</loc></url>';
try {foreach(db()->query("SELECT kind,slug,updated_at FROM entries WHERE published=1 AND kind IN ('projects','insights')") as $e)echo '<url><loc>https://vitaw.co.zw/'.esc($e['kind']).'/'.esc($e['slug']).'/</loc><lastmod>'.esc($e['updated_at']).'</lastmod></url>';}catch(Throwable $e){}
echo '</urlset>';
