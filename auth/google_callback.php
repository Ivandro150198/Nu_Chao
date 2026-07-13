<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if (current_user()) {
    redirect('/No_chao/index.php');
}

if (!google_configurado()) {
    flash('error', 'Login Google não configurado.');
    redirect('/No_chao/auth/login.php');
}

if (!empty($_GET['error'])) {
    flash('error', 'Autenticação Google cancelada.');
    redirect('/No_chao/auth/login.php');
}

$state = (string) ($_GET['state'] ?? '');
$code = (string) ($_GET['code'] ?? '');
$expected = (string) ($_SESSION['google_oauth_state'] ?? '');
$modo = (string) ($_SESSION['google_oauth_modo'] ?? 'login');
$tipo = (string) ($_SESSION['google_oauth_tipo'] ?? 'CLIENTE');

unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_modo'], $_SESSION['google_oauth_tipo']);

if ($code === '' || $state === '' || !hash_equals($expected, $state)) {
    flash('error', 'Sessão Google inválida. Tente novamente.');
    redirect('/No_chao/auth/login.php');
}

try {
    $tokens = google_trocar_code($code);
    $perfil = google_perfil($tokens['access_token']);
} catch (Throwable $e) {
    error_log('Google OAuth: ' . $e->getMessage());
    flash('error', 'Não foi possível autenticar com o Google. Tente novamente.');
    redirect('/No_chao/auth/login.php');
}

$googleId = (string) $perfil['sub'];
$email = strtolower(trim((string) $perfil['email']));
$nome = trim((string) ($perfil['name'] ?? $perfil['given_name'] ?? 'Utilizador Google'));
if ($nome === '') {
    $nome = 'Utilizador Google';
}

$pdo = db();

// 1) Já existe conta com este google_id
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE google_id = ? LIMIT 1');
$stmt->execute([$googleId]);
$user = $stmt->fetch();

// 2) Conta com o mesmo email
if (!$user) {
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        // Conta com palavra-passe: não associar Google automaticamente (evita takeover)
        if (!empty($user['senha_hash'])) {
            flash('error', 'Já existe uma conta com este email. Entre com a palavra-passe e associe o Google no perfil, ou use outro email Google.');
            redirect('/No_chao/auth/login.php');
        }
        $pdo->prepare('UPDATE usuarios SET google_id = ? WHERE id = ? AND (google_id IS NULL OR google_id = "")')
            ->execute([$googleId, $user['id']]);
        $user['google_id'] = $googleId;
    }
}

if ($user) {
    if (empty($user['ativo'])) {
        flash('error', 'Esta conta está desactivada. Contacte o administrador.');
        redirect('/No_chao/auth/login.php');
    }
    if ($user['tipo'] === 'ENTREGADOR' && empty($user['aprovado'])) {
        flash('error', 'A sua conta de entregador ainda aguarda aprovação do administrador.');
        redirect('/No_chao/auth/login.php');
    }

    login_user($user);
    flash('success', 'Bem-vindo(a), ' . $user['nome'] . '!');
    if ($user['tipo'] === 'ADMIN') redirect('/No_chao/admin/');
    if ($user['tipo'] === 'ENTREGADOR') redirect('/No_chao/entregador/');
    redirect('/No_chao/index.php');
}

// 3) Conta nova — criar conforme tipo escolhido no registo (ou cliente no login)
if (!in_array($tipo, ['CLIENTE', 'ENTREGADOR'], true)) {
    $tipo = 'CLIENTE';
}
// No login com Google sem conta: cria como cliente (pode comprar de imediato)
if ($modo === 'login') {
    $tipo = 'CLIENTE';
}

$aprovado = $tipo === 'CLIENTE' ? 1 : 0;
$telefone = 'Não informado';

$ins = $pdo->prepare(
    'INSERT INTO usuarios (nome, email, telefone, senha_hash, tipo, ativo, aprovado, google_id)
     VALUES (?, ?, ?, NULL, ?, 1, ?, ?)'
);
$ins->execute([$nome, $email, $telefone, $tipo, $aprovado, $googleId]);
$id = (int) $pdo->lastInsertId();

if ($tipo === 'ENTREGADOR') {
    flash('info', 'Conta Google de entregador criada. Aguarde a aprovação do administrador para entrar.');
    redirect('/No_chao/auth/login.php');
}

login_user([
    'id' => $id,
    'nome' => $nome,
    'email' => $email,
    'telefone' => $telefone,
    'tipo' => 'CLIENTE',
]);
flash('success', 'Conta criada com Google. Já pode fazer compras.');
redirect('/No_chao/index.php');
