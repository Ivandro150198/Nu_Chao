<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/icons.php';

ensure_produto_imagens_table();

$id = (int) ($_GET['id'] ?? 0);
$mostrarPrecos = config_bool('mostrar_precos', true);
$mostrarStock = config_bool('mostrar_stock', true);
$lojaAberta = config_bool('loja_aberta', true);

$stmt = db()->prepare('SELECT * FROM produtos WHERE id = ? AND ativo = 1 LIMIT 1');
$stmt->execute([$id]);
$produto = $stmt->fetch();

if (!$produto) {
    flash('error', 'Produto não encontrado.');
    redirect('/No_chao/index.php#produtos');
}

$imagens = produto_imagens((int) $produto['id']);
if (!$imagens && !empty($produto['imagem'])) {
    $imagens = [[
        'id' => 0,
        'produto_id' => (int) $produto['id'],
        'ficheiro' => (string) $produto['imagem'],
        'ordem' => 0,
        'url' => produto_imagem_url((string) $produto['imagem']),
    ]];
}

$tamanhos = array_filter(array_map('trim', explode(',', (string) $produto['tamanhos'])));
if (!$tamanhos) {
    $tamanhos = ['Único'];
}
$emPromo = produto_em_promocao($produto);
$stock = (int) $produto['stock'];
$stockBaixo = produto_stock_baixo($produto);
$pageTitle = $produto['nome'] . ' — ' . site_nome();

require __DIR__ . '/../includes/header.php';
?>

<section class="section product-detail-page">
  <div class="container">
    <p class="breadcrumb">
      <a href="/No_chao/index.php#produtos"><?= icon('chevron-left', 'icon sm') ?> Voltar aos produtos</a>
    </p>

    <div class="product-detail">
      <div class="product-gallery" id="productGallery" data-gallery>
        <div class="product-gallery-main">
          <?php if ($imagens): ?>
            <img id="galleryMain" src="<?= e($imagens[0]['url']) ?>" alt="<?= e($produto['nome']) ?>">
          <?php else: ?>
            <div class="placeholder product-gallery-empty">NC</div>
          <?php endif; ?>
          <?php if ($emPromo): ?>
            <span class="badge badge-promo">−<?= produto_desconto_percent($produto) ?>%</span>
          <?php endif; ?>
        </div>
        <?php if (count($imagens) > 1): ?>
          <div class="product-gallery-thumbs" role="tablist" aria-label="Imagens do produto">
            <?php foreach ($imagens as $i => $img): ?>
              <button
                type="button"
                class="gallery-thumb<?= $i === 0 ? ' is-active' : '' ?>"
                data-src="<?= e($img['url']) ?>"
                aria-label="Imagem <?= $i + 1 ?>"
              >
                <img src="<?= e($img['url']) ?>" alt="" loading="lazy">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="product-detail-info">
        <span class="pill pill-cat"><?= $produto['categoria'] === 'ROUPA' ? 'Roupa' : 'Acessório' ?></span>
        <h1><?= e($produto['nome']) ?></h1>

        <?php if ($mostrarPrecos): ?>
          <div class="price-row product-detail-price">
            <?php if ($emPromo): ?>
              <span class="price-old"><?= money($produto['preco']) ?></span>
              <span class="price price-promo"><?= money($produto['preco_promocional']) ?></span>
            <?php else: ?>
              <span class="price"><?= money($produto['preco']) ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if ($mostrarStock): ?>
          <div class="stock-row <?= $stock < 1 ? 'is-out' : ($stockBaixo ? 'is-low' : 'is-ok') ?>">
            <?php if ($stock < 1): ?>
              Esgotado
            <?php elseif ($stockBaixo): ?>
              Só <?= $stock ?> em stock
            <?php else: ?>
              <?= $stock ?> em stock
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($produto['descricao'])): ?>
          <div class="product-detail-desc">
            <h2>Detalhes</h2>
            <p><?= nl2br(e((string) $produto['descricao'])) ?></p>
          </div>
        <?php endif; ?>

        <form class="product-detail-actions" method="post" action="/No_chao/loja/carrinho_acao.php">
          <?= csrf_field() ?>
          <input type="hidden" name="acao" value="add">
          <input type="hidden" name="produto_id" value="<?= (int) $produto['id'] ?>">
          <input type="hidden" name="redirect" value="/No_chao/loja/produto.php?id=<?= (int) $produto['id'] ?>">
          <label for="tamanho">Tamanho</label>
          <div class="product-actions">
            <select id="tamanho" name="tamanho" aria-label="Tamanho">
              <?php foreach ($tamanhos as $t): ?>
                <option value="<?= e($t) ?>"><?= e($t) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn primary" type="submit" <?= ($stock < 1 || !$lojaAberta) ? 'disabled' : '' ?>>
              <?= icon('cart', 'icon') ?>
              <?= $stock < 1 ? 'Esgotado' : (!$lojaAberta ? 'Indisponível' : 'Adicionar ao carrinho') ?>
            </button>
          </div>
        </form>

        <p class="muted product-detail-note">
          <?= icon('check', 'icon inline sm') ?> Pagamento na entrega · Entregas em Bissau
        </p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
