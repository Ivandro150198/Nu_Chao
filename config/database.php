<?php
declare(strict_types=1);

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'nu_chao');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Nu Chao');
define('APP_CURRENCY', 'XOF');
/** Número WhatsApp da loja (com código do país, sem + ou espaços). Ex: Guiné-Bissau = 245... */
define('APP_WHATSAPP', getenv('APP_WHATSAPP') ?: '245955000000');
define('BASE_PATH', dirname(__DIR__));

if (getenv('APP_BASE_URL') !== false) {
    define('APP_BASE_URL', rtrim((string) getenv('APP_BASE_URL'), '/'));
} elseif (getenv('VERCEL')) {
    define('APP_BASE_URL', '');
} else {
    define('APP_BASE_URL', '/No_chao');
}

require_once __DIR__ . '/google.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
