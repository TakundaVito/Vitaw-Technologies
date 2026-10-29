<?php
$p=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);$root=dirname(__DIR__).'/public';
if($p==='/'||is_file($root.$p))return false;
if($p==='/admin'||$p==='/admin/'){require $root.'/admin/index.php';return;}
if(preg_match('~^/(projects|insights)/([a-z0-9-]+)/?$~',$p,$m)){$_GET['slug']=$m[2];require $root.'/entry.php';return;}
if(preg_match('~^/(projects|insights)/?$~',$p,$m)){$_GET['kind']=$m[1];require $root.'/listing.php';return;}
http_response_code(404);readfile($root.'/404.html');
