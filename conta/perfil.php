<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/icons.php';

$user = require_login();
$pdo = db();
$error = '';
$ok = false;

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
$stmt->execute([$user['id']]);
$perfil = $stmt->fetch();
if (!$perfil) {
    logout_user();
    redirect('/No_chao/auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $acao = $_POST['acao'] ?? 'perfil';
    if ($acao === 'perfil') {
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $telefone = trim((string) ($_POST['telefone'] ?? ''));
        if ($nome === '' || $telefone === '') {
            $error = 'Nome e telefone são obrigatórios.';
        } else {
            $pdo->prepare('UPDATE usuarios SET nome = ?, telefone = ? WHERE id = ?')
                ->execute([$nome, $telefone, $user['id']]);
            $_SESSION['user']['nome'] = $nome;
            $_SESSION['user']['telefone'] = $telefone;
            $perfil['nome'] = $nome;
            $perfil['telefone'] = $telefone;
            $ok = true;
            flash('success', 'Perfil actualizado.');
            redirect('/No_chao/conta/perfil.php');
        }
    }

    if ($acao === 'senha') {
        $actual = (string) ($_POST['senha_actual'] ?? '');
        $nova = (string) ($_POST['senha_nova'] ?? '');
        $nova2 = (string) ($_POST['senha_nova2'] ?? '');
        if (!empty($perfil['senha_hash']) && !password_verify($actual, $perfil['senha_hash'])) {
            $error = 'Palavra-passe actual incorrecta.';
        } elseif (strlen($nova) < 6) {
            $error = 'A nova palavra-passe deve ter pelo menos 6 caracteres.';
        } elseif ($nova !== $nova2) {
            $error = 'A confirmação da nova palavra-passe não coincide.';
        } else {
            $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')
                ->execute([password_hash($nova, PASSWORD_DEFAULT), $user['id']]);
            flash('success', 'Palavra-passe actualizada.');
            redirect('/No_chao/conta/perfil.php');
        }
    }
}

$cnt = $pdo->prepare('SELECT COUNT(*) FROM pedidos WHERE cliente_id = ?');
$cnt->execute([$user['id']]);
$pedidosCount = (int) $cnt->fetchColumn();

$pageTitle = 'O meu perfil — Nu Chao';
require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero-inner">
    <p class="eyebrow"><?= icon('user', 'icon inline') ?> Conta</p>
    <h1>O meu perfil</h1>
    <p>Gerir os seus dados e aceder aos pedidos.</p>
  </div>
</section>

<section class="section">
  <div class="container profile-layout">
    <aside class="panel profile-side reveal">
      <div class="profile-avatar"><?= strtoupper(mb_substr($perfil['nome'], 0, 1)) ?></div>
      <h3><?= e($perfil['nome']) ?></h3>
      <p class="muted"><?= e($perfil['email']) ?></p>
      <p class="muted"><?= e($perfil['telefone']) ?></p>
      <span class="pill pill-cat"><?= e($perfil['tipo']) ?></span>
      <?php if ($perfil['tipo'] === 'CLIENTE'): ?>
        <p class="muted" style="margin-top:0.85rem"><?= (int) $pedidosCount ?> pedido(s)</p>
        <a class="btn primary block" href="/No_chao/conta/meus_pedidos.php" style="margin-top:0.75rem">
          <?= icon('bag', 'icon') ?> Os meus pedidos
        </a>
      <?php elseif ($perfil['tipo'] === 'ADMIN'): ?>
        <a class="btn primary block" href="/No_chao/admin/" style="margin-top:0.75rem"><?= icon('package', 'icon') ?> Gestão</a>
      <?php elseif ($perfil['tipo'] === 'ENTREGADOR'): ?>
        <a class="btn primary block" href="/No_chao/entregador/" style="margin-top:0.75rem"><?= icon('truck', 'icon') ?> Entregas</a>
      <?php endif; ?>
    </aside>

    <div class="profile-main">
      <?php if ($error): ?>
        <div class="flash flash-error" style="margin-bottom:1rem"><?= e($error) ?></div>
      <?php endif; ?>

      <form class="panel reveal" method="post">
        <h3><?= icon('user', 'icon inline') ?> Dados pessoais</h3>
        <input type="hidden" name="acao" value="perfil">
        <div class="form-group">
          <label for="nome">Nome</label>
          <input id="nome" name="nome" required value="<?= e($perfil['nome']) ?>">
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" type="email" value="<?= e($perfil['email']) ?>" disabled>
          <small class="muted">O email não pode ser alterado.</small>
        </div>
        <div class="form-group">
          <label for="telefone">Telefone / WhatsApp</label>
          <input id="telefone" name="telefone" required value="<?= e($perfil['telefone']) ?>">
        </div>
        <button class="btn primary" type="submit"><?= icon('check', 'icon') ?> Guardar</button>
      </form>

      <?php if (empty($perfil['google_id']) || !empty($perfil['senha_hash'])): ?>
        <form class="panel reveal" method="post" style="margin-top:1rem">
          <h3><?= icon('login', 'icon inline') ?> Palavra-passe</h3>
          <input type="hidden" name="acao" value="senha">
          <?php if (!empty($perfil['senha_hash'])): ?>
            <div class="form-group">
              <label for="senha_actual">Palavra-passe actual</label>
              <input type="password" id="senha_actual" name="senha_actual" required>
            </div>
          <?php endif; ?>
          <div class="form-row">
            <div class="form-group">
              <label for="senha_nova">Nova palavra-passe</label>
              <input type="password" id="senha_nova" name="senha_nova" required minlength="6">
            </div>
            <div class="form-group">
              <label for="senha_nova2">Confirmar</label>
              <input type="password" id="senha_nova2" name="senha_nova2" required minlength="6">
            </div>
          </div>
          <button class="btn ghost" type="submit">Actualizar palavra-passe</button>
        </form>
      <?php else: ?>
        <div class="panel reveal" style="margin-top:1rem">
          <p class="muted">Esta conta entra com Google. Não precisa de palavra-passe local.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
