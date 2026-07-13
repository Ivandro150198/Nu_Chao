<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/icons.php';

$user = current_user();
$pageTitle = $pageTitle ?? APP_NAME;
$cartCount = cart_count();
$flash = get_flash();
$scriptName = basename($_SERVER['SCRIPT_NAME'] ?? '');
$userInitial = $user ? strtoupper(mb_substr($user['nome'], 0, 1)) : '';
?>
<!DOCTYPE html>
<html lang="pt" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= e($pageTitle) ?></title>
  <?= csrf_meta() ?>
  <meta name="theme-color" content="#0f2e1f" id="metaThemeColor">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <link rel="icon" type="image/png" href="/No_chao/assets/logo-nc.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/No_chao/assets/css/style.css">
  <link rel="stylesheet" href="/No_chao/assets/css/responsive.css">
  <script>
    (function () {
      try {
        var t = localStorage.getItem('nu_chao_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', t);
      } catch (e) {}
    })();
  </script>
</head>
<body>
<header class="topbar">
  <div class="container topbar-inner">
    <a class="brand" href="/No_chao/index.php">
      <img src="/No_chao/assets/logo-nc.png" alt="<?= e(site_nome()) ?>" class="brand-logo">
      <span class="brand-text"><?= e(site_nome()) ?></span>
    </a>

    <nav class="main-nav" id="mainNav" aria-label="Menu principal">
      <a href="/No_chao/index.php#produtos"><?= icon('shirt', 'icon nav-ico') ?><span>Produtos</span></a>
      <a href="/No_chao/loja/sobre.php"><?= icon('info', 'icon nav-ico') ?><span>Sobre</span></a>
      <a href="/No_chao/loja/contacto.php"><?= icon('phone', 'icon nav-ico') ?><span>Contacto</span></a>
      <?php if ($user && $user['tipo'] === 'ADMIN'): ?>
        <a href="/No_chao/admin/site.php"><?= icon('package', 'icon nav-ico') ?><span>Gestão</span></a>
      <?php elseif ($user && $user['tipo'] === 'ENTREGADOR'): ?>
        <a href="/No_chao/entregador/"><?= icon('truck', 'icon nav-ico') ?><span>Entregas</span></a>
      <?php endif; ?>
      <?php if ($user): ?>
        <?php if (in_array($user['tipo'], ['CLIENTE', 'ADMIN'], true)): ?>
          <a class="nav-auth-mobile" href="/No_chao/conta/meus_pedidos.php"><?= icon('bag', 'icon nav-ico') ?><span>Os meus pedidos</span></a>
        <?php endif; ?>
        <a class="nav-auth-mobile" href="/No_chao/conta/perfil.php"><?= icon('user', 'icon nav-ico') ?><span>Perfil</span></a>
        <form method="post" action="/No_chao/auth/logout.php" class="nav-auth-mobile-form">
          <?= csrf_field() ?>
          <button type="submit" class="nav-auth-mobile-btn"><?= icon('logout', 'icon nav-ico') ?><span>Sair</span></button>
        </form>
      <?php else: ?>
        <a class="nav-auth-mobile" href="/No_chao/auth/login.php"><?= icon('login', 'icon nav-ico') ?><span>Entrar</span></a>
        <a class="nav-auth-mobile js-open-register" href="/No_chao/auth/registar.php"><?= icon('user', 'icon nav-ico') ?><span>Registar</span></a>
      <?php endif; ?>
    </nav>

    <div class="actions">
      <button type="button" class="icon-btn" id="themeToggle" aria-label="Mudar tema" title="Mudar tema">
        <span class="theme-ico theme-ico-sun" hidden><?= icon('sun') ?></span>
        <span class="theme-ico theme-ico-moon"><?= icon('moon') ?></span>
      </button>

      <?php if ($user): ?>
        <div class="user-menu" id="userMenu">
          <button type="button" class="user-menu-btn" id="userMenuBtn" aria-expanded="false" aria-haspopup="true" aria-controls="userMenuPanel" title="<?= e($user['nome']) ?>">
            <span class="user-avatar" aria-hidden="true"><?= e($userInitial) ?></span>
            <span class="user-menu-caret" aria-hidden="true"></span>
          </button>
          <div class="user-menu-panel" id="userMenuPanel" hidden role="menu">
            <div class="user-menu-head">
              <strong><?= e($user['nome']) ?></strong>
              <span><?= e($user['email']) ?></span>
            </div>
            <?php if (in_array($user['tipo'], ['CLIENTE', 'ADMIN'], true)): ?>
              <a role="menuitem" class="user-menu-primary" href="/No_chao/conta/meus_pedidos.php">
                <?= icon('bag', 'icon sm') ?>
                <span class="user-menu-label">
                  <strong>Os meus pedidos</strong>
                  <small>Acompanhar entrega</small>
                </span>
              </a>
            <?php endif; ?>
            <a role="menuitem" href="/No_chao/conta/perfil.php"><?= icon('user', 'icon sm') ?> O meu perfil</a>
            <?php if ($user['tipo'] === 'ADMIN'): ?>
              <a role="menuitem" href="/No_chao/admin/site.php"><?= icon('package', 'icon sm') ?> Gestão</a>
            <?php elseif ($user['tipo'] === 'ENTREGADOR'): ?>
              <a role="menuitem" href="/No_chao/entregador/"><?= icon('truck', 'icon sm') ?> Entregas</a>
            <?php endif; ?>
            <form method="post" action="/No_chao/auth/logout.php" role="none">
              <?= csrf_field() ?>
              <button role="menuitem" class="is-danger user-menu-btn-link" type="submit"><?= icon('logout', 'icon sm') ?> Sair</button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <a class="btn ghost sm desktop-only" href="/No_chao/auth/login.php"><?= icon('login', 'icon sm') ?> Entrar</a>
        <a class="btn primary sm desktop-only js-open-register" href="/No_chao/auth/registar.php"><?= icon('user', 'icon sm') ?> Registar</a>
      <?php endif; ?>

      <a class="cart-btn" href="/No_chao/loja/carrinho.php" aria-label="Carrinho">
        <?= icon('cart', 'icon') ?>
        <span class="cart-label">Carrinho</span>
        <span class="cart-count"><?= (int) $cartCount ?></span>
      </a>
      <button class="nav-toggle" id="navToggle" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="mainNav">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>
<div class="nav-overlay" id="navOverlay" hidden></div>

<?php if ($flash): ?>
  <div class="flash flash-<?= e($flash['type']) ?>">
    <div class="container"><?= e($flash['message']) ?></div>
  </div>
<?php endif; ?>

<?php
$promoAlertas = [];
if (config_bool('alerta_promocao_activo', true)) {
    $txtGlobal = trim((string) (config_get('alerta_promocao_texto') ?? ''));
    if ($txtGlobal !== '') {
        $promoAlertas[] = ['titulo' => 'Promoção', 'mensagem' => $txtGlobal, 'id' => 'cfg'];
    }
}
foreach (promocoes_activas() as $pr) {
    $promoAlertas[] = [
        'titulo' => $pr['titulo'],
        'mensagem' => $pr['mensagem'],
        'id' => (string) $pr['id'],
    ];
}
$promoAlertas = array_slice($promoAlertas, 0, 3);
?>
<?php if ($promoAlertas): ?>
  <div class="promo-alerts" id="promoAlerts" role="region" aria-label="Alertas de promoção">
    <?php foreach ($promoAlertas as $alerta): ?>
      <div class="promo-alert" data-promo-id="<?= e($alerta['id']) ?>">
        <div class="container promo-alert-inner">
          <span class="promo-alert-ico"><?= icon('tag', 'icon sm') ?></span>
          <div class="promo-alert-text">
            <strong><?= e($alerta['titulo']) ?></strong>
            <span><?= e($alerta['mensagem']) ?></span>
          </div>
          <button type="button" class="promo-alert-close" aria-label="Fechar alerta">&times;</button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<main>
