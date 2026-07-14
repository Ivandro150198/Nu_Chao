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
$id = (int) ($_GET['id'] ?? 0);
$codigo = trim((string) ($_GET['codigo'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'cancelar') {
    csrf_require();
    $cancelId = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE id = ? AND cliente_id = ? LIMIT 1');
    $stmt->execute([$cancelId, $user['id']]);
    $pedido = $stmt->fetch();
    if ($pedido && cliente_pode_cancelar($pedido)) {
        $itens = $pdo->prepare('SELECT produto_id, quantidade FROM itens_pedido WHERE pedido_id = ?');
        $itens->execute([$cancelId]);
        $upd = $pdo->prepare('UPDATE produtos SET stock = stock + ? WHERE id = ?');
        foreach ($itens->fetchAll() as $item) {
            $upd->execute([(int) $item['quantidade'], (int) $item['produto_id']]);
        }
        $pdo->prepare(
            "UPDATE pedidos SET status_pedido = 'CANCELADO', status_pagamento = 'CANCELADO' WHERE id = ?"
        )->execute([$cancelId]);
        flash('info', 'Pedido cancelado.');
        redirect(url('conta/pedido.php?id=') . $cancelId);
    }
    flash('error', 'Este pedido já não pode ser cancelado.');
    redirect(url('conta/meus_pedidos.php'));
}

if ($id > 0) {
    $stmt = $pdo->prepare(
        "SELECT p.*, z.nome AS zona_nome, z.tempo_estimado,
                e.nome AS entregador_nome, e.telefone AS entregador_tel
         FROM pedidos p
         JOIN zonas_entrega z ON z.id = p.zona_id
         LEFT JOIN usuarios e ON e.id = p.entregador_id
         WHERE p.id = ? AND p.cliente_id = ?
         LIMIT 1"
    );
    $stmt->execute([$id, $user['id']]);
} elseif ($codigo !== '') {
    $stmt = $pdo->prepare(
        "SELECT p.*, z.nome AS zona_nome, z.tempo_estimado,
                e.nome AS entregador_nome, e.telefone AS entregador_tel
         FROM pedidos p
         JOIN zonas_entrega z ON z.id = p.zona_id
         LEFT JOIN usuarios e ON e.id = p.entregador_id
         WHERE p.codigo = ? AND p.cliente_id = ?
         LIMIT 1"
    );
    $stmt->execute([$codigo, $user['id']]);
} else {
    redirect(url('conta/meus_pedidos.php'));
}

$pedido = $stmt->fetch();
if (!$pedido) {
    flash('error', 'Pedido não encontrado.');
    redirect(url('conta/meus_pedidos.php'));
}

$itensStmt = $pdo->prepare(
    'SELECT i.*, pr.nome AS produto_nome, pr.imagem
     FROM itens_pedido i
     JOIN produtos pr ON pr.id = i.produto_id
     WHERE i.pedido_id = ?'
);
$itensStmt->execute([$pedido['id']]);
$itens = $itensStmt->fetchAll();

$steps = pedido_tracking_steps($pedido['status_pedido']);
$waUrl = whatsapp_url(montar_mensagem_pedido(
    $pedido,
    $itens,
    (string) $pedido['zona_nome'],
    ['nome' => $user['nome'], 'telefone' => $pedido['telefone_contacto']]
));

$pageTitle = 'Pedido ' . $pedido['codigo'] . ' — Nu Chao';
require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero-inner">
    <p class="eyebrow"><a href="<?= url('conta/meus_pedidos.php') ?>"><?= icon('bag', 'icon inline') ?> Os meus pedidos</a></p>
    <h1>Pedido <?= e($pedido['codigo']) ?></h1>
    <p>Acompanhe em tempo real o estado da sua encomenda.</p>
  </div>
</section>

<section class="section">
  <div class="container track-layout">
    <div class="panel track-panel">
      <div class="order-card-badges" style="margin-bottom:1.25rem">
        <span class="badge-status st-<?= e($pedido['status_pedido']) ?>"><?= e(status_pedido_label($pedido['status_pedido'])) ?></span>
        <span class="badge-status st-<?= e($pedido['status_pagamento']) ?>"><?= e(status_pagamento_label($pedido['status_pagamento'])) ?></span>
      </div>

      <h2 class="track-title"><?= icon('truck', 'icon inline') ?> Acompanhamento</h2>
      <ol class="track-timeline">
        <?php foreach ($steps as $step): ?>
          <li class="track-step is-<?= e($step['state']) ?>">
            <span class="track-marker">
              <?php if ($step['state'] === 'done'): ?>
                <?= icon('check') ?>
              <?php elseif ($step['state'] === 'cancelled'): ?>
                <?= icon('close') ?>
              <?php elseif ($step['state'] === 'current'): ?>
                <?= icon('clock') ?>
              <?php else: ?>
                <span class="track-empty"></span>
              <?php endif; ?>
            </span>
            <div class="track-content">
              <strong><?= e($step['label']) ?></strong>
              <p class="muted"><?= e($step['desc']) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>

      <div class="track-actions">
        <a class="btn primary" href="<?= e($waUrl) ?>" target="_blank" rel="noopener">
          <?= icon('whatsapp', 'icon') ?> WhatsApp da loja
        </a>
        <?php if (cliente_pode_cancelar($pedido)): ?>
          <form method="post" onsubmit="return confirm('Cancelar este pedido?');">
            <input type="hidden" name="acao" value="cancelar">
            <input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
            <button class="btn danger" type="submit"><?= icon('close', 'icon') ?> Cancelar pedido</button>
          </form>
        <?php endif; ?>
        <a class="btn ghost" href="<?= url('conta/meus_pedidos.php') ?>"><?= icon('bag', 'icon') ?> Voltar aos pedidos</a>
      </div>
    </div>

    <aside class="track-side">
      <div class="panel">
        <h3><?= icon('map', 'icon inline') ?> Entrega</h3>
        <p><strong>Zona:</strong> <?= e($pedido['zona_nome']) ?></p>
        <p><strong>Morada:</strong> <?= e($pedido['endereco']) ?></p>
        <?php if ($pedido['ponto_referencia']): ?>
          <p><strong>Ref:</strong> <?= e($pedido['ponto_referencia']) ?></p>
        <?php endif; ?>
        <p><strong>Contacto:</strong> <?= e($pedido['telefone_contacto']) ?></p>
        <?php if ($pedido['tempo_estimado']): ?>
          <p class="muted"><?= icon('clock', 'icon sm') ?> Tempo estimado: <?= e($pedido['tempo_estimado']) ?></p>
        <?php endif; ?>
        <?php if (!empty($pedido['entregador_nome'])): ?>
          <hr class="soft-hr">
          <p><strong>Entregador:</strong> <?= e($pedido['entregador_nome']) ?></p>
          <?php if (!empty($pedido['entregador_tel'])): ?>
            <p><a href="tel:<?= e($pedido['entregador_tel']) ?>"><?= icon('phone', 'icon sm') ?> <?= e($pedido['entregador_tel']) ?></a></p>
          <?php endif; ?>
        <?php else: ?>
          <p class="muted">Entregador ainda não atribuído.</p>
        <?php endif; ?>
      </div>

      <div class="panel">
        <h3><?= icon('package', 'icon inline') ?> Itens</h3>
        <ul class="order-items">
          <?php foreach ($itens as $i): ?>
            <li>
              <span><?= e($i['produto_nome']) ?> (<?= e($i['tamanho']) ?>) × <?= (int) $i['quantidade'] ?></span>
              <strong><?= money($i['subtotal']) ?></strong>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="summary-line"><span>Produtos</span><span><?= money($pedido['total_produtos']) ?></span></div>
        <div class="summary-line"><span>Entrega</span><span><?= money($pedido['taxa_entrega']) ?></span></div>
        <div class="summary-line total"><span>Total</span><span><?= money($pedido['valor_total']) ?></span></div>
        <p class="muted" style="margin-top:0.75rem">
          Pagamento: <?= e(metodo_pagamento_label($pedido['metodo_pagamento'])) ?>
          <?php if ($pedido['precisa_troco_para']): ?>
            · troco para <?= money($pedido['precisa_troco_para']) ?>
          <?php endif; ?>
        </p>
        <?php if ($pedido['observacoes']): ?>
          <p class="muted"><strong>Obs:</strong> <?= e($pedido['observacoes']) ?></p>
        <?php endif; ?>
      </div>
    </aside>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
