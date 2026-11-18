<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/src/bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST')jsonResponse(['error'=>'Method not allowed.'],405);
try{
 if(!empty($_SERVER['HTTP_ORIGIN'])&&parse_url($_SERVER['HTTP_ORIGIN'],PHP_URL_HOST)!==explode(':',$_SERVER['HTTP_HOST'])[0])jsonResponse(['error'=>'Invalid request origin.'],403);
 if(!rateLimit('site-request:'.($_SERVER['REMOTE_ADDR']??''),4,3600))jsonResponse(['error'=>'Too many requests. Please try again later.'],429);
 $limits=['name'=>120,'company'=>160,'email'=>254,'phone'=>40,'website_type'=>80,'budget'=>80,'timeline'=>80,'features'=>8000,'reference_url'=>500];$d=[];
 foreach($limits as $key=>$max){$value=trim((string)($_POST[$key]??''));if(strlen($value)>$max)jsonResponse(['error'=>'A field exceeds its maximum length.'],422);$d[$key]=$value;}
 if(!$d['name']||!filter_var($d['email'],FILTER_VALIDATE_EMAIL)||strlen($d['features'])<20)jsonResponse(['error'=>'Enter your name, a valid email and at least 20 characters about the website.'],422);
 if($d['reference_url']&&!filter_var($d['reference_url'],FILTER_VALIDATE_URL))jsonResponse(['error'=>'Enter a valid reference website URL, including https://.'],422);
 db()->prepare('INSERT INTO site_requests(name,company,email,phone,website_type,budget,timeline,features,reference_url,status,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)')->execute([$d['name'],$d['company'],$d['email'],$d['phone'],$d['website_type'],$d['budget'],$d['timeline'],$d['features'],$d['reference_url'],'New',gmdate('c')]);
 jsonResponse(['ok'=>true,'message'=>'Your website request has been received.'],201);
}catch(Throwable $e){error_log('Vitaw site request: '.$e->getMessage());jsonResponse(['error'=>'The website request service is temporarily unavailable.'],503);}
