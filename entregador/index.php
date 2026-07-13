<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/icons.php';

$user = require_login('ENTREGADOR');

$chk = db()->prepare('SELECT aprovado, ativo FROM usuarios WHERE id = ?');
$chk->execute([$user['id']]);
$row = $chk->fetch();
if (!$row || empty($row['ativo']) || empty($row['aprovado'])) {
    logout_user();
    flash('error', 'A sua conta de entregador ainda não está aprovada.');
    redirect('/No_chao/auth/login.php');
}
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $acao = $_POST['acao'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    $check = $pdo->prepare('SELECT * FROM pedidos WHERE id = ? AND entregador_id = ?');
    $check->execute([$id, $user['id']]);
    $pedido = $check->fetch();

    if (!$pedido) {
        flash('error', 'Pedido não encontrado ou não atribuído a si.');
        redirect('/No_chao/entregador/');
    }

    if ($acao === 'iniciar' && in_array($pedido['status_pedido'], ['PENDENTE', 'CONFIRMADO'], true)) {
        $pdo->prepare("UPDATE pedidos SET status_pedido = 'A_CAMINHO' WHERE id = ?")->execute([$id]);
        flash('success', 'Entrega iniciada — está a caminho.');
    }

    if ($acao === 'entregar' && $pedido['status_pedido'] === 'A_CAMINHO') {
        $pdo->prepare(
            "UPDATE pedidos SET status_pedido = 'ENTREGUE', status_pagamento = 'PAGO_NA_ENTREGA' WHERE id = ?"
        )->execute([$id]);
        flash('success', 'Entrega concluída e pagamento registado.');
    }

    redirect('/No_chao/entregador/');
}

$filtro = $_GET['tab'] ?? 'activas';

$sql = "SELECT p.*, u.nome AS cliente, u.telefone AS cliente_tel, z.nome AS zona
        FROM pedidos p
        JOIN usuarios u ON u.id = p.cliente_id
        JOIN zonas_entrega z ON z.id = p.zona_id
        WHERE p.entregador_id = ?";

if ($filtro === 'concluidas') {
    $sql .= " AND p.status_pedido = 'ENTREGUE'";
} else {
    $sql .= " AND p.status_pedido IN ('PENDENTE','CONFIRMADO','A_CAMINHO')";
}
$sql .= ' ORDER BY p.criado_em DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute([$user['id']]);
$pedidos = $stmt->fetchAll();

$st = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE entregador_id = ? AND status_pedido IN ('PENDENTE','CONFIRMADO','A_CAMINHO')");
$st->execute([$user['id']]);
$activas = (int) $st->fetchColumn();
$st2 = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE entregador_id = ? AND status_pedido = 'ENTREGUE'");
$st2->execute([$user['id']]);
$concluidas = (int) $st2->fetchColumn();

$pageTitle = 'Entregas — Nu Chao';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="pt" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= e($pageTitle) ?></title>
  <?= csrf_meta() ?>
  <meta name="theme-color" content="#0f2e1f" id="metaThemeColor">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/No_chao/assets/css/style.css">
  <link rel="stylesheet" href="/No_chao/assets/css/painel.css">
  <link rel="stylesheet" href="/No_chao/assets/css/responsive.css">
  <link rel="stylesheet" href="/No_chao/assets/css/admin-responsive.css">
  <script>
    (function () {
      try {
        var t = localStorage.getItem('nu_chao_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', t);
      } catch (e) {}
    })();
  </script>
</head>
<body class="painel-body">
<header class="topbar painel-topbar">
  <div class="painel-shell topbar-inner">
    <a class="brand" href="/No_chao/entregador/">
      <img src="/No_chao/assets/logo-nc.png" alt="Nu Chao" class="brand-logo">
      <span class="brand-text painel-brand-text">Deliver</span>
    </a>
    <div class="actions">
      <button type="button" class="icon-btn" id="themeToggle" aria-label="Mudar tema" title="Mudar tema">
        <span class="theme-ico theme-ico-sun" hidden><?= icon('sun') ?></span>
        <span class="theme-ico theme-ico-moon"><?= icon('moon') ?></span>
      </button>
      <span class="user-chip"><?= e($user['nome']) ?></span>
      <a class="btn ghost sm hide-sm" href="/No_chao/auth/logout.php"><?= icon('logout', 'icon sm') ?> Sair</a>
    </div>
  </div>
</header>

<?php if ($flash): ?>
  <div class="flash flash-<?= e($flash['type']) ?>"><div class="painel-shell"><?= e($flash['message']) ?></div></div>
<?php endif; ?>

<main class="section">
  <div class="painel-shell">
    <div class="painel-head">
      <div>
        <h2><?= icon('truck', 'icon inline') ?> Área do entregador</h2>
        <p>Receba pedidos, entregue e confirme o pagamento na entrega.</p>
      </div>
    </div>

    <div class="painel-stats">
      <div class="painel-stat"><strong><?= $activas ?></strong><span>Entregas activas</span></div>
      <div class="painel-stat"><strong><?= $concluidas ?></strong><span>Concluídas</span></div>
    </div>

    <nav class="painel-tabs" aria-label="Filtro de entregas">
      <a class="<?= $filtro !== 'concluidas' ? 'active' : '' ?>" href="?tab=activas"><?= icon('clock', 'icon sm') ?> Activas</a>
      <a class="<?= $filtro === 'concluidas' ? 'active' : '' ?>" href="?tab=concluidas"><?= icon('check', 'icon sm') ?> Concluídas</a>
    </nav>

    <?php if (!$pedidos): ?>
      <div class="empty reveal">Sem entregas nesta lista. Aguarde atribuição do administrador.</div>
    <?php else: ?>
      <div class="deliver-grid">
        <?php foreach ($pedidos as $i => $p): ?>
          <?php
            $itensStmt = $pdo->prepare(
                'SELECT i.quantidade, i.tamanho, pr.nome FROM itens_pedido i
                 JOIN produtos pr ON pr.id = i.produto_id WHERE i.pedido_id = ?'
            );
            $itensStmt->execute([$p['id']]);
            $itens = $itensStmt->fetchAll();
            $cardClass = 'deliver-card reveal';
            if ($p['status_pedido'] === 'A_CAMINHO') {
                $cardClass .= ' is-caminho';
            } elseif ($p['status_pedido'] === 'ENTREGUE') {
                $cardClass .= ' is-done';
            }
          ?>
          <article class="<?= e($cardClass) ?>" style="animation-delay: <?= number_format(min($i, 6) * 0.06, 2) ?>s">
            <div class="meta-row">
              <h3><?= e($p['codigo']) ?></h3>
              <span class="badge-status st-<?= e($p['status_pedido']) ?>"><?= e(status_pedido_label($p['status_pedido'])) ?></span>
            </div>
            <p>
              <strong><?= icon('user', 'icon sm') ?> <?= e($p['cliente']) ?></strong><br>
              <a href="tel:<?= e($p['telefone_contacto']) ?>"><?= icon('phone', 'icon sm') ?> <?= e($p['telefone_contacto']) ?></a>
            </p>
            <p class="muted">
              <?= icon('map', 'icon sm') ?> <?= e($p['zona']) ?> · <?= e($p['endereco']) ?>
              <?php if ($p['ponto_referencia']): ?><br>Ref: <?= e($p['ponto_referencia']) ?><?php endif; ?>
            </p>
            <p><strong>A cobrar:</strong> <span class="price"><?= money($p['valor_total']) ?></span></p>
            <p class="muted">
              <?= e(metodo_pagamento_label($p['metodo_pagamento'])) ?>
              <?php if ($p['precisa_troco_para']): ?> · troco para <?= money($p['precisa_troco_para']) ?><?php endif; ?>
            </p>
            <ul>
              <?php foreach ($itens as $item): ?>
                <li><?= e($item['nome']) ?> (<?= e($item['tamanho']) ?>) × <?= (int)$item['quantidade'] ?></li>
              <?php endforeach; ?>
            </ul>

            <div class="card-actions">
              <?php if (in_array($p['status_pedido'], ['PENDENTE', 'CONFIRMADO'], true)): ?>
                <form method="post">
                  <input type="hidden" name="acao" value="iniciar">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button class="btn primary block" type="submit"><?= icon('truck', 'icon') ?> Iniciar entrega</button>
                </form>
                <a class="btn ghost block" href="https://wa.me/<?= e(preg_replace('/\D+/', '', $p['telefone_contacto'])) ?>" target="_blank" rel="noopener">
                  <?= icon('whatsapp', 'icon') ?> Contactar cliente
                </a>
              <?php elseif ($p['status_pedido'] === 'A_CAMINHO'): ?>
                <form method="post" onsubmit="return confirm('Confirmar entrega e pagamento recebido?')">
                  <input type="hidden" name="acao" value="entregar">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button class="btn primary block" type="submit"><?= icon('check', 'icon') ?> Entregue · Pagamento recebido</button>
                </form>
                <a class="btn ghost block" href="tel:<?= e($p['telefone_contacto']) ?>">
                  <?= icon('phone', 'icon') ?> Ligar ao cliente
                </a>
              <?php else: ?>
                <p class="muted"><?= icon('check', 'icon sm') ?> Pago na entrega</p>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<nav class="painel-mobile-bar" aria-label="Navegação entregador">
  <a href="?tab=activas" class="<?= $filtro !== 'concluidas' ? 'is-active' : '' ?>">
    <?= icon('truck') ?><span>Activas</span>
  </a>
  <a href="?tab=concluidas" class="<?= $filtro === 'concluidas' ? 'is-active' : '' ?>">
    <?= icon('check') ?><span>Feitas</span>
  </a>
  <a href="tel:<?= e($user['telefone'] ?? '') ?>">
    <?= icon('phone') ?><span>Chamada</span>
  </a>
  <a href="/No_chao/auth/logout.php">
    <?= icon('logout') ?><span>Sair</span>
  </a>
</nav>

<script src="/No_chao/assets/js/app.js"></script>
</body>
</html>
