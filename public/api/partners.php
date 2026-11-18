<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/src/bootstrap.php';
header('Cache-Control: public, max-age=300');
try{$rows=db()->query('SELECT id,name,url,description,logo_url,featured FROM partners WHERE visible=1 ORDER BY featured DESC,sort_order DESC,name ASC')->fetchAll();jsonResponse(['partners'=>$rows]);}
catch(Throwable $e){jsonResponse(['partners'=>[]]);}
