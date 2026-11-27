<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/src/bootstrap.php';
header('Cache-Control: no-store');
function paymentHash(array $fields,string $key): string {
    $text='';foreach($fields as $name=>$value){if(strtolower((string)$name)!=='hash'){if(!is_string($value))throw new RuntimeException('Invalid payment response');$text.=$value;}}
    return strtoupper(hash('sha512',$text.$key));
}
function paymentUrl(string $url): bool {
    $p=parse_url($url);return ($p['scheme']??'')==='https'&&strtolower($p['host']??'')==='www.paynow.co.zw'&&!isset($p['user'])&&!isset($p['pass'])&&(!isset($p['port'])||$p['port']===443);
}
function paymentPost(string $url,array $fields,string $key): array {
    if(!paymentUrl($url))throw new RuntimeException('Invalid gateway URL');
    $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($fields),CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>25,CURLOPT_FOLLOWLOCATION=>false]);
    $raw=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
    if($code!==200||!is_string($raw))throw new RuntimeException('Gateway unavailable');
    parse_str($raw,$reply);$reply=array_change_key_case($reply,CASE_LOWER);
    if(!is_string($reply['hash']??null)||!hash_equals(paymentHash($reply,$key),strtoupper($reply['hash'])))throw new RuntimeException('Invalid gateway signature');
    return $reply;
}
function paymentConfirm(PDO $pdo,array $row,string $key): array {
    $reply=paymentPost($row['poll_url'],[],$key);
    if(($reply['reference']??'')!==$row['reference']||!is_numeric($reply['amount']??null)||number_format((float)$reply['amount'],2,'.','')!==$row['amount'])throw new RuntimeException('Payment details mismatch');
    $state=strtolower($reply['status']??'');
    $pdo->prepare('UPDATE service_payments SET status=?,gateway_reference=? WHERE reference=?')->execute([$state,(string)($reply['paynowreference']??''),$row['reference']]);
    return ['reference'=>$row['reference'],'amount'=>$row['amount'],'service'=>$row['service'],'status'=>$state,'paid'=>in_array($state,['paid','awaiting delivery','delivered'],true)];
}
try{
    $file=dirname(__DIR__,2).'/paynow.php';$settings=is_file($file)?require $file:[];
    $ready=!empty($settings['integration_id'])&&!empty($settings['integration_key'])&&($settings['currency']??'')==='USD';
    $action=(string)($_GET['action']??'');
    if($_SERVER['REQUEST_METHOD']==='GET'){
        sessionStart();jsonResponse(['available'=>$ready,'csrf'=>$_SESSION['csrf']]);
    }
    if($_SERVER['REQUEST_METHOD']!=='POST')jsonResponse(['error'=>'Method not allowed.'],405);
    if(!$ready)jsonResponse(['error'=>'Online checkout is being connected. Please contact info@vitaw.co.zw to arrange payment.'],503);
    $pdo=db();$pdo->exec("CREATE TABLE IF NOT EXISTS service_payments (reference VARCHAR(64) PRIMARY KEY,service VARCHAR(100) NOT NULL,amount VARCHAR(20) NOT NULL,email VARCHAR(254) NOT NULL,project_reference VARCHAR(120) NOT NULL,poll_url VARCHAR(1000) NOT NULL,status VARCHAR(40) NOT NULL,gateway_reference VARCHAR(64) NOT NULL,created_at VARCHAR(30) NOT NULL)");
    if($action==='notify'){
        $fields=$_POST;
        if(!is_string($fields['hash']??null)||!hash_equals(paymentHash($fields,$settings['integration_key']),strtoupper($fields['hash'])))jsonResponse(['error'=>'Invalid signature.'],403);
        $q=$pdo->prepare('SELECT * FROM service_payments WHERE reference=?');$q->execute([(string)($fields['reference']??'')]);$row=$q->fetch();
        if(!$row||!$row['poll_url'])jsonResponse(['error'=>'Payment not ready.'],409);
        paymentConfirm($pdo,$row,$settings['integration_key']);jsonResponse(['ok'=>true]);
    }
    sessionStart();
    if(!hash_equals($_SESSION['csrf'],(string)($_SERVER['HTTP_X_CSRF_TOKEN']??'')))jsonResponse(['error'=>'Reload the page and try again.'],403);
    $input=json_decode(file_get_contents('php://input'),true)??[];
    if(($input['action']??'')==='status'){
        $ref=(string)($input['reference']??'');
        if(!preg_match('/^VT-[a-f0-9]{32}$/D',$ref))jsonResponse(['error'=>'Invalid payment reference.'],422);
        if(!rateLimit('payment-status:'.($_SERVER['REMOTE_ADDR']??''),30,3600))jsonResponse(['error'=>'Please check again later.'],429);
        $q=$pdo->prepare('SELECT * FROM service_payments WHERE reference=?');$q->execute([$ref]);$row=$q->fetch();
        if(!$row||!$row['poll_url'])jsonResponse(['error'=>'Payment not found. Contact Vitaw with your reference.'],404);
        jsonResponse(paymentConfirm($pdo,$row,$settings['integration_key']));
    }
    if(($input['action']??'')!=='create')jsonResponse(['error'=>'Unknown request.'],400);
    if(!rateLimit('payment-create:'.($_SERVER['REMOTE_ADDR']??''),10,3600))jsonResponse(['error'=>'Please try again later.'],429);
    $services=['web'=>'Website development','school'=>'School management system','app'=>'Mobile app development','ai'=>'AI & business automation','iot'=>'IoT & electronics','engineering'=>'Engineering design','other'=>'Other agreed service'];
    $service=$services[$input['service']??'']??null;$amount=(string)($input['amount']??'');$email=trim((string)($input['email']??''));$project=trim((string)($input['reference']??''));
    if(!$service||!preg_match('/^\d{1,5}(\.\d{1,2})?$/D',$amount)||(float)$amount<1||(float)$amount>10000||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>254||strlen($project)>120)jsonResponse(['error'=>'Choose a service, enter a valid email and an amount from $1 to $10,000.'],422);
    $amount=number_format((float)$amount,2,'.','');$ref='VT-'.bin2hex(random_bytes(16));
    $pdo->prepare('INSERT INTO service_payments(reference,service,amount,email,project_reference,poll_url,status,gateway_reference,created_at) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$ref,$service,$amount,$email,$project,'','created','',gmdate('c')]);
    $fields=['id'=>(string)$settings['integration_id'],'reference'=>$ref,'amount'=>$amount,'additionalinfo'=>'Vitaw Technologies - '.$service,'returnurl'=>'https://vitaw.co.zw/pay/?reference='.$ref,'resulturl'=>'https://vitaw.co.zw/api/payments.php?action=notify','authemail'=>$email,'status'=>'Message'];
    $fields['hash']=paymentHash($fields,$settings['integration_key']);
    $reply=paymentPost('https://www.paynow.co.zw/interface/initiatetransaction',$fields,$settings['integration_key']);
    if(strtolower($reply['status']??'')!=='ok'||!paymentUrl($reply['browserurl']??'')||!paymentUrl($reply['pollurl']??''))throw new RuntimeException('Gateway did not accept payment');
    $pdo->prepare("UPDATE service_payments SET poll_url=?,status='pending' WHERE reference=?")->execute([$reply['pollurl'],$ref]);
    jsonResponse(['url'=>$reply['browserurl'],'reference'=>$ref]);
}catch(Throwable $e){error_log('Vitaw Paynow: '.$e->getMessage());jsonResponse(['error'=>'Payment could not be confirmed. If you already approved payment, contact info@vitaw.co.zw before paying again.'],503);}
