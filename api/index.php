<?php
declare(strict_types=1);

/**
 * Front-controller para Vercel (vercel-php).
 * Em XAMPP local as páginas são servidas directamente — este ficheiro não é necessário.
 */

$root = dirname(__DIR__);
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uriPath = is_string($uriPath) ? rawurldecode($uriPath) : '/';
$uriPath = '/' . trim($uriPath, '/');
if ($uriPath === '/') {
    $uriPath = '/';
}

$relative = $uriPath === '/' ? 'index.php' : ltrim($uriPath, '/');

// Índices de pasta
$candidate = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
if (is_dir($candidate)) {
    $relative = rtrim($relative, '/') . '/index.php';
    $candidate = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
} elseif (!str_ends_with(strtolower($relative), '.php') && is_file($candidate . '.php')) {
    $relative .= '.php';
    $candidate = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

$blockedPrefixes = ['config/', 'sql/', 'includes/', 'vendor/', '.git/', '.cursor/'];
$blockedFiles = ['.env', 'composer.json', 'composer.lock', 'vercel.json', '.vercelignore', '.gitignore'];
$lowerRel = strtolower($relative);

foreach ($blockedPrefixes as $prefix) {
    if (str_starts_with($lowerRel, $prefix)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Forbidden';
        exit;
    }
}

$baseName = basename($relative);
if (in_array($baseName, $blockedFiles, true) || str_ends_with($lowerRel, '.sql') || str_ends_with($lowerRel, '.md')) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Forbidden';
    exit;
}

if (!str_ends_with(strtolower($relative), '.php')) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not Found';
    exit;
}

$target = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
$realRoot = realpath($root);
$realTarget = realpath($target);

if ($realRoot === false || $realTarget === false || !is_file($realTarget)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not Found';
    exit;
}

if (!str_starts_with($realTarget, $realRoot) || $realTarget === realpath(__FILE__)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Forbidden';
    exit;
}

$_SERVER['SCRIPT_FILENAME'] = $realTarget;
$_SERVER['SCRIPT_NAME'] = '/' . str_replace('\\', '/', $relative);
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];

require $realTarget;
