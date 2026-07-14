<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/icons.php';

$user = require_login();
if (!in_array($user['tipo'], ['CLIENTE', 'ADMIN'], true)) {
    flash('error', 'Área disponível para clientes.');
    redirect(url('index.php'));
}

$pdo = db();

// Cancelar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'cancelar') {
    csrf_require();
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE id = ? AND cliente_id = ? LIMIT 1');
    $stmt->execute([$id, $user['id']]);
    $pedido = $stmt->fetch();
    if ($pedido && cliente_pode_cancelar($pedido)) {
        // Devolver stock
        $itens = $pdo->prepare('SELECT produto_id, quantidade FROM itens_pedido WHERE pedido_id = ?');
        $itens->execute([$id]);
        $upd = $pdo->prepare('UPDATE produtos SET stock = stock + ? WHERE id = ?');
        foreach ($itens->fetchAll() as $item) {
            $upd->execute([(int) $item['quantidade'], (int) $item['produto_id']]);
        }
        $pdo->prepare(
            "UPDATE pedidos SET status_pedido = 'CANCELADO', status_pagamento = 'CANCELADO' WHERE id = ?"
        )->execute([$id]);
        flash('info', 'Pedido ' . $pedido['codigo'] . ' cancelado.');
    } else {
        flash('error', 'Este pedido já não pode ser cancelado.');
    }
    redirect(url('conta/meus_pedidos.php'));
}

$filtro = (string) ($_GET['status'] ?? '');
$busca = trim((string) ($_GET['q'] ?? ''));

$sql = "SELECT p.*, z.nome AS zona_nome, e.nome AS entregador_nome, e.telefone AS entregador_tel
        FROM pedidos p
        JOIN zonas_entrega z ON z.id = p.zona_id
        LEFT JOIN usuarios e ON e.id = p.entregador_id
        WHERE p.cliente_id = ?";
$params = [$user['id']];

if ($filtro && in_array($filtro, ['PENDENTE', 'CONFIRMADO', 'A_CAMINHO', 'ENTREGUE', 'CANCELADO'], true)) {
    $sql .= ' AND p.status_pedido = ?';
    $params[] = $filtro;
}
if ($busca !== '') {
    $sql .= ' AND (p.codigo LIKE ? OR p.endereco LIKE ? OR z.nome LIKE ?)';
    $like = '%' . $busca . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$sql .= ' ORDER BY p.criado_em DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

$statsStmt = $pdo->prepare(
    "SELECT
        COUNT(*) AS total,
        SUM(status_pedido IN ('PENDENTE','CONFIRMADO','A_CAMINHO')) AS activos,
        SUM(status_pedido = 'ENTREGUE') AS entregues,
        SUM(status_pedido = 'CANCELADO') AS cancelados
     FROM pedidos WHERE cliente_id = ?"
);
$statsStmt->execute([$user['id']]);
$stats = $statsStmt->fetch() ?: ['total' => 0, 'activos' => 0, 'entregues' => 0, 'cancelados' => 0];

$pageTitle = 'Os meus pedidos — Nu Chao';
require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero-inner">
    <p class="eyebrow"><?= icon('bag', 'icon inline') ?> Área do cliente</p>
    <h1>Os meus pedidos</h1>
    <p>Acompanhe o estado, veja detalhes e gira as suas encomendas Nu Chao.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="stats orders-stats">
      <div class="stat"><strong><?= (int) $stats['total'] ?></strong><span>Total</span></div>
      <div class="stat"><strong><?= (int) $stats['activos'] ?></strong><span>Em curso</span></div>
      <div class="stat"><strong><?= (int) $stats['entregues'] ?></strong><span>Entregues</span></div>
      <div class="stat"><strong><?= (int) $stats['cancelados'] ?></strong><span>Cancelados</span></div>
    </div>

    <form class="orders-toolbar" method="get">
      <div class="filters">
        <a class="chip <?= $filtro === '' ? 'active' : '' ?>" href="?">Todos</a>
        <?php foreach (['PENDENTE', 'CONFIRMADO', 'A_CAMINHO', 'ENTREGUE', 'CANCELADO'] as $st): ?>
          <a class="chip <?= $filtro === $st ? 'active' : '' ?>" href="?status=<?= $st ?>"><?= e(status_pedido_label($st)) ?></a>
        <?php endforeach; ?>
      </div>
      <label class="search-field orders-search">
        <?= icon('search', 'icon sm') ?>
        <input class="form-control" type="search" name="q" value="<?= e($busca) ?>" placeholder="Código, zona ou morada…">
      </label>
      <?php if ($filtro): ?><input type="hidden" name="status" value="<?= e($filtro) ?>"><?php endif; ?>
    </form>

    <?php if (!$pedidos): ?>
      <div class="empty">
        <p><?= icon('package', 'icon inline') ?> Nenhum pedido encontrado.</p>
        <p><a class="btn primary" href="<?= url('index.php#produtos') ?>"><?= icon('shirt', 'icon') ?> Ver produtos</a></p>
      </div>
    <?php else: ?>
      <div class="orders-list">
        <?php foreach ($pedidos as $p): ?>
          <?php
            $steps = pedido_tracking_steps($p['status_pedido']);
            $current = null;
            foreach ($steps as $s) {
                if ($s['state'] === 'current' || $s['state'] === 'cancelled') {
                    $current = $s;
                    break;
                }
            }
          ?>
          <article class="order-card reveal">
            <div class="order-card-top">
              <div>
                <h3><?= e($p['codigo']) ?></h3>
                <p class="muted"><?= e(date('d/m/Y H:i', strtotime($p['criado_em']))) ?> · <?= e($p['zona_nome']) ?></p>
              </div>
              <div class="order-card-badges">
                <span class="badge-status st-<?= e($p['status_pedido']) ?>"><?= e(status_pedido_label($p['status_pedido'])) ?></span>
                <span class="badge-status st-<?= e($p['status_pagamento']) ?>"><?= e(status_pagamento_label($p['status_pagamento'])) ?></span>
              </div>
            </div>

            <div class="order-mini-track" aria-label="Acompanhamento">
              <?php foreach ($steps as $step): ?>
                <div class="order-mini-step is-<?= e($step['state']) ?>">
                  <span class="dot"></span>
                  <span class="lbl"><?= e($step['label']) ?></span>
                </div>
              <?php endforeach; ?>
            </div>

            <?php if ($current): ?>
              <p class="order-status-msg muted"><?= icon('info', 'icon sm') ?> <?= e($current['desc']) ?></p>
            <?php endif; ?>

            <div class="order-card-meta">
              <div>
                <span class="muted">Morada</span>
                <strong><?= e($p['endereco']) ?></strong>
              </div>
              <div>
                <span class="muted">Total</span>
                <strong class="price"><?= money($p['valor_total']) ?></strong>
              </div>
              <?php if (!empty($p['entregador_nome'])): ?>
                <div>
                  <span class="muted">Entregador</span>
                  <strong><?= e($p['entregador_nome']) ?></strong>
                </div>
              <?php endif; ?>
            </div>

            <div class="order-card-actions">
              <a class="btn primary sm" href="<?= url('conta/pedido.php?id=' . (int) $p['id']) ?>">
                <?= icon('package', 'icon sm') ?> Acompanhar
              </a>
              <?php if (cliente_pode_cancelar($p)): ?>
                <form method="post" onsubmit="return confirm('Cancelar o pedido <?= e($p['codigo']) ?>?');">
                  <input type="hidden" name="acao" value="cancelar">
                  <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                  <button class="btn danger sm" type="submit"><?= icon('close', 'icon sm') ?> Cancelar</button>
                </form>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
