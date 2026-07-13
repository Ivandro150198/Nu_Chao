<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/icons.php';

$pageTitle = 'Sobre nós — ' . site_nome();

try {
    $zonas = db()->query(
        'SELECT nome, taxa, tempo_estimado FROM zonas_entrega WHERE ativa = 1 ORDER BY nome'
    )->fetchAll();
} catch (Throwable $e) {
    $zonas = [];
}

$missaoItens = linhas_lista(site_cfg('sobre_missao_itens'));

require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero-inner">
    <p class="eyebrow"><?= icon('spark', 'icon inline') ?> <?= e(site_cfg('sobre_eyebrow', 'A nossa história')) ?></p>
    <h1><?= e(site_cfg('sobre_titulo', 'Sobre a ' . site_nome())) ?></h1>
    <p><?= e(site_cfg('sobre_lead')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container about-story">
    <div class="about-panel reveal">
      <h2><?= icon('heart', 'icon inline') ?> <?= e(site_cfg('sobre_quem_titulo', 'Quem somos')) ?></h2>
      <?= nl2p(site_cfg('sobre_quem_texto')) ?>
    </div>
    <div class="about-panel reveal">
      <h2><?= icon('spark', 'icon inline') ?> <?= e(site_cfg('sobre_missao_titulo', 'A nossa missão')) ?></h2>
      <?= nl2p(site_cfg('sobre_missao_texto')) ?>
      <?php if ($missaoItens): ?>
        <ul class="about-list">
          <?php foreach ($missaoItens as $item): ?>
            <li><?= icon('check', 'icon sm') ?> <?= e($item) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <h2><?= icon('check', 'icon inline') ?> <?= e(site_cfg('sobre_passos_titulo', 'Como funciona')) ?></h2>
      <p><?= e(site_cfg('sobre_passos_subtitulo', 'Do site até à sua casa, em poucos passos.')) ?></p>
    </div>
    <div class="steps-grid">
      <article class="step-card reveal">
        <span class="step-num">01</span>
        <h3><?= icon('shirt', 'icon inline') ?> <?= e(site_cfg('sobre_passo1_titulo', 'Escolha')) ?></h3>
        <p class="muted"><?= e(site_cfg('sobre_passo1_texto')) ?></p>
      </article>
      <article class="step-card reveal">
        <span class="step-num">02</span>
        <h3><?= icon('map', 'icon inline') ?> <?= e(site_cfg('sobre_passo2_titulo', 'Encomende')) ?></h3>
        <p class="muted"><?= e(site_cfg('sobre_passo2_texto')) ?></p>
      </article>
      <article class="step-card reveal">
        <span class="step-num">03</span>
        <h3><?= icon('truck', 'icon inline') ?> <?= e(site_cfg('sobre_passo3_titulo', 'Receba')) ?></h3>
        <p class="muted"><?= e(site_cfg('sobre_passo3_texto')) ?></p>
      </article>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <h2><?= icon('truck', 'icon inline') ?> <?= e(site_cfg('sobre_zonas_titulo', 'Zonas de entrega')) ?></h2>
      <p><?= e(site_cfg('sobre_zonas_subtitulo', 'Cobertura actual em Bissau e arredores. Taxas em XOF.')) ?></p>
    </div>
    <?php if ($zonas): ?>
      <div class="zones-grid">
        <?php foreach ($zonas as $z): ?>
          <div class="zone-card reveal">
            <strong><?= e($z['nome']) ?></strong>
            <span class="muted"><?= e($z['tempo_estimado'] ?: 'Sob consulta') ?></span>
            <span class="price"><?= money($z['taxa']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty">As zonas de entrega serão publicadas em breve.</div>
    <?php endif; ?>
  </div>
</section>

<section class="section section-alt">
  <div class="container about-cta">
    <div>
      <h2><?= e(site_cfg('sobre_cta_titulo', 'Pronto para vestir ' . site_nome() . '?')) ?></h2>
      <p class="muted"><?= e(site_cfg('sobre_cta_texto')) ?></p>
    </div>
    <div class="hero-actions">
      <a class="btn primary" href="/No_chao/index.php#produtos"><?= icon('shirt', 'icon') ?> Ver produtos</a>
      <a class="btn ghost" href="/No_chao/loja/contacto.php"><?= icon('phone', 'icon') ?> Contacto</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
