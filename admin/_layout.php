<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

// Autenticação e CSRF ANTES de qualquer POST nos ficheiros admin
require_login('ADMIN');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
}

function admin_header(string $title, string $active = 'dashboard'): void
{
    require_once __DIR__ . '/../includes/icons.php';
    $user = current_user();
    $pageTitle = $title . ' — Gestão ' . site_nome();
    $flash = get_flash();
    ?>
<!DOCTYPE html>
<html lang="pt" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= e($pageTitle) ?></title>
  <?= csrf_meta() ?>
  <meta name="theme-color" content="#0f2e1f" id="metaThemeColor">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/painel.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/responsive.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/admin-responsive.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/admin-modal.css') ?>">
  <script>window.APP_BASE_URL = <?= json_encode(APP_BASE_URL, JSON_UNESCAPED_SLASHES) ?>;</script>
  <script>
    (function () {
      try {
        var t = localStorage.getItem('nu_chao_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', t);
      } catch (e) {}
    })();
  </script>
</head>
<body class="admin-body painel-body">
<header class="topbar admin-topbar painel-topbar">
  <div class="admin-container painel-shell topbar-inner">
    <a class="brand" href="<?= url('admin/') ?>">
      <img src="<?= url('assets/logo-nc.png') ?>" alt="<?= e(site_nome()) ?>" class="brand-logo">
      <span class="brand-text painel-brand-text">Gestão</span>
    </a>
    <div class="actions">
      <button type="button" class="icon-btn" id="themeToggle" aria-label="Mudar tema" title="Mudar tema">
        <span class="theme-ico theme-ico-sun" hidden><?= icon('sun') ?></span>
        <span class="theme-ico theme-ico-moon"><?= icon('moon') ?></span>
      </button>
      <span class="user-chip"><?= e($user['nome']) ?></span>
      <a class="btn ghost sm hide-sm" href="<?= url('index.php') ?>"><?= icon('home', 'icon sm') ?> Loja</a>
      <form method="post" action="<?= url('auth/logout.php') ?>" class="inline-form hide-sm">
        <?= csrf_field() ?>
        <button class="btn ghost sm" type="submit"><?= icon('logout', 'icon sm') ?> Sair</button>
      </form>
    </div>
  </div>
</header>
<?php if ($flash): ?>
  <div class="flash flash-<?= e($flash['type']) ?>"><div class="admin-container painel-shell"><?= e($flash['message']) ?></div></div>
<?php endif; ?>
<main class="section">
  <div class="admin-container painel-shell">
    <nav class="admin-nav painel-nav" aria-label="Menu gestão">
      <a class="<?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= url('admin/') ?>"><?= icon('spark', 'icon sm') ?> Dashboard</a>
      <a class="<?= in_array($active, ['site', 'hero', 'conteudo', 'info', 'zonas'], true) ? 'active' : '' ?>" href="<?= url('admin/site.php') ?>"><?= icon('package', 'icon sm') ?> Site</a>
      <a class="<?= $active === 'produtos' ? 'active' : '' ?>" href="<?= url('admin/produtos.php') ?>"><?= icon('shirt', 'icon sm') ?> Produtos</a>
      <a class="<?= $active === 'promocoes' ? 'active' : '' ?>" href="<?= url('admin/promocoes.php') ?>"><?= icon('tag', 'icon sm') ?> Promoções</a>
      <a class="<?= $active === 'pedidos' ? 'active' : '' ?>" href="<?= url('admin/pedidos.php') ?>"><?= icon('bag', 'icon sm') ?> Pedidos</a>
      <a class="<?= $active === 'entregadores' ? 'active' : '' ?>" href="<?= url('admin/entregadores.php') ?>"><?= icon('truck', 'icon sm') ?> Entregadores</a>
      <a class="<?= $active === 'configuracoes' ? 'active' : '' ?>" href="<?= url('admin/configuracoes.php') ?>"><?= icon('settings', 'icon sm') ?> Opções</a>
    </nav>
<?php
}

function admin_footer(string $active = 'dashboard'): void
{
    require_once __DIR__ . '/../includes/icons.php';
    ?>
  </div>
</main>

<nav class="painel-mobile-bar" aria-label="Navegação gestão">
  <a href="<?= url('admin/') ?>" class="<?= $active === 'dashboard' ? 'is-active' : '' ?>">
    <?= icon('spark') ?><span>Início</span>
  </a>
  <a href="<?= url('admin/site.php') ?>" class="<?= in_array($active, ['site', 'hero', 'conteudo', 'info', 'zonas'], true) ? 'is-active' : '' ?>">
    <?= icon('package') ?><span>Site</span>
  </a>
  <a href="<?= url('admin/produtos.php') ?>" class="<?= $active === 'produtos' ? 'is-active' : '' ?>">
    <?= icon('shirt') ?><span>Produtos</span>
  </a>
  <a href="<?= url('admin/pedidos.php') ?>" class="<?= $active === 'pedidos' ? 'is-active' : '' ?>">
    <?= icon('bag') ?><span>Pedidos</span>
  </a>
  <a href="<?= url('admin/configuracoes.php') ?>" class="<?= $active === 'configuracoes' ? 'is-active' : '' ?>">
    <?= icon('settings') ?><span>Mais</span>
  </a>
</nav>

<script src="<?= url('assets/js/app.js') ?>"></script>
<script src="<?= url('assets/js/admin.js') ?>"></script>
</body>
</html>
<?php
}
