<?php
// Copy to config.php OUTSIDE public_html. Never upload this file inside public_html.
return [
    'dsn' => 'mysql:host=localhost;dbname=YOUR_DATABASE;charset=utf8mb4',
    'db_user' => 'YOUR_DATABASE_USER',
    'db_password' => 'YOUR_DATABASE_PASSWORD',
    'admin_email' => 'takundavito@gmail.com',
    // Generate with PHP password_hash. No default password is provided.
    'admin_password_hash' => 'REPLACE_WITH_PASSWORD_HASH',
    'storage' => __DIR__ . '/storage',
];
