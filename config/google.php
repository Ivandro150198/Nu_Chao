<?php
declare(strict_types=1);

/**
 * Credenciais Google OAuth 2.0
 * Crie em: https://console.cloud.google.com/apis/credentials
 * URI de redireccionamento: http://localhost/No_chao/auth/google_callback.php
 *
 * Preferência: variáveis de ambiente OU ficheiro config/google.local.php (não versionado).
 */
$local = __DIR__ . '/google.local.php';
if (is_file($local)) {
    require $local;
}

if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
}
if (!defined('GOOGLE_REDIRECT_URI')) {
    define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/No_chao/auth/google_callback.php');
}

function google_configurado(): bool
{
    return GOOGLE_CLIENT_ID !== ''
        && GOOGLE_CLIENT_SECRET !== ''
        && !str_starts_with(GOOGLE_CLIENT_ID, 'COLE_AQUI')
        && !str_contains(GOOGLE_CLIENT_ID, 'n5tdch8prd6mem879msrcmvih6msg4g8');
}

function google_auth_url(string $modo = 'login', string $tipo = 'CLIENTE'): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $csrf = bin2hex(random_bytes(16));
    $_SESSION['google_oauth_state'] = $csrf;
    $_SESSION['google_oauth_modo'] = in_array($modo, ['login', 'registo'], true) ? $modo : 'login';
    $_SESSION['google_oauth_tipo'] = in_array($tipo, ['CLIENTE', 'ENTREGADOR'], true) ? $tipo : 'CLIENTE';

    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'online',
        'prompt' => 'select_account',
        'state' => $csrf,
    ];

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

function google_trocar_code(string $code): array
{
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'grant_type' => 'authorization_code',
        ]),
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_TIMEOUT => 20,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        throw new RuntimeException('Falha na ligação ao Google.');
    }
    $data = json_decode($raw, true);
    if ($status >= 400 || empty($data['access_token'])) {
        throw new RuntimeException('Não foi possível autenticar com o Google.');
    }
    return $data;
}

function google_perfil(string $accessToken): array
{
    $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT => 20,
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        throw new RuntimeException('Falha ao obter perfil Google.');
    }
    $data = json_decode($raw, true);
    if ($status >= 400 || empty($data['sub']) || empty($data['email'])) {
        throw new RuntimeException('Não foi possível ler o email da conta Google.');
    }
    return $data;
}
