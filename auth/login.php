<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if (current_user()) {
    $u = current_user();
    if ($u['tipo'] === 'ADMIN') redirect(url('admin/'));
    if ($u['tipo'] === 'ENTREGADOR') redirect(url('entregador/'));
    redirect(url('index.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');
    $ipKey = 'login_' . client_ip();
    $emailKey = 'login_e_' . mb_strtolower($email);

    if (rate_limit_hit($ipKey, 12, 900) || rate_limit_hit($emailKey, 8, 900)) {
        $error = 'Demasiadas tentativas. Aguarde alguns minutos e tente novamente.';
    } else {
        $stmt = db()->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $senhaOk = $user && !empty($user['senha_hash']) && password_verify($senha, $user['senha_hash']);

        if ($senhaOk) {
            if (empty($user['ativo'])) {
                $error = 'Esta conta está desactivada. Contacte o administrador.';
            } elseif ($user['tipo'] === 'ENTREGADOR' && empty($user['aprovado'])) {
                $error = 'A sua conta de entregador ainda aguarda aprovação do administrador.';
            } else {
                rate_limit_clear($ipKey);
                rate_limit_clear($emailKey);
                login_user($user);
                flash('success', 'Bem-vindo(a), ' . $user['nome'] . '!');
                if ($user['tipo'] === 'ADMIN') redirect(url('admin/'));
                if ($user['tipo'] === 'ENTREGADOR') redirect(url('entregador/'));
                redirect(url('index.php'));
            }
        } elseif ($user && empty($user['senha_hash']) && !empty($user['google_id'])) {
            $error = 'Esta conta usa Google. Clique em “Continuar com Google”.';
        } else {
            usleep(250000);
            $error = 'Email ou palavra-passe incorrectos.';
        }
    }
}

$pageTitle = 'Entrar — ' . site_nome();
require __DIR__ . '/../includes/header.php';
?>

<section class="auth-wrap">
  <div class="auth-card">
    <h1>Entrar</h1>
    <p class="muted">Aceda à loja, gestão ou área do entregador.</p>
    <?php if ($error): ?><p class="flash-error" style="padding:0.5rem 0"><?= e($error) ?></p><?php endif; ?>

    <?php if (google_configurado()): ?>
      <a class="btn-google" href="<?= url('auth/google_auth.php?modo=login') ?>">
        <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.9 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l5.7-5.7C34.2 6.1 29.4 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.5-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 16 19 12 24 12c3.1 0 5.8 1.1 8 3l5.7-5.7C34.2 6.1 29.4 4 24 4 16.3 4 9.6 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.3 35.3 26.8 36 24 36c-5.3 0-9.7-3.1-11.3-7.5l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.1-4.1 5.5l.1.1 6.2 5.2C39.2 37.3 44 33 44 24c0-1.3-.1-2.5-.4-3.5z"/></svg>
        Continuar com Google
      </a>
      <div class="auth-divider"><span>ou</span></div>
    <?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email" required autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="senha">Palavra-passe</label>
        <input class="form-control" type="password" id="senha" name="senha" required autocomplete="current-password">
      </div>
      <button class="btn primary block" type="submit">Entrar</button>
    </form>
    <p class="help">Não tem conta? <a class="js-open-register" href="<?= url('auth/registar.php') ?>">Registar</a></p>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
