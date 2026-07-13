</main>
<footer class="site-footer reveal">
  <div class="container footer-grid">
    <div>
      <strong><?= e(site_nome()) ?></strong>
      <p><?= e(site_cfg('footer_texto', site_cfg('site_tagline', 'Roupas e acessórios inspirados na Guiné-Bissau. Pagamento na entrega em Bissau e arredores.'))) ?></p>
    </div>
    <div>
      <strong>Contacto</strong>
      <p><a href="https://wa.me/<?= e(whatsapp_numero()) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon inline') ?> WhatsApp</a></p>
      <p><a href="/No_chao/loja/contacto.php"><?= icon('mail', 'icon inline') ?> Página de contacto</a></p>
      <p><?= icon('map', 'icon inline') ?> <?= e(site_cfg('site_localizacao', 'Bissau, Guiné-Bissau')) ?></p>
    </div>
    <div>
      <strong>Navegação</strong>
      <p><a href="/No_chao/index.php#produtos">Produtos</a></p>
      <p><a href="/No_chao/loja/sobre.php">Sobre nós</a></p>
      <p><a href="/No_chao/loja/contacto.php">Contacto</a></p>
      <?php $footerUser = $user ?? current_user(); ?>
      <?php if ($footerUser): ?>
        <p><a href="/No_chao/conta/perfil.php">O meu perfil</a></p>
        <?php if (($footerUser['tipo'] ?? '') === 'CLIENTE'): ?>
          <p><a href="/No_chao/conta/meus_pedidos.php">Os meus pedidos</a></p>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</footer>

<?php
$scriptName = $scriptName ?? basename($_SERVER['SCRIPT_NAME'] ?? '');
$user = $user ?? current_user();
?>
<nav class="mobile-bar" aria-label="Navegação rápida">
  <a href="/No_chao/index.php" class="<?= $scriptName === 'index.php' ? 'is-active' : '' ?>">
    <?= icon('home', 'icon') ?><span>Início</span>
  </a>
  <a href="/No_chao/index.php#produtos">
    <?= icon('shirt', 'icon') ?><span>Produtos</span>
  </a>
  <a href="/No_chao/loja/carrinho.php" class="<?= $scriptName === 'carrinho.php' ? 'is-active' : '' ?>">
    <?= icon('cart', 'icon') ?><span>Carrinho</span>
  </a>
  <a href="/No_chao/loja/contacto.php" class="<?= $scriptName === 'contacto.php' ? 'is-active' : '' ?>">
    <?= icon('phone', 'icon') ?><span>Contacto</span>
  </a>
  <?php if ($user): ?>
    <?php if (($user['tipo'] ?? '') === 'CLIENTE'): ?>
      <a href="/No_chao/conta/meus_pedidos.php" class="<?= in_array($scriptName, ['meus_pedidos.php', 'pedido.php'], true) ? 'is-active' : '' ?>">
        <?= icon('bag', 'icon') ?><span>Pedidos</span>
      </a>
    <?php else: ?>
      <a href="/No_chao/conta/perfil.php" class="<?= in_array($scriptName, ['perfil.php', 'meus_pedidos.php', 'pedido.php'], true) ? 'is-active' : '' ?>">
        <?= icon('user', 'icon') ?><span>Conta</span>
      </a>
    <?php endif; ?>
  <?php else: ?>
    <a href="/No_chao/auth/login.php" class="<?= in_array($scriptName, ['login.php', 'registar.php'], true) ? 'is-active' : '' ?>">
      <?= icon('login', 'icon') ?><span>Entrar</span>
    </a>
  <?php endif; ?>
</nav>

<?php
if (!current_user()) {
    require __DIR__ . '/modal_registo.php';
}
?>

<script src="/No_chao/assets/js/app.js"></script>
</body>
</html>
