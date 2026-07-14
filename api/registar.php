<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (current_user()) {
    json_response(['ok' => false, 'error' => 'Já tem sessão iniciada.'], 400);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Método inválido.'], 405);
}

csrf_require();

// Honeypot anti-bot
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    json_response(['ok' => true, 'redirect' => url('index.php'), 'message' => 'OK'], 200);
}

$ipKey = 'reg_' . client_ip();
if (rate_limit_hit($ipKey, 8, 900)) {
    json_response(['ok' => false, 'error' => 'Demasiadas tentativas. Aguarde alguns minutos.'], 429);
}

$nome = trim((string) ($_POST['nome'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$telefone = trim((string) ($_POST['telefone'] ?? ''));
$senha = (string) ($_POST['senha'] ?? '');
$senha2 = (string) ($_POST['senha2'] ?? '');
$tipo = (string) ($_POST['tipo'] ?? 'CLIENTE');

if (!in_array($tipo, ['CLIENTE', 'ENTREGADOR'], true)) {
    $tipo = 'CLIENTE';
}

if ($nome === '' || mb_strlen($nome) > 120) {
    json_response(['ok' => false, 'error' => 'Nome inválido.'], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
    json_response(['ok' => false, 'error' => 'Email inválido.'], 422);
}
if ($telefone === '' || mb_strlen($telefone) > 30) {
    json_response(['ok' => false, 'error' => 'Telefone inválido.'], 422);
}
if (strlen($senha) < 6 || strlen($senha) > 128) {
    json_response(['ok' => false, 'error' => 'Palavra-passe: mínimo 6 caracteres.'], 422);
}
if ($senha !== $senha2) {
    json_response(['ok' => false, 'error' => 'As palavras-passe não coincidem.'], 422);
}

try {
    $aprovado = $tipo === 'CLIENTE' ? 1 : 0;
    $stmt = db()->prepare(
        'INSERT INTO usuarios (nome, email, telefone, senha_hash, tipo, ativo, aprovado)
         VALUES (?, ?, ?, ?, ?, 1, ?)'
    );
    $stmt->execute([
        $nome,
        mb_strtolower($email),
        $telefone,
        password_hash($senha, PASSWORD_DEFAULT),
        $tipo,
        $aprovado,
    ]);
    $id = (int) db()->lastInsertId();
    rate_limit_clear($ipKey);

    if ($tipo === 'ENTREGADOR') {
        flash('info', 'Pedido de entregador enviado. Aguarde a aprovação do administrador para entrar.');
        json_response([
            'ok' => true,
            'tipo' => 'ENTREGADOR',
            'redirect' => url('auth/login.php'),
            'message' => 'Pedido enviado. Aguarde aprovação do administrador.',
        ]);
    }

    login_user([
        'id' => $id,
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone,
        'tipo' => 'CLIENTE',
    ]);
    flash('success', 'Conta criada. Já pode fazer compras.');
    json_response([
        'ok' => true,
        'tipo' => 'CLIENTE',
        'redirect' => url('index.php'),
        'message' => 'Conta criada com sucesso.',
    ]);
} catch (PDOException $e) {
    json_response(['ok' => false, 'error' => 'Este email já está registado.'], 409);
}
