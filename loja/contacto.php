<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/icons.php';

$pageTitle = 'Contacto — ' . site_nome();
$ok = false;
$error = '';
$waNum = whatsapp_numero();
$localizacao = site_cfg('site_localizacao', 'Bissau, Guiné-Bissau');
$horario = site_cfg('site_horario', 'Seg–Sáb · 09:00–19:00');
$emailLoja = site_cfg('site_email');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $mensagem = trim((string) ($_POST['mensagem'] ?? ''));

    if ($nome === '' || $mensagem === '') {
        $error = 'Preencha pelo menos o nome e a mensagem.';
    } else {
        $texto = "Olá " . site_nome() . "!\n\n*Contacto do site*\nNome: {$nome}\nEmail: {$email}\nTelefone: {$telefone}\n\nMensagem:\n{$mensagem}";
        $whatsappRedirect = whatsapp_url($texto, $waNum);
        $ok = true;
    }
}

require __DIR__ . '/../includes/header.php';
?>

<section class="section contact-page">
  <div class="container">
    <div class="section-head">
      <h2><?= icon('phone', 'icon inline') ?> Contacto</h2>
      <p><?= e(site_cfg('contacto_subtitulo', 'Fale connosco por WhatsApp, telefone ou formulário.')) ?></p>
    </div>

    <div class="contact-grid">
      <div class="contact-cards">
        <a class="contact-card" href="https://wa.me/<?= e($waNum) ?>" target="_blank" rel="noopener">
          <span class="contact-ico"><?= icon('whatsapp') ?></span>
          <div>
            <strong>WhatsApp</strong>
            <span>+<?= e($waNum) ?></span>
          </div>
        </a>
        <a class="contact-card" href="tel:+<?= e($waNum) ?>">
          <span class="contact-ico"><?= icon('phone') ?></span>
          <div>
            <strong>Telefone</strong>
            <span>+<?= e($waNum) ?></span>
          </div>
        </a>
        <div class="contact-card">
          <span class="contact-ico"><?= icon('map') ?></span>
          <div>
            <strong>Localização</strong>
            <span><?= e($localizacao) ?></span>
          </div>
        </div>
        <div class="contact-card">
          <span class="contact-ico"><?= icon('clock') ?></span>
          <div>
            <strong>Horário</strong>
            <span><?= e($horario) ?></span>
          </div>
        </div>
        <?php if ($emailLoja !== ''): ?>
          <a class="contact-card" href="mailto:<?= e($emailLoja) ?>">
            <span class="contact-ico"><?= icon('mail') ?></span>
            <div>
              <strong>Email</strong>
              <span><?= e($emailLoja) ?></span>
            </div>
          </a>
        <?php endif; ?>
      </div>

      <div class="panel contact-form-card">
        <h3><?= icon('send', 'icon inline') ?> Enviar mensagem</h3>
        <?php if ($error): ?>
          <p class="flash-error" style="padding:0.4rem 0"><?= e($error) ?></p>
        <?php endif; ?>
        <?php if ($ok): ?>
          <p class="flash-success" style="padding:0.4rem 0">A abrir o WhatsApp com a sua mensagem…</p>
          <a class="btn primary block" href="<?= e($whatsappRedirect) ?>" id="waContactLink" target="_blank" rel="noopener">
            <?= icon('whatsapp', 'icon') ?> Abrir WhatsApp
          </a>
          <script>
            setTimeout(function () {
              window.location.href = <?= json_encode($whatsappRedirect, JSON_UNESCAPED_SLASHES) ?>;
            }, 700);
          </script>
        <?php else: ?>
          <form method="post">
            <div class="form-group">
              <label for="nome">Nome</label>
              <input id="nome" name="nome" required value="<?= e($_POST['nome'] ?? '') ?>">
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="telefone">Telefone</label>
                <input id="telefone" name="telefone" value="<?= e($_POST['telefone'] ?? '') ?>">
              </div>
            </div>
            <div class="form-group">
              <label for="mensagem">Mensagem</label>
              <textarea id="mensagem" name="mensagem" rows="5" required><?= e($_POST['mensagem'] ?? '') ?></textarea>
            </div>
            <button class="btn primary block" type="submit"><?= icon('send', 'icon') ?> Enviar via WhatsApp</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
