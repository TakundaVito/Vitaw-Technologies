<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/src/bootstrap.php';
header('Cache-Control: no-store');
try{
 if($_SERVER['REQUEST_METHOD']==='GET'){
  $rows=db()->query("SELECT id,name,company,reviewer_role AS role,rating,review,created_at FROM reviews WHERE status='Approved' ORDER BY updated_at DESC LIMIT 24")->fetchAll();jsonResponse(['reviews'=>$rows]);
 }
 if($_SERVER['REQUEST_METHOD']!=='POST')jsonResponse(['error'=>'Method not allowed.'],405);
 if(!empty($_SERVER['HTTP_ORIGIN'])&&parse_url($_SERVER['HTTP_ORIGIN'],PHP_URL_HOST)!==explode(':',$_SERVER['HTTP_HOST'])[0])jsonResponse(['error'=>'Invalid request origin.'],403);
 if(!rateLimit('review:'.($_SERVER['REMOTE_ADDR']??''),3,86400))jsonResponse(['error'=>'Too many review submissions. Please try again tomorrow.'],429);
 $name=trim((string)($_POST['name']??''));$company=trim((string)($_POST['company']??''));$role=trim((string)($_POST['role']??''));$review=trim((string)($_POST['review']??''));$rating=(int)($_POST['rating']??0);
 if(!$name||strlen($name)>120||strlen($company)>160||strlen($role)>120||strlen($review)<20||strlen($review)>2000||$rating<1||$rating>5)jsonResponse(['error'=>'Complete your name, rating and a review of at least 20 characters.'],422);
 db()->prepare('INSERT INTO reviews(name,company,reviewer_role,rating,review,status,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?)')->execute([$name,$company,$role,$rating,$review,'Pending',gmdate('c'),gmdate('c')]);
 jsonResponse(['ok'=>true,'message'=>'Thank you. Your review will appear after approval.'],201);
}catch(Throwable $e){error_log('Vitaw reviews: '.$e->getMessage());jsonResponse(['error'=>'The review service is temporarily unavailable.'],503);}
