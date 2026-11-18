<?php
declare(strict_types=1);
ini_set('display_errors', '0');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
function config(): array {
    static $config;
    if ($config !== null) return $config;
    $path = dirname(__DIR__) . '/config.php';
    if (!is_file($path)) throw new RuntimeException('Site setup is pending.');
    return $config = require $path;
}
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $c = config();
        $pdo = new PDO($c['dsn'], $c['db_user'] ?? null, $c['db_password'] ?? null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        ensureFeatureTables($pdo);
    }
    return $pdo;
}
function ensureFeatureTables(PDO $pdo): void {
    static $done=false;if($done)return;$done=true;
    $driver=$pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $id=$driver==='sqlite'?'INTEGER PRIMARY KEY AUTOINCREMENT':'INTEGER PRIMARY KEY AUTO_INCREMENT';
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_requests (id $id,name VARCHAR(120) NOT NULL,company VARCHAR(160) NOT NULL,email VARCHAR(254) NOT NULL,phone VARCHAR(40) NOT NULL,website_type VARCHAR(80) NOT NULL,budget VARCHAR(80) NOT NULL,timeline VARCHAR(80) NOT NULL,features TEXT NOT NULL,reference_url VARCHAR(500) NOT NULL,status VARCHAR(30) NOT NULL DEFAULT 'New',created_at VARCHAR(30) NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (id $id,name VARCHAR(120) NOT NULL,company VARCHAR(160) NOT NULL,reviewer_role VARCHAR(120) NOT NULL,rating INTEGER NOT NULL,review TEXT NOT NULL,status VARCHAR(30) NOT NULL DEFAULT 'Pending',created_at VARCHAR(30) NOT NULL,updated_at VARCHAR(30) NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS partners (id $id,name VARCHAR(180) NOT NULL,url VARCHAR(500) NOT NULL,description TEXT NOT NULL,logo_url VARCHAR(500) NOT NULL,featured INTEGER NOT NULL DEFAULT 0,visible INTEGER NOT NULL DEFAULT 0,sort_order INTEGER NOT NULL DEFAULT 0,created_at VARCHAR(30) NOT NULL,updated_at VARCHAR(30) NOT NULL)");
    $q=$pdo->prepare('SELECT COUNT(*) FROM partners WHERE name=?');$q->execute(['Cicosy Private Limited']);
    if(!(int)$q->fetchColumn())$pdo->prepare('INSERT INTO partners(name,url,description,logo_url,featured,visible,sort_order,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?)')->execute(['Cicosy Private Limited','https://cicosy.com/','Our principal technology partner, collaborating with Vitaw Technologies on ambitious digital solutions.','',1,1,10,gmdate('c'),gmdate('c')]);
}
function sessionStart(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['httponly'=>true, 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite'=>'Strict', 'path'=>'/']);
    session_start();
    if (isset($_SESSION['last_seen']) && time() - $_SESSION['last_seen'] > 1800) { $_SESSION = []; session_regenerate_id(true); }
    $_SESSION['last_seen'] = time();
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
function esc(mixed $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf(): void {
    sessionStart();
    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) { http_response_code(403); exit('The session expired. Reload and try again.'); }
}
function admin(): void { sessionStart(); if (empty($_SESSION['admin'])) { header('Location: /admin/'); exit; } }
function jsonResponse(array $data, int $code = 200): never { http_response_code($code); header('Content-Type: application/json'); echo json_encode($data); exit; }
function rateLimit(string $key, int $limit, int $window): bool {
    $bucket = hash('sha256', $key . ':' . (string)floor(time()/$window));
    $pdo=db();
    $sql=$pdo->getAttribute(PDO::ATTR_DRIVER_NAME)==='sqlite'
        ? 'INSERT INTO rate_limits (bucket,hits,expires) VALUES (?,1,?) ON CONFLICT(bucket) DO UPDATE SET hits=hits+1'
        : 'INSERT INTO rate_limits (bucket,hits,expires) VALUES (?,1,?) ON DUPLICATE KEY UPDATE hits=hits+1';
    $pdo->prepare($sql)->execute([$bucket,time()+$window]);
    $s=$pdo->prepare('SELECT hits FROM rate_limits WHERE bucket=?'); $s->execute([$bucket]);
    $allowed=(int)$s->fetchColumn()<=$limit;
    $pdo->prepare('DELETE FROM rate_limits WHERE expires < ?')->execute([time()]);
    return $allowed;
}
