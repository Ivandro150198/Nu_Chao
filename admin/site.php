<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

admin_header('Gestão do site', 'site');
?>

<div class="admin-page-head">
  <div>
    <h2>Gestão do site</h2>
    <p>Controle tudo o que aparece na loja: hero, textos, contacto, zonas, produtos e promoções.</p>
  </div>
  <a class="btn ghost sm" href="/No_chao/index.php" target="_blank" rel="noopener">Ver loja</a>
</div>

<div class="admin-cms-grid">
  <a class="admin-cms-card" href="/No_chao/admin/hero.php">
    <span class="admin-cms-ico"><?= icon('spark') ?></span>
    <strong>Hero / Banner</strong>
    <span>Slides do topo da página inicial</span>
  </a>
  <a class="admin-cms-card" href="/No_chao/admin/conteudo.php">
    <span class="admin-cms-ico"><?= icon('info') ?></span>
    <strong>Conteúdos &amp; textos</strong>
    <span>Início, Sobre, rodapé e secções</span>
  </a>
  <a class="admin-cms-card" href="/No_chao/admin/info.php">
    <span class="admin-cms-ico"><?= icon('phone') ?></span>
    <strong>Informações &amp; contacto</strong>
    <span>Nome, WhatsApp, horário, localização</span>
  </a>
  <a class="admin-cms-card" href="/No_chao/admin/zonas.php">
    <span class="admin-cms-ico"><?= icon('map') ?></span>
    <strong>Zonas de entrega</strong>
    <span>Taxas, tempos e cobertura em Bissau</span>
  </a>
  <a class="admin-cms-card" href="/No_chao/admin/produtos.php">
    <span class="admin-cms-ico"><?= icon('shirt') ?></span>
    <strong>Produtos &amp; stock</strong>
    <span>Catálogo, preços e promoções por peça</span>
  </a>
  <a class="admin-cms-card" href="/No_chao/admin/promocoes.php">
    <span class="admin-cms-ico"><?= icon('tag') ?></span>
    <strong>Promoções &amp; alertas</strong>
    <span>Barras de promoção no topo da loja</span>
  </a>
  <a class="admin-cms-card" href="/No_chao/admin/configuracoes.php">
    <span class="admin-cms-ico"><?= icon('settings') ?></span>
    <strong>Opções da loja</strong>
    <span>Stock visível, loja aberta, alertas</span>
  </a>
  <a class="admin-cms-card" href="/No_chao/admin/pedidos.php">
    <span class="admin-cms-ico"><?= icon('bag') ?></span>
    <strong>Pedidos</strong>
    <span>Gestão de encomendas COD</span>
  </a>
  <a class="admin-cms-card" href="/No_chao/admin/entregadores.php">
    <span class="admin-cms-ico"><?= icon('truck') ?></span>
    <strong>Entregadores</strong>
    <span>Aprovar e gerir entregadores</span>
  </a>
</div>

<?php admin_footer('site'); ?>
