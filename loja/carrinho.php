<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Carrinho — ' . site_nome();
$items = cart();
$total = cart_total();
require __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="container">
    <div class="section-head">
      <h2>Carrinho</h2>
      <p>Revise os itens antes de finalizar com pagamento na entrega.</p>
    </div>

    <?php if (!$items): ?>
      <div class="empty">
        <p>O carrinho está vazio.</p>
        <p><a class="btn primary" href="<?= url('index.php#produtos') ?>">Ver produtos</a></p>
      </div>
    <?php else: ?>
      <div class="cart-layout">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Produto</th>
                <th>Tamanho</th>
                <th>Qtd</th>
                <th>Subtotal</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $key => $item): ?>
                <tr>
                  <td data-label="Produto">
                    <strong><?= e($item['nome']) ?></strong><br>
                    <span class="muted"><?= money($item['preco']) ?></span>
                  </td>
                  <td data-label="Tamanho"><?= e($item['tamanho']) ?></td>
                  <td data-label="Quantidade">
                    <form method="post" action="<?= url('loja/carrinho_acao.php') ?>" class="qty-control">
                      <?= csrf_field() ?>
                      <input type="hidden" name="acao" value="update">
                      <input type="hidden" name="key" value="<?= e($key) ?>">
                      <button type="submit" name="quantidade" value="<?= max(0, (int)$item['quantidade'] - 1) ?>" aria-label="Diminuir">−</button>
                      <span><?= (int) $item['quantidade'] ?></span>
                      <button type="submit" name="quantidade" value="<?= (int)$item['quantidade'] + 1 ?>" aria-label="Aumentar">+</button>
                    </form>
                  </td>
                  <td data-label="Subtotal"><?= money($item['preco'] * $item['quantidade']) ?></td>
                  <td data-label="">
                    <form method="post" action="<?= url('loja/carrinho_acao.php') ?>">
                      <?= csrf_field() ?>
                      <input type="hidden" name="acao" value="remove">
                      <input type="hidden" name="key" value="<?= e($key) ?>">
                      <button class="btn danger sm" type="submit">Remover</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <aside class="summary-box">
          <h3>Resumo</h3>
          <div class="summary-line"><span>Produtos</span><span><?= money($total) ?></span></div>
          <div class="summary-line"><span>Entrega</span><span>Calculada no checkout</span></div>
          <div class="summary-line total"><span>Subtotal</span><span><?= money($total) ?></span></div>
          <p class="muted" style="font-size:0.9rem">Pagamento apenas na entrega (dinheiro, Orange Money ou mobile money).</p>
          <a class="btn primary block" href="<?= url('loja/checkout.php') ?>">Finalizar pedido</a>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
