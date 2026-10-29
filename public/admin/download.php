<?php
require dirname(__DIR__,2).'/src/bootstrap.php';admin();
$s=db()->prepare('SELECT attachment FROM inquiries WHERE id=?');$s->execute([(int)($_GET['id']??0)]);$name=$s->fetchColumn();
if(!$name||!preg_match('/^[a-f0-9]{40}\.(pdf|jpg|png)$/',$name)){http_response_code(404);exit;}
$file=config()['storage'].'/inquiries/'.$name;if(!is_file($file)){http_response_code(404);exit;}
header('Cache-Control: no-store');header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="project-document.'.pathinfo($name,PATHINFO_EXTENSION).'"');readfile($file);
