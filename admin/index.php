<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

$stats = [
    'produtos' => (int) db()->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1')->fetchColumn(),
    'pendentes' => (int) db()->query("SELECT COUNT(*) FROM pedidos WHERE status_pedido = 'PENDENTE'")->fetchColumn(),
    'caminho' => (int) db()->query("SELECT COUNT(*) FROM pedidos WHERE status_pedido = 'A_CAMINHO'")->fetchColumn(),
    'entregues' => (int) db()->query("SELECT COUNT(*) FROM pedidos WHERE status_pedido = 'ENTREGUE'")->fetchColumn(),
    'receita' => (float) db()->query("SELECT COALESCE(SUM(valor_total),0) FROM pedidos WHERE status_pagamento = 'PAGO_NA_ENTREGA'")->fetchColumn(),
    'entregadores_pendentes' => (int) db()->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'ENTREGADOR' AND aprovado = 0 AND ativo = 1")->fetchColumn(),
    'stock_baixo' => (int) db()->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1 AND stock > 0 AND stock <= stock_alerta')->fetchColumn(),
    'promocoes' => (int) db()->query('SELECT COUNT(*) FROM promocoes WHERE activo = 1')->fetchColumn(),
];

$recentes = db()->query(
    "SELECT p.*, u.nome AS cliente, z.nome AS zona
     FROM pedidos p
     JOIN usuarios u ON u.id = p.cliente_id
     JOIN zonas_entrega z ON z.id = p.zona_id
     ORDER BY p.criado_em DESC LIMIT 8"
)->fetchAll();

admin_header('Dashboard', 'dashboard');
?>

<div class="admin-page-head painel-head">
  <div>
    <h2>Dashboard</h2>
    <p>Centro de gestão da loja Nu Chao — conteúdo, produtos, pedidos e entregas.</p>
  </div>
  <a class="btn primary sm" href="/No_chao/admin/site.php"><?= icon('package', 'icon sm') ?> Gestão do site</a>
</div>

<div class="admin-stats painel-stats">
  <div class="admin-stat painel-stat"><strong><?= $stats['produtos'] ?></strong><span>Produtos activos</span></div>
  <div class="admin-stat painel-stat"><strong><?= $stats['promocoes'] ?></strong><span>Promoções activas</span></div>
  <div class="admin-stat painel-stat"><strong><?= $stats['stock_baixo'] ?></strong><span>Stock baixo</span></div>
  <div class="admin-stat painel-stat"><strong><?= $stats['pendentes'] ?></strong><span>Pedidos pendentes</span></div>
  <div class="admin-stat painel-stat"><strong><?= $stats['caminho'] ?></strong><span>A caminho</span></div>
  <div class="admin-stat painel-stat"><strong><?= money($stats['receita']) ?></strong><span>Recebido na entrega</span></div>
</div>

<?php if ($stats['stock_baixo'] > 0): ?>
  <div class="flash flash-info" style="margin-bottom:1rem">
    <?= $stats['stock_baixo'] ?> produto(s) com stock baixo.
    <a href="/No_chao/admin/configuracoes.php">Ver alertas</a> ·
    <a href="/No_chao/admin/produtos.php">Gerir stock</a>
  </div>
<?php endif; ?>

<?php if ($stats['entregadores_pendentes'] > 0): ?>
  <div class="flash flash-info" style="margin-bottom:1rem">
    Há <?= (int)$stats['entregadores_pendentes'] ?> pedido(s) de entregador à espera de aprovação.
    <a href="/No_chao/admin/entregadores.php">Validar agora</a>
  </div>
<?php endif; ?>

<div class="admin-cms-grid" style="margin-bottom:1.25rem">
  <a class="admin-cms-card" href="/No_chao/admin/site.php">
    <span class="admin-cms-ico"><?= icon('package') ?></span>
    <strong>Gestão do site</strong>
    <span>Hero, textos, contacto e zonas</span>
  </a>
  <a class="admin-cms-card" href="/No_chao/admin/hero.php">
    <span class="admin-cms-ico"><?= icon('spark') ?></span>
    <strong>Hero</strong>
    <span>Banner da página inicial</span>
  </a>
  <a class="admin-cms-card" href="/No_chao/admin/produtos.php">
    <span class="admin-cms-ico"><?= icon('shirt') ?></span>
    <strong>Produtos</strong>
    <span>Catálogo e stock</span>
  </a>
  <a class="admin-cms-card" href="/No_chao/admin/promocoes.php">
    <span class="admin-cms-ico"><?= icon('tag') ?></span>
    <strong>Promoções</strong>
    <span>Alertas e descontos</span>
  </a>
</div>

<div class="admin-list-card" style="margin-top:1rem">
  <div class="list-head">
    <h3>Pedidos recentes</h3>
    <a href="/No_chao/admin/pedidos.php">Ver todos</a>
  </div>
  <div class="admin-scroll">
    <table class="admin-table" style="min-width:700px;table-layout:auto">
      <thead>
        <tr>
          <th>Código</th>
          <th>Cliente</th>
          <th>Zona</th>
          <th>Total</th>
          <th>Estado</th>
          <th>Pagamento</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentes as $p): ?>
          <tr>
            <td><a href="/No_chao/admin/pedidos.php?id=<?= (int)$p['id'] ?>"><strong><?= e($p['codigo']) ?></strong></a></td>
            <td><?= e($p['cliente']) ?></td>
            <td><?= e($p['zona']) ?></td>
            <td><?= money($p['valor_total']) ?></td>
            <td><span class="badge-status st-<?= e($p['status_pedido']) ?>"><?= e(status_pedido_label($p['status_pedido'])) ?></span></td>
            <td><span class="badge-status st-<?= e($p['status_pagamento']) ?>"><?= e(status_pagamento_label($p['status_pagamento'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recentes): ?>
          <tr><td colspan="6" class="muted">Ainda sem pedidos.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php admin_footer('dashboard'); ?>
