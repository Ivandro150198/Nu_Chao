<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

$grupos = [
    'Início — coleção' => [
        'colecao_titulo', 'colecao_subtitulo',
    ],
    'Página Sobre' => [
        'sobre_eyebrow', 'sobre_titulo', 'sobre_lead',
        'sobre_quem_titulo', 'sobre_quem_texto',
        'sobre_missao_titulo', 'sobre_missao_texto', 'sobre_missao_itens',
        'sobre_passos_titulo', 'sobre_passos_subtitulo',
        'sobre_passo1_titulo', 'sobre_passo1_texto',
        'sobre_passo2_titulo', 'sobre_passo2_texto',
        'sobre_passo3_titulo', 'sobre_passo3_texto',
        'sobre_zonas_titulo', 'sobre_zonas_subtitulo',
        'sobre_cta_titulo', 'sobre_cta_texto',
    ],
];

$labels = [
    'colecao_titulo' => 'Título da coleção (home)',
    'colecao_subtitulo' => 'Subtítulo da coleção (home)',
    'sobre_eyebrow' => 'Eyebrow',
    'sobre_titulo' => 'Título principal',
    'sobre_lead' => 'Lead / introdução',
    'sobre_quem_titulo' => 'Quem somos — título',
    'sobre_quem_texto' => 'Quem somos — texto (parágrafos separados por linha em branco)',
    'sobre_missao_titulo' => 'Missão — título',
    'sobre_missao_texto' => 'Missão — texto',
    'sobre_missao_itens' => 'Missão — lista (uma linha por item)',
    'sobre_passos_titulo' => 'Como funciona — título',
    'sobre_passos_subtitulo' => 'Como funciona — subtítulo',
    'sobre_passo1_titulo' => 'Passo 1 — título',
    'sobre_passo1_texto' => 'Passo 1 — texto',
    'sobre_passo2_titulo' => 'Passo 2 — título',
    'sobre_passo2_texto' => 'Passo 2 — texto',
    'sobre_passo3_titulo' => 'Passo 3 — título',
    'sobre_passo3_texto' => 'Passo 3 — texto',
    'sobre_zonas_titulo' => 'Zonas — título',
    'sobre_zonas_subtitulo' => 'Zonas — subtítulo',
    'sobre_cta_titulo' => 'CTA final — título',
    'sobre_cta_texto' => 'CTA final — texto',
];

$longas = [
    'sobre_quem_texto', 'sobre_missao_texto', 'sobre_missao_itens',
    'sobre_lead',
];

$todas = [];
foreach ($grupos as $chaves) {
    foreach ($chaves as $c) {
        $todas[] = $c;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    config_save_many($todas, $_POST);
    flash('success', 'Conteúdos do site guardados.');
    redirect(url('admin/conteudo.php'));
}

admin_header('Conteúdos', 'conteudo');
?>

<div class="admin-page-head">
  <div>
    <h2>Conteúdos &amp; textos</h2>
    <p>Textos exibidos na página inicial e na página Sobre.</p>
  </div>
  <a class="btn ghost sm" href="<?= url('admin/site.php') ?>">← Gestão do site</a>
</div>

<form method="post">
  <?php foreach ($grupos as $tituloGrupo => $chaves): ?>
    <div class="admin-form-card" style="margin-bottom:1rem">
      <h3><?= e($tituloGrupo) ?></h3>
      <div class="admin-form-grid">
        <?php foreach ($chaves as $chave): ?>
          <?php
            $isLong = in_array($chave, $longas, true) || str_ends_with($chave, '_texto') || str_ends_with($chave, '_itens');
            $valor = site_cfg($chave);
          ?>
          <div class="form-group <?= $isLong ? 'full' : '' ?>">
            <label for="<?= e($chave) ?>"><?= e($labels[$chave] ?? $chave) ?></label>
            <?php if ($isLong): ?>
              <textarea id="<?= e($chave) ?>" name="<?= e($chave) ?>" rows="3"><?= e($valor) ?></textarea>
            <?php else: ?>
              <input id="<?= e($chave) ?>" name="<?= e($chave) ?>" value="<?= e($valor) ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="admin-form-actions" style="margin-bottom:2rem">
    <button class="btn primary" type="submit">Guardar conteúdos</button>
    <a class="btn ghost" href="<?= url('loja/sobre.php') ?>" target="_blank" rel="noopener">Ver Sobre</a>
  </div>
</form>

<?php admin_footer('conteudo'); ?>
