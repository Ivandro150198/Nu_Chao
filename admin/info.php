<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

$chaves = [
    'site_nome', 'site_tagline', 'site_titulo_home', 'site_localizacao', 'site_horario', 'site_email', 'whatsapp_loja',
    'contacto_subtitulo', 'footer_texto',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($chaves as $chave) {
        $valor = trim((string) ($_POST[$chave] ?? ''));
        if ($chave === 'whatsapp_loja') {
            $valor = preg_replace('/\D+/', '', $valor) ?: APP_WHATSAPP;
        }
        config_set($chave, $valor);
    }
    flash('success', 'Informações do site guardadas.');
    redirect('/No_chao/admin/info.php');
}

$cfg = [];
foreach ($chaves as $k) {
    $defaults = [
        'site_nome' => APP_NAME,
        'site_tagline' => 'Roupas e acessórios inspirados na Guiné-Bissau.',
        'site_titulo_home' => 'Nu Chao — Moda na Guiné-Bissau',
        'site_localizacao' => 'Bissau, Guiné-Bissau',
        'site_horario' => 'Seg–Sáb · 09:00–19:00',
        'whatsapp_loja' => APP_WHATSAPP,
        'contacto_subtitulo' => 'Fale connosco por WhatsApp, telefone ou formulário.',
        'footer_texto' => 'Roupas e acessórios inspirados na Guiné-Bissau. Pagamento na entrega em Bissau e arredores.',
    ];
    $cfg[$k] = site_cfg($k, $defaults[$k] ?? '');
}

admin_header('Informações', 'info');
?>

<div class="admin-page-head">
  <div>
    <h2>Informações &amp; contacto</h2>
    <p>Dados que aparecem no cabeçalho, contacto, rodapé e WhatsApp.</p>
  </div>
  <a class="btn ghost sm" href="/No_chao/admin/site.php">← Gestão do site</a>
</div>

<form class="admin-form-card" method="post">
  <h3>Marca</h3>
  <div class="admin-form-grid">
    <div class="form-group">
      <label for="site_nome">Nome da loja</label>
      <input id="site_nome" name="site_nome" required value="<?= e($cfg['site_nome']) ?>">
    </div>
    <div class="form-group">
      <label for="site_titulo_home">Título da página inicial (SEO)</label>
      <input id="site_titulo_home" name="site_titulo_home" value="<?= e($cfg['site_titulo_home']) ?>">
    </div>
    <div class="form-group full">
      <label for="site_tagline">Slogan / descrição curta</label>
      <input id="site_tagline" name="site_tagline" value="<?= e($cfg['site_tagline']) ?>">
    </div>
    <div class="form-group full">
      <label for="footer_texto">Texto do rodapé</label>
      <textarea id="footer_texto" name="footer_texto" rows="2"><?= e($cfg['footer_texto']) ?></textarea>
    </div>
  </div>

  <h3 style="margin-top:1.5rem">Contacto</h3>
  <div class="admin-form-grid">
    <div class="form-group">
      <label for="whatsapp_loja">WhatsApp / telefone</label>
      <input id="whatsapp_loja" name="whatsapp_loja" value="<?= e($cfg['whatsapp_loja']) ?>" placeholder="245955000000">
    </div>
    <div class="form-group">
      <label for="site_email">Email (opcional)</label>
      <input id="site_email" name="site_email" type="email" value="<?= e($cfg['site_email']) ?>">
    </div>
    <div class="form-group">
      <label for="site_localizacao">Localização</label>
      <input id="site_localizacao" name="site_localizacao" value="<?= e($cfg['site_localizacao']) ?>">
    </div>
    <div class="form-group">
      <label for="site_horario">Horário</label>
      <input id="site_horario" name="site_horario" value="<?= e($cfg['site_horario']) ?>">
    </div>
    <div class="form-group full">
      <label for="contacto_subtitulo">Subtítulo da página Contacto</label>
      <input id="contacto_subtitulo" name="contacto_subtitulo" value="<?= e($cfg['contacto_subtitulo']) ?>">
    </div>
  </div>

  <div class="admin-form-actions">
    <button class="btn primary" type="submit">Guardar informações</button>
    <a class="btn ghost" href="/No_chao/loja/contacto.php" target="_blank" rel="noopener">Ver página contacto</a>
  </div>
</form>

<?php admin_footer('info'); ?>
