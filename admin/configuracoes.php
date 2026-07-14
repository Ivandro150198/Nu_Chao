<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

$pdo = db();

$defaults = [
    'mostrar_stock' => '1',
    'stock_alerta_global' => '5',
    'alerta_promocao_activo' => '1',
    'alerta_promocao_texto' => '',
    'loja_aberta' => '1',
    'mensagem_loja_fechada' => 'Estamos temporariamente encerrados. Volte em breve.',
    'whatsapp_loja' => APP_WHATSAPP,
    'mostrar_precos' => '1',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = array_keys($defaults);
    foreach ($keys as $chave) {
        if (in_array($chave, ['mostrar_stock', 'alerta_promocao_activo', 'loja_aberta', 'mostrar_precos'], true)) {
            config_set($chave, isset($_POST[$chave]) ? '1' : '0');
            continue;
        }
        $valor = trim((string) ($_POST[$chave] ?? $defaults[$chave]));
        if ($chave === 'stock_alerta_global') {
            $valor = (string) max(0, (int) $valor);
        }
        if ($chave === 'whatsapp_loja') {
            $valor = preg_replace('/\D+/', '', $valor) ?: APP_WHATSAPP;
        }
        config_set($chave, $valor);
    }
    flash('success', 'Definições do site guardadas.');
    redirect(url('admin/configuracoes.php'));
}

$cfg = [];
foreach ($defaults as $k => $v) {
    $cfg[$k] = config_get($k, $v) ?? $v;
}

$stockBaixo = $pdo->query(
    'SELECT id, nome, stock, stock_alerta FROM produtos WHERE ativo = 1 AND stock > 0 AND stock <= stock_alerta ORDER BY stock ASC LIMIT 12'
)->fetchAll();
$esgotados = (int) $pdo->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1 AND stock < 1')->fetchColumn();

admin_header('Definições', 'configuracoes');
?>

<div class="admin-page-head">
  <div>
    <h2>Definições do site</h2>
    <p>Controle stock visível, alertas de promoção, WhatsApp e estado da loja.</p>
  </div>
  <a class="btn ghost sm" href="<?= url('admin/site.php') ?>">← Gestão do site</a>
</div>

<div class="admin-stack">
  <form class="admin-form-card" method="post">
    <h3>Loja e contacto</h3>
    <div class="admin-form-grid">
      <div class="form-group" style="display:flex;align-items:flex-end">
        <label style="display:flex;align-items:center;gap:0.5rem;margin:0;padding-bottom:0.7rem">
          <input type="checkbox" name="loja_aberta" <?= $cfg['loja_aberta'] === '1' ? 'checked' : '' ?>>
          Loja aberta a receber pedidos
        </label>
      </div>
      <div class="form-group">
        <label for="whatsapp_loja">WhatsApp da loja</label>
        <input id="whatsapp_loja" name="whatsapp_loja" value="<?= e($cfg['whatsapp_loja']) ?>" placeholder="245955000000">
      </div>
      <div class="form-group full">
        <label for="mensagem_loja_fechada">Mensagem se a loja estiver fechada</label>
        <input id="mensagem_loja_fechada" name="mensagem_loja_fechada" value="<?= e($cfg['mensagem_loja_fechada']) ?>">
      </div>
    </div>

    <h3 style="margin-top:1.5rem">Stock e preços</h3>
    <div class="admin-form-grid">
      <div class="form-group" style="display:flex;align-items:flex-end">
        <label style="display:flex;align-items:center;gap:0.5rem;margin:0;padding-bottom:0.7rem">
          <input type="checkbox" name="mostrar_stock" <?= $cfg['mostrar_stock'] === '1' ? 'checked' : '' ?>>
          Mostrar quantidade em stock na loja
        </label>
      </div>
      <div class="form-group" style="display:flex;align-items:flex-end">
        <label style="display:flex;align-items:center;gap:0.5rem;margin:0;padding-bottom:0.7rem">
          <input type="checkbox" name="mostrar_precos" <?= $cfg['mostrar_precos'] === '1' ? 'checked' : '' ?>>
          Mostrar preços na loja
        </label>
      </div>
      <div class="form-group">
        <label for="stock_alerta_global">Alerta global de stock baixo</label>
        <input type="number" id="stock_alerta_global" name="stock_alerta_global" min="0" value="<?= e($cfg['stock_alerta_global']) ?>">
        <small class="muted">Usado como referência; cada produto pode ter o seu próprio limite.</small>
      </div>
    </div>

    <h3 style="margin-top:1.5rem">Alerta de promoção (barra rápida)</h3>
    <div class="admin-form-grid">
      <div class="form-group" style="display:flex;align-items:flex-end">
        <label style="display:flex;align-items:center;gap:0.5rem;margin:0;padding-bottom:0.7rem">
          <input type="checkbox" name="alerta_promocao_activo" <?= $cfg['alerta_promocao_activo'] === '1' ? 'checked' : '' ?>>
          Mostrar alerta global de promoção
        </label>
      </div>
      <div class="form-group full">
        <label for="alerta_promocao_texto">Texto do alerta global</label>
        <input id="alerta_promocao_texto" name="alerta_promocao_texto" value="<?= e($cfg['alerta_promocao_texto']) ?>" placeholder="Ex: Frete especial em Bandim este fim de semana!">
        <small class="muted">Além disto, as promoções activas em <a href="<?= url('admin/promocoes.php') ?>">Promoções</a> também aparecem.</small>
      </div>
    </div>

    <div class="admin-form-actions">
      <button class="btn primary" type="submit">Guardar definições</button>
      <a class="btn ghost" href="<?= url('admin/promocoes.php') ?>">Gerir promoções</a>
    </div>
  </form>

  <div class="admin-list-card">
    <div class="list-head">
      <h3>Stock baixo</h3>
      <span><?= $esgotados ?> esgotado(s)</span>
    </div>
    <?php if (!$stockBaixo && $esgotados === 0): ?>
      <div class="empty" style="border:0">Nenhum alerta de stock de momento.</div>
    <?php else: ?>
      <div class="admin-scroll">
        <table class="admin-table" style="min-width:480px;table-layout:auto">
          <thead>
            <tr>
              <th>Produto</th>
              <th>Stock</th>
              <th>Alerta</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($stockBaixo as $p): ?>
              <tr>
                <td><?= e($p['nome']) ?></td>
                <td><span class="stock-low"><?= (int)$p['stock'] ?></span></td>
                <td>≤ <?= (int)$p['stock_alerta'] ?></td>
                <td><a class="btn ghost sm" href="<?= url('admin/produtos.php?edit=' . (int) $p['id']) ?>">Editar</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($esgotados > 0): ?>
        <p class="muted" style="padding:0.75rem 1rem 0">Há <?= $esgotados ?> produto(s) com stock 0. Reponha em Produtos.</p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?php admin_footer('configuracoes'); ?>
