<?php
declare(strict_types=1);
if (!function_exists('icon')) {
    require_once __DIR__ . '/icons.php';
}
if (current_user()) {
    return;
}
$googleOk = function_exists('google_configurado') && google_configurado();
?>
<div class="modal-root" id="registerModal" hidden aria-hidden="true">
  <div class="modal-backdrop" data-close-register></div>
  <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="registerModalTitle">
    <button type="button" class="modal-close" data-close-register aria-label="Fechar">
      <?= icon('close') ?>
    </button>
    <div class="modal-body">
      <h2 id="registerModalTitle"><?= icon('user', 'icon inline') ?> Criar conta</h2>
      <p class="muted">Escolha o tipo de conta. Clientes compram de imediato; entregadores precisam de aprovação.</p>
      <p class="modal-error" id="registerError" hidden></p>

      <form id="registerForm" method="post" action="<?= url('api/registar.php') ?>" novalidate>
        <?= csrf_field() ?>
        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hp-field" aria-hidden="true">
        <div class="form-group">
          <label>Tipo de utilizador</label>
          <div class="tipo-opcoes">
            <label class="tipo-card active">
              <input type="radio" name="tipo" value="CLIENTE" checked>
              <strong>Cliente</strong>
              <span>Fazer compras com pagamento na entrega. Sem aprovação.</span>
            </label>
            <label class="tipo-card">
              <input type="radio" name="tipo" value="ENTREGADOR">
              <strong>Entregador</strong>
              <span>Receber entregas. Conta validada pelo administrador.</span>
            </label>
          </div>
        </div>
        <div class="form-group">
          <label for="reg_nome">Nome completo</label>
          <input class="form-control" id="reg_nome" name="nome" required autocomplete="name">
        </div>
        <div class="form-group">
          <label for="reg_email">Email</label>
          <input class="form-control" type="email" id="reg_email" name="email" required autocomplete="email">
        </div>
        <div class="form-group">
          <label for="reg_telefone">Telefone / WhatsApp</label>
          <input class="form-control" id="reg_telefone" name="telefone" required placeholder="+245..." autocomplete="tel">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="reg_senha">Palavra-passe</label>
            <input class="form-control" type="password" id="reg_senha" name="senha" required minlength="6" autocomplete="new-password">
          </div>
          <div class="form-group">
            <label for="reg_senha2">Confirmar</label>
            <input class="form-control" type="password" id="reg_senha2" name="senha2" required minlength="6" autocomplete="new-password">
          </div>
        </div>
        <button class="btn primary block" type="submit" id="registerSubmit">
          <?= icon('user', 'icon') ?> Registar
        </button>
      </form>

      <?php if ($googleOk): ?>
        <div class="auth-divider"><span>ou</span></div>
        <a class="btn-google" href="#" id="googleRegistoBtn">
          <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.9 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l5.7-5.7C34.2 6.1 29.4 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.5-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 16 19 12 24 12c3.1 0 5.8 1.1 8 3l5.7-5.7C34.2 6.1 29.4 4 24 4 16.3 4 9.6 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.3 35.3 26.8 36 24 36c-5.3 0-9.7-3.1-11.3-7.5l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.1-4.1 5.5l.1.1 6.2 5.2C39.2 37.3 44 33 44 24c0-1.3-.1-2.5-.4-3.5z"/></svg>
          Registar com Google
        </a>
        <p class="help">O tipo seleccionado acima será usado na conta Google.</p>
      <?php endif; ?>

      <p class="help">Já tem conta? <a href="<?= url('auth/login.php') ?>">Entrar</a></p>
    </div>
  </div>
</div>
