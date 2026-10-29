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
    }
    return $pdo;
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
