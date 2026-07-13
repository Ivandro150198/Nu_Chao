<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/icons.php';

require_login();

$ultimo = $_SESSION['ultimo_pedido_whatsapp'] ?? null;
if (!$ultimo || empty($ultimo['url'])) {
    redirect('/No_chao/conta/meus_pedidos.php');
}

$whatsappUrl = $ultimo['url'];
$codigo = $ultimo['codigo'] ?? '';
$pedidoId = (int) ($ultimo['id'] ?? 0);
unset($_SESSION['ultimo_pedido_whatsapp']);

$trackUrl = $pedidoId > 0
    ? '/No_chao/conta/pedido.php?id=' . $pedidoId
    : '/No_chao/conta/meus_pedidos.php';

$pageTitle = 'Pedido enviado — Nu Chao';
require __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="container auth-wrap" style="min-height:50vh">
    <div class="auth-card" style="text-align:center; max-width:480px">
      <h1><?= icon('check', 'icon inline') ?> Pedido <?= e($codigo) ?></h1>
      <p class="muted">O pedido foi registado. A abrir o WhatsApp para enviar os detalhes à loja…</p>
      <p style="margin:1.5rem 0">
        <a class="btn primary block" id="waLink" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener">
          <?= icon('whatsapp', 'icon') ?> Abrir WhatsApp agora
        </a>
      </p>
      <p class="help">
        <a class="btn ghost block" href="<?= e($trackUrl) ?>"><?= icon('truck', 'icon') ?> Acompanhar este pedido</a>
      </p>
      <p class="help"><a href="/No_chao/conta/meus_pedidos.php">Ver todos os pedidos</a></p>
    </div>
  </div>
</section>

<script>
  setTimeout(function () {
    window.open(<?= json_encode($whatsappUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, '_blank');
  }, 600);
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
