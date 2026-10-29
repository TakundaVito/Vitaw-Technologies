<?php
require dirname(__DIR__,2).'/src/bootstrap.php';
header('Cache-Control: no-store');
try {
    $s=db()->query('SELECT id,kind,title,slug,category,summary,metadata,created_at,updated_at FROM entries WHERE published=1 ORDER BY created_at DESC');
    $entries=$s->fetchAll();foreach($entries as &$e) $e['metadata']=array_intersect_key(json_decode($e['metadata'],true)??[],array_flip(['featuredImage','featured','industry','author']));
    $publicSettings=['heroHeadline','heroDescription','email','phone','location','projectsDelivered','technologies','disciplines','clients'];
    $settings=[];foreach(db()->query('SELECT name,value FROM settings')->fetchAll() as $row) if(in_array($row['name'],$publicSettings,true)) $settings[$row['name']]=$row['value'];
    jsonResponse(['entries'=>$entries,'settings'=>$settings]);
}catch(Throwable $e){ jsonResponse(['entries'=>[],'settings'=>[],'setupPending'=>true]); }
