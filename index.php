<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';

$pageTitle = site_cfg('site_titulo_home', site_nome() . ' — Moda na Guiné-Bissau');

$mostrarStock = config_bool('mostrar_stock', true);
$mostrarPrecos = config_bool('mostrar_precos', true);
$lojaAberta = config_bool('loja_aberta', true);

try {
    ensure_produto_imagens_table();
    $produtos = db()->query(
        "SELECT * FROM produtos WHERE ativo = 1 ORDER BY em_promocao DESC, categoria, nome"
    )->fetchAll();
    $countRoupa = (int) db()->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1 AND categoria = 'ROUPA'")->fetchColumn();
    $countAcess = (int) db()->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1 AND categoria = 'ACESSORIO'")->fetchColumn();
    $countPromo = count(array_filter($produtos, 'produto_em_promocao'));
    $imgCounts = [];
    try {
        foreach (db()->query('SELECT produto_id, COUNT(*) AS c FROM produto_imagens GROUP BY produto_id') as $row) {
            $imgCounts[(int) $row['produto_id']] = (int) $row['c'];
        }
    } catch (Throwable $e) {
        $imgCounts = [];
    }
} catch (Throwable $e) {
    $produtos = [];
    $countRoupa = 0;
    $countAcess = 0;
    $countPromo = 0;
    $imgCounts = [];
    $dbError = true;
}

$heroSlides = hero_slides_activos();

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($dbError)): ?>
  <div class="flash flash-error"><div class="container">Base de dados indisponível. Abra <a href="/No_chao/install.php">install.php</a>.</div></div>
<?php endif; ?>

<section class="hero-carousel" id="heroCarousel" aria-label="Destaques <?= e(site_nome()) ?>">
  <div class="hero-track">
    <?php foreach ($heroSlides as $i => $slide): ?>
      <article class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>" data-slide="<?= $i ?>">
        <div class="hero-slide-bg" style="--hero-img:url('<?= e($slide['image_url'] ?? hero_image_url($slide['image'] ?? '')) ?>')"></div>
        <div class="container hero-inner">
          <h1><?= e($slide['title']) ?></h1>
          <p><?= e($slide['text']) ?></p>
          <div class="hero-actions">
            <a class="btn primary" href="<?= e($slide['cta_href'] ?: '#produtos') ?>"><?= icon('shirt', 'icon') ?> <?= e($slide['cta']) ?></a>
          </div>
          <?php if (!empty($slide['note'])): ?>
            <div class="hero-note"><?= icon('check', 'icon inline') ?> <?= e($slide['note']) ?></div>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <button class="hero-nav prev" type="button" aria-label="Slide anterior"><?= icon('chevron-left') ?></button>
  <button class="hero-nav next" type="button" aria-label="Próximo slide"><?= icon('chevron-right') ?></button>
  <div class="hero-dots" role="tablist" aria-label="Slides">
    <?php foreach ($heroSlides as $i => $_): ?>
      <button type="button" class="hero-dot<?= $i === 0 ? ' is-active' : '' ?>" data-goto="<?= $i ?>" aria-label="Ir para slide <?= $i + 1 ?>"></button>
    <?php endforeach; ?>
  </div>
</section>

<section class="section" id="produtos">
  <div class="container">
    <div class="section-head">
      <h2><?= icon('shirt', 'icon inline') ?> <?= e(site_cfg('colecao_titulo', site_cfg('produtos_titulo', 'Coleção'))) ?></h2>
      <p><?= e(site_cfg('colecao_subtitulo', site_cfg('produtos_subtitulo', 'Escolha a categoria e adicione ao carrinho.'))) ?></p>
    </div>

    <div class="filters cat-pills" id="catCarousel" role="toolbar" aria-label="Filtrar produtos">
      <div class="cat-track">
        <button type="button" class="cat-pill is-active" data-filter="TODOS">
          <span class="cat-ico"><?= icon('spark', 'icon sm') ?></span>
          <strong>Tudo</strong>
          <em><?= count($produtos) ?></em>
        </button>
        <button type="button" class="cat-pill" data-filter="ROUPA">
          <span class="cat-ico"><?= icon('shirt', 'icon sm') ?></span>
          <strong>Roupas</strong>
          <em><?= $countRoupa ?></em>
        </button>
        <button type="button" class="cat-pill" data-filter="ACESSORIO">
          <span class="cat-ico"><?= icon('bag', 'icon sm') ?></span>
          <strong>Acessórios</strong>
          <em><?= $countAcess ?></em>
        </button>
        <button type="button" class="cat-pill" data-filter="PROMO">
          <span class="cat-ico"><?= icon('tag', 'icon sm') ?></span>
          <strong>Promoções</strong>
          <em><?= $countPromo ?></em>
        </button>
      </div>
      <label class="search-field">
        <?= icon('search', 'icon sm') ?>
        <input class="form-control" type="search" id="productSearch" placeholder="Pesquisar..." aria-label="Pesquisar produtos">
      </label>
    </div>

    <?php if (!$lojaAberta): ?>
      <div class="store-closed-banner"><?= icon('bell', 'icon inline') ?> <?= e(config_get('mensagem_loja_fechada', 'Loja temporariamente fechada.') ?? '') ?></div>
    <?php endif; ?>

    <?php if (!$produtos): ?>
      <div class="empty">Ainda não há produtos. O administrador pode adicioná-los na área de gestão.</div>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($produtos as $p): ?>
          <?php
            $tamanhos = array_filter(array_map('trim', explode(',', (string) $p['tamanhos'])));
            if (!$tamanhos) { $tamanhos = ['Único']; }
            $emPromo = produto_em_promocao($p);
            $stock = (int) $p['stock'];
            $stockBaixo = produto_stock_baixo($p);
            $capa = produto_imagem_capa($p);
            $nImg = $imgCounts[(int) $p['id']] ?? ($capa ? 1 : 0);
          ?>
          <article class="product-card<?= $emPromo ? ' is-promo' : '' ?>" data-categoria="<?= e($p['categoria']) ?>" data-promo="<?= $emPromo ? '1' : '0' ?>">
            <a class="product-media" href="/No_chao/loja/produto.php?id=<?= (int)$p['id'] ?>">
              <?php if ($emPromo): ?>
                <span class="badge badge-promo">−<?= produto_desconto_percent($p) ?>%</span>
              <?php endif; ?>
              <?php if ($capa): ?>
                <img src="<?= e(produto_imagem_url($capa)) ?>" alt="<?= e($p['nome']) ?>" loading="lazy">
              <?php else: ?>
                <div class="placeholder">NC</div>
              <?php endif; ?>
              <?php if ($nImg > 1): ?>
                <span class="badge badge-imgs"><?= $nImg ?></span>
              <?php endif; ?>
            </a>
            <div class="product-body">
              <h3><a href="/No_chao/loja/produto.php?id=<?= (int)$p['id'] ?>"><?= e($p['nome']) ?></a></h3>
              <?php if ($mostrarPrecos): ?>
                <div class="price-row">
                  <?php if ($emPromo): ?>
                    <span class="price-old"><?= money($p['preco']) ?></span>
                    <span class="price price-promo"><?= money($p['preco_promocional']) ?></span>
                  <?php else: ?>
                    <span class="price"><?= money($p['preco']) ?></span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              <?php if ($mostrarStock && ($stock < 1 || $stockBaixo)): ?>
                <div class="stock-row <?= $stock < 1 ? 'is-out' : 'is-low' ?>">
                  <?= $stock < 1 ? 'Esgotado' : 'Só ' . $stock . ' em stock' ?>
                </div>
              <?php endif; ?>
              <form class="product-actions" method="post" action="/No_chao/loja/carrinho_acao.php">
                <input type="hidden" name="acao" value="add">
                <input type="hidden" name="produto_id" value="<?= (int) $p['id'] ?>">
                <select name="tamanho" aria-label="Tamanho">
                  <?php foreach ($tamanhos as $t): ?>
                    <option value="<?= e($t) ?>"><?= e($t) ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="btn primary sm" type="submit" <?= ($stock < 1 || !$lojaAberta) ? 'disabled' : '' ?>>
                  <?= icon('cart', 'icon sm') ?>
                  <?= $stock < 1 ? 'Esgotado' : (!$lojaAberta ? 'Indisponível' : 'Adicionar') ?>
                </button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
