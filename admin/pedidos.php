<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($acao === 'status') {
        $status = (string) ($_POST['status_pedido'] ?? '');
        $allowed = ['PENDENTE', 'CONFIRMADO', 'A_CAMINHO', 'ENTREGUE', 'CANCELADO'];
        if (in_array($status, $allowed, true)) {
            $cur = $pdo->prepare('SELECT status_pedido FROM pedidos WHERE id = ?');
            $cur->execute([$id]);
            $actual = $cur->fetchColumn();

            $pagamento = null;
            if ($status === 'ENTREGUE') {
                $pagamento = 'PAGO_NA_ENTREGA';
            } elseif ($status === 'CANCELADO') {
                $pagamento = 'CANCELADO';
            }

            $pdo->beginTransaction();
            try {
                if ($status === 'CANCELADO' && $actual && $actual !== 'CANCELADO') {
                    devolver_stock_pedido($pdo, $id);
                }
                if ($pagamento) {
                    $pdo->prepare('UPDATE pedidos SET status_pedido = ?, status_pagamento = ? WHERE id = ?')
                        ->execute([$status, $pagamento, $id]);
                } else {
                    $pdo->prepare('UPDATE pedidos SET status_pedido = ? WHERE id = ?')
                        ->execute([$status, $id]);
                }
                $pdo->commit();
                flash('success', 'Estado do pedido actualizado.');
            } catch (Throwable $e) {
                $pdo->rollBack();
                error_log('Admin pedido status: ' . $e->getMessage());
                flash('error', 'Não foi possível actualizar o pedido.');
            }
        }
        redirect('/No_chao/admin/pedidos.php' . ($id ? '?id=' . $id : ''));
    }

    if ($acao === 'atribuir') {
        $entregadorId = (int) ($_POST['entregador_id'] ?? 0);
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE id = ? AND tipo = 'ENTREGADOR' AND ativo = 1 AND aprovado = 1");
        $check->execute([$entregadorId]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE pedidos SET entregador_id = ?, status_pedido = IF(status_pedido = 'PENDENTE', 'CONFIRMADO', status_pedido) WHERE id = ?")
                ->execute([$entregadorId, $id]);
            flash('success', 'Entregador atribuído.');
        }
        redirect('/No_chao/admin/pedidos.php?id=' . $id);
    }
}

$entregadores = $pdo->query(
    "SELECT id, nome, telefone FROM usuarios WHERE tipo = 'ENTREGADOR' AND ativo = 1 AND aprovado = 1 ORDER BY nome"
)->fetchAll();

$filtro = $_GET['status'] ?? '';
$sql = "SELECT p.*, u.nome AS cliente, u.telefone AS cliente_tel, z.nome AS zona,
               e.nome AS entregador_nome
        FROM pedidos p
        JOIN usuarios u ON u.id = p.cliente_id
        JOIN zonas_entrega z ON z.id = p.zona_id
        LEFT JOIN usuarios e ON e.id = p.entregador_id";
$params = [];
if ($filtro && in_array($filtro, ['PENDENTE','CONFIRMADO','A_CAMINHO','ENTREGUE','CANCELADO'], true)) {
    $sql .= ' WHERE p.status_pedido = ?';
    $params[] = $filtro;
}
$sql .= ' ORDER BY p.criado_em DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

$detalhe = null;
$itens = [];
if (isset($_GET['id'])) {
    $ds = $pdo->prepare(
        "SELECT p.*, u.nome AS cliente, u.telefone AS cliente_tel, u.email AS cliente_email,
                z.nome AS zona, e.nome AS entregador_nome
         FROM pedidos p
         JOIN usuarios u ON u.id = p.cliente_id
         JOIN zonas_entrega z ON z.id = p.zona_id
         LEFT JOIN usuarios e ON e.id = p.entregador_id
         WHERE p.id = ?"
    );
    $ds->execute([(int) $_GET['id']]);
    $detalhe = $ds->fetch() ?: null;
    if ($detalhe) {
        $is = $pdo->prepare(
            'SELECT i.*, pr.nome AS produto_nome FROM itens_pedido i
             JOIN produtos pr ON pr.id = i.produto_id WHERE i.pedido_id = ?'
        );
        $is->execute([$detalhe['id']]);
        $itens = $is->fetchAll();
    }
}

admin_header('Pedidos', 'pedidos');
?>

<div class="admin-page-head">
  <div>
    <h2>Pedidos</h2>
    <p>Confirme, atribua entregador e acompanhe o pagamento na entrega.</p>
  </div>
</div>

<div class="filters" style="margin-bottom:1rem">
  <a class="chip <?= $filtro === '' ? 'active' : '' ?>" href="?">Todos</a>
  <?php foreach (['PENDENTE','CONFIRMADO','A_CAMINHO','ENTREGUE','CANCELADO'] as $st): ?>
    <a class="chip <?= $filtro === $st ? 'active' : '' ?>" href="?status=<?= $st ?>"><?= e(status_pedido_label($st)) ?></a>
  <?php endforeach; ?>
</div>

<?php if ($detalhe): ?>
  <div class="admin-stack" style="margin-bottom:1.5rem">
    <div class="admin-form-card">
      <h3>Pedido <?= e($detalhe['codigo']) ?></h3>
      <div class="admin-form-grid">
        <div>
          <p><strong>Cliente:</strong> <?= e($detalhe['cliente']) ?> · <?= e($detalhe['cliente_tel']) ?></p>
          <p><strong>Zona:</strong> <?= e($detalhe['zona']) ?></p>
          <p><strong>Morada:</strong> <?= e($detalhe['endereco']) ?><?= $detalhe['ponto_referencia'] ? ' (' . e($detalhe['ponto_referencia']) . ')' : '' ?></p>
          <p><strong>Pagamento:</strong> <?= e(metodo_pagamento_label($detalhe['metodo_pagamento'])) ?>
            <?php if ($detalhe['precisa_troco_para']): ?> · troco para <?= money($detalhe['precisa_troco_para']) ?><?php endif; ?>
          </p>
        </div>
        <div>
          <p><strong>Estado:</strong> <?= e(status_pedido_label($detalhe['status_pedido'])) ?> · <?= e(status_pagamento_label($detalhe['status_pagamento'])) ?></p>
          <p><strong>Entregador:</strong> <?= e($detalhe['entregador_nome'] ?? 'Não atribuído') ?></p>
          <p><strong>Total:</strong> <?= money($detalhe['valor_total']) ?></p>
          <p><strong>Itens:</strong></p>
          <ul style="margin:0;padding-left:1.1rem">
            <?php foreach ($itens as $i): ?>
              <li><?= e($i['produto_nome']) ?> (<?= e($i['tamanho']) ?>) × <?= (int)$i['quantidade'] ?> — <?= money($i['subtotal']) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>

    <div class="admin-form-card">
      <h3>Acções</h3>
      <div class="admin-form-grid">
        <form method="post">
          <input type="hidden" name="acao" value="atribuir">
          <input type="hidden" name="id" value="<?= (int)$detalhe['id'] ?>">
          <div class="form-group">
            <label>Atribuir entregador</label>
            <select name="entregador_id" required>
              <option value="">Seleccione</option>
              <?php foreach ($entregadores as $e): ?>
                <option value="<?= (int)$e['id'] ?>" <?= ((int)$detalhe['entregador_id'] === (int)$e['id']) ? 'selected' : '' ?>>
                  <?= e($e['nome']) ?> (<?= e($e['telefone']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn primary" type="submit">Atribuir</button>
        </form>

        <form method="post">
          <input type="hidden" name="acao" value="status">
          <input type="hidden" name="id" value="<?= (int)$detalhe['id'] ?>">
          <div class="form-group">
            <label>Alterar estado</label>
            <select name="status_pedido">
              <?php foreach (['PENDENTE','CONFIRMADO','A_CAMINHO','ENTREGUE','CANCELADO'] as $st): ?>
                <option value="<?= $st ?>" <?= $detalhe['status_pedido'] === $st ? 'selected' : '' ?>><?= e(status_pedido_label($st)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn ghost" type="submit">Actualizar estado</button>
        </form>
      </div>
      <p class="help"><a href="/No_chao/admin/pedidos.php">← Voltar à lista</a></p>
    </div>
  </div>
<?php endif; ?>

<div class="admin-list-card">
  <div class="list-head">
    <h3>Lista de pedidos</h3>
    <span><?= count($pedidos) ?> resultados</span>
  </div>
  <div class="admin-scroll">
    <table class="admin-table" style="table-layout:auto">
      <thead>
        <tr>
          <th>Código</th>
          <th>Cliente</th>
          <th>Zona</th>
          <th>Total</th>
          <th>Estado</th>
          <th>Entregador</th>
          <th>Acções</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pedidos as $p): ?>
          <tr>
            <td data-label="Código"><strong><?= e($p['codigo']) ?></strong></td>
            <td data-label="Cliente"><?= e($p['cliente']) ?><br><span class="muted"><?= e($p['cliente_tel']) ?></span></td>
            <td data-label="Zona"><?= e($p['zona']) ?></td>
            <td data-label="Total"><?= money($p['valor_total']) ?></td>
            <td data-label="Estado">
              <span class="badge-status st-<?= e($p['status_pedido']) ?>"><?= e(status_pedido_label($p['status_pedido'])) ?></span><br>
              <span class="muted"><?= e(status_pagamento_label($p['status_pagamento'])) ?></span>
            </td>
            <td data-label="Entregador"><?= e($p['entregador_nome'] ?? '—') ?></td>
            <td data-label="Acções" class="col-acoes">
              <div class="acoes-cell">
                <a class="btn ghost sm" href="?id=<?= (int)$p['id'] ?>">Gerir</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$pedidos): ?>
          <tr><td colspan="7" class="muted">Sem pedidos neste filtro.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php admin_footer('pedidos'); ?>
