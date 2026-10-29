<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/src/bootstrap.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') jsonResponse(['error'=>'Method not allowed.'],405);
try {
    // Browser requests must originate on the same host. Server-side clients are rate limited.
    if (!empty($_SERVER['HTTP_ORIGIN'])) {
        $origin=parse_url($_SERVER['HTTP_ORIGIN']);
        if (($origin['host'] ?? '')!==explode(':',$_SERVER['HTTP_HOST'])[0]) jsonResponse(['error'=>'Invalid request origin.'],403);
    }
    if ((int)($_SERVER['CONTENT_LENGTH']??0)>6*1024*1024) jsonResponse(['error'=>'The upload is too large.'],413);
    if (!rateLimit('inquiry:'.($_SERVER['REMOTE_ADDR']??''),5,3600)) jsonResponse(['error'=>'Too many inquiries. Please try later or use WhatsApp.'],429);
    $fields=['name'=>120,'company'=>160,'email'=>254,'phone'=>40,'type'=>80,'budget'=>80,'description'=>10000];$data=[];
    foreach ($fields as $k=>$max) { $v=$_POST[$k]??''; if(!is_string($v)||strlen($v)>$max) jsonResponse(['error'=>'A field exceeds its maximum length.'],422); $data[$k]=trim($v); }
    if (!$data['name']||!filter_var($data['email'],FILTER_VALIDATE_EMAIL)||strlen($data['description'])<20) jsonResponse(['error'=>'Enter your name, valid email and at least 20 characters describing the project.'],422);
    if (!in_array($data['type'],['Software','Website','Mobile Application','AI','Automation','IoT','Engineering Design','CAD','Consulting','Other'],true)) jsonResponse(['error'=>'Select a valid project type.'],422);
    $attachment='';
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error']!==UPLOAD_ERR_NO_FILE) {
        $f=$_FILES['attachment'];
        if ($f['error']!==UPLOAD_ERR_OK||$f['size']>5*1024*1024) jsonResponse(['error'=>'Attachment must be smaller than 5 MB.'],422);
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);$types=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png'];
        if (!isset($types[$mime])) jsonResponse(['error'=>'Only PDF, JPG and PNG attachments are accepted.'],422);
        $dir=config()['storage'].'/inquiries'; if(!is_dir($dir)) mkdir($dir,0700,true);
        $attachment=bin2hex(random_bytes(20)).'.'.$types[$mime];
        if (!move_uploaded_file($f['tmp_name'],$dir.'/'.$attachment)) throw new RuntimeException('Upload storage failed');
    }
    try { db()->prepare('INSERT INTO inquiries (name,company,email,phone,project_type,budget,description,attachment,status,created_at) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute([$data['name'],$data['company'],$data['email'],$data['phone'],$data['type'],$data['budget'],$data['description'],$attachment,'New',gmdate('c')]); }
    catch(Throwable $e) { if($attachment) unlink(config()['storage'].'/inquiries/'.$attachment); throw $e; }
    jsonResponse(['ok'=>true],201);
} catch(Throwable $e) { error_log('Vitaw inquiry error: '.$e->getMessage()); jsonResponse(['error'=>'The inquiry service is temporarily unavailable.'],503); }
