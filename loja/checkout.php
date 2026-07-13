<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$user = require_login();
if ($user['tipo'] !== 'CLIENTE' && $user['tipo'] !== 'ADMIN') {
    flash('error', 'Apenas clientes podem fazer pedidos nesta área.');
    redirect('/No_chao/index.php');
}

$items = cart();
if (!$items) {
    flash('error', 'O carrinho está vazio.');
    redirect('/No_chao/loja/carrinho.php');
}

$zonas = db()->query('SELECT * FROM zonas_entrega WHERE ativa = 1 ORDER BY nome')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    if (!config_bool('loja_aberta', true)) {
        $error = config_get('mensagem_loja_fechada', 'Loja temporariamente fechada.') ?? 'Loja fechada.';
    } else {
    $zonaId = (int) ($_POST['zona_id'] ?? 0);
    $endereco = trim((string) ($_POST['endereco'] ?? ''));
    $ponto = trim((string) ($_POST['ponto_referencia'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? $user['telefone']));
    $metodo = (string) ($_POST['metodo_pagamento'] ?? 'DINHEIRO');
    $troco = $_POST['precisa_troco_para'] !== '' ? (float) $_POST['precisa_troco_para'] : null;
    $obs = trim((string) ($_POST['observacoes'] ?? ''));

    $zonaStmt = db()->prepare('SELECT * FROM zonas_entrega WHERE id = ? AND ativa = 1');
    $zonaStmt->execute([$zonaId]);
    $zona = $zonaStmt->fetch();

    $recalc = cart_revalidar();
    $items = $recalc['items'];

    if (!$recalc['ok']) {
        $error = $recalc['error'] ?? 'Carrinho inválido.';
    } elseif (!$zona || $endereco === '' || $telefone === '' || mb_strlen($endereco) > 500) {
        $error = 'Preencha zona, morada e telefone.';
    } elseif (!in_array($metodo, ['DINHEIRO', 'TPA', 'MOBILE_MONEY', 'Orange Money'], true)) {
        $error = 'Método de pagamento inválido.';
    } else {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $totalProdutos = (float) $recalc['total'];
            $taxa = (float) $zona['taxa'];
            $valorTotal = $totalProdutos + $taxa;
            $codigo = gerar_codigo_pedido();

            $ins = $pdo->prepare(
                'INSERT INTO pedidos
                (codigo, cliente_id, zona_id, endereco, ponto_referencia, telefone_contacto,
                 metodo_pagamento, precisa_troco_para, total_produtos, taxa_entrega, valor_total, observacoes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $ins->execute([
                $codigo,
                $user['id'],
                $zonaId,
                $endereco,
                $ponto ?: null,
                $telefone,
                $metodo,
                $metodo === 'DINHEIRO' ? $troco : null,
                $totalProdutos,
                $taxa,
                $valorTotal,
                $obs ?: null,
            ]);
            $pedidoId = (int) $pdo->lastInsertId();
            if ($pedidoId < 1) {
                throw new RuntimeException('Falha ao criar pedido.');
            }

            $itemIns = $pdo->prepare(
                'INSERT INTO itens_pedido (pedido_id, produto_id, tamanho, quantidade, preco_unitario, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stockUpd = $pdo->prepare('UPDATE produtos SET stock = stock - ? WHERE id = ? AND stock >= ?');

            foreach ($items as $item) {
                $sub = (float) $item['preco'] * (int) $item['quantidade'];
                $itemIns->execute([
                    $pedidoId,
                    $item['produto_id'],
                    $item['tamanho'],
                    $item['quantidade'],
                    $item['preco'],
                    $sub,
                ]);
                $stockUpd->execute([$item['quantidade'], $item['produto_id'], $item['quantidade']]);
                if ($stockUpd->rowCount() === 0) {
                    throw new RuntimeException('Stock insuficiente para: ' . $item['nome']);
                }
            }

            $pdo->commit();
            $_SESSION['cart'] = [];

            $mensagem = montar_mensagem_pedido(
                [
                    'codigo' => $codigo,
                    'endereco' => $endereco,
                    'ponto_referencia' => $ponto,
                    'telefone_contacto' => $telefone,
                    'metodo_pagamento' => $metodo,
                    'precisa_troco_para' => $metodo === 'DINHEIRO' ? $troco : null,
                    'total_produtos' => $totalProdutos,
                    'taxa_entrega' => $taxa,
                    'valor_total' => $valorTotal,
                    'observacoes' => $obs,
                ],
                $items,
                (string) $zona['nome'],
                [
                    'nome' => $user['nome'],
                    'telefone' => $telefone,
                ]
            );

            $_SESSION['ultimo_pedido_whatsapp'] = [
                'codigo' => $codigo,
                'id' => $pedidoId,
                'url' => whatsapp_url($mensagem),
            ];
            flash('success', "Pedido {$codigo} criado! A abrir o WhatsApp para enviar o pedido…");
            redirect('/No_chao/conta/pedido_enviado.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Nu Chao checkout: ' . $e->getMessage());
            $error = 'Não foi possível criar o pedido. Verifique o stock e tente novamente.';
        }
    }
    } // loja aberta
}

$pageTitle = 'Checkout — Nu Chao';
require __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="container">
    <div class="section-head">
      <h2>Checkout · Pagamento na entrega</h2>
      <p>Indique a zona e a morada. O pagamento é feito ao receber o pedido.</p>
    </div>

    <?php if ($error): ?>
      <div class="flash flash-error" style="margin-bottom:1rem"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="cart-layout">
      <form method="post" class="panel">
        <?= csrf_field() ?>
        <h3>Dados de entrega</h3>
        <div class="form-group">
          <label for="zona_id">Zona de entrega</label>
          <select id="zona_id" name="zona_id" required>
            <option value="">Seleccione a zona</option>
            <?php foreach ($zonas as $z): ?>
              <option value="<?= (int) $z['id'] ?>"
                data-taxa="<?= (float) $z['taxa'] ?>"
                <?= ((int)($_POST['zona_id'] ?? 0) === (int)$z['id']) ? 'selected' : '' ?>>
                <?= e($z['nome']) ?> — <?= money($z['taxa']) ?> (<?= e($z['tempo_estimado']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="endereco">Morada / rua</label>
          <input id="endereco" name="endereco" required value="<?= e($_POST['endereco'] ?? '') ?>" placeholder="Ex: perto do mercado de Bandim">
        </div>
        <div class="form-group">
          <label for="ponto_referencia">Ponto de referência</label>
          <input id="ponto_referencia" name="ponto_referencia" value="<?= e($_POST['ponto_referencia'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="telefone">Telefone de contacto</label>
          <input id="telefone" name="telefone" required value="<?= e($_POST['telefone'] ?? $user['telefone']) ?>">
        </div>
        <div class="form-group">
          <label for="metodo_pagamento">Como pretende pagar na entrega?</label>
          <select id="metodo_pagamento" name="metodo_pagamento">
            <option value="DINHEIRO">Dinheiro</option>
            <option value="ORANGE_MONEY">Orange Money</option>
            <option value="MOBILE_MONEY">Mobile Money</option>
          </select>
        </div>
        <div class="form-group" id="trocoGroup">
          <label for="precisa_troco_para">Precisa de troco para (XOF)</label>
          <input type="number" min="0" step="0.01" id="precisa_troco_para" name="precisa_troco_para" value="<?= e((string)($_POST['precisa_troco_para'] ?? '')) ?>" placeholder="Ex: 10000 ou 9999.50">
        </div>
        <div class="form-group">
          <label for="observacoes">Observações</label>
          <textarea id="observacoes" name="observacoes" rows="3"><?= e($_POST['observacoes'] ?? '') ?></textarea>
        </div>
        <button class="btn primary block" type="submit">Confirmar e enviar no WhatsApp</button>
        <p class="help" style="margin-top:0.75rem">O pedido fica registado no site e a mensagem abre no WhatsApp da loja.</p>
      </form>

      <aside class="summary-box">
        <h3>O seu pedido</h3>
        <?php foreach ($items as $item): ?>
          <div class="summary-line">
            <span><?= e($item['nome']) ?> × <?= (int)$item['quantidade'] ?></span>
            <span><?= money($item['preco'] * $item['quantidade']) ?></span>
          </div>
        <?php endforeach; ?>
        <div class="summary-line"><span>Produtos</span><span><?= money(cart_total()) ?></span></div>
        <div class="summary-line"><span>Entrega</span><span id="taxaLabel">—</span></div>
        <div class="summary-line total"><span>Total a pagar na entrega</span><span id="totalLabel"><?= money(cart_total()) ?></span></div>
      </aside>
    </div>
  </div>
</section>

<script>
(() => {
  const zona = document.getElementById('zona_id');
  const taxaLabel = document.getElementById('taxaLabel');
  const totalLabel = document.getElementById('totalLabel');
  const produtos = <?= json_encode(cart_total()) ?>;
  const metodo = document.getElementById('metodo_pagamento');
  const trocoGroup = document.getElementById('trocoGroup');

  function fmt(n) {
    return new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(n) + ' XOF';
  }
  function update() {
    const opt = zona.options[zona.selectedIndex];
    const taxa = opt && opt.dataset.taxa ? Number(opt.dataset.taxa) : 0;
    taxaLabel.textContent = zona.value ? fmt(taxa) : '—';
    totalLabel.textContent = fmt(produtos + taxa);
  }
  function toggleTroco() {
    trocoGroup.style.display = metodo.value === 'DINHEIRO' ? '' : 'none';
  }
  zona.addEventListener('change', update);
  metodo.addEventListener('change', toggleTroco);
  update();
  toggleTroco();
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
