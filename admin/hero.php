<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

$pdo = db();
$error = '';
$openModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar') {
        $id = (int) ($_POST['id'] ?? 0);
        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $texto = trim((string) ($_POST['texto'] ?? ''));
        $nota = trim((string) ($_POST['nota'] ?? ''));
        $cta = trim((string) ($_POST['cta_texto'] ?? 'Ver coleção')) ?: 'Ver coleção';
        $href = trim((string) ($_POST['cta_href'] ?? '#produtos')) ?: '#produtos';
        $ordem = (int) ($_POST['ordem'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;
        $imagemActual = trim((string) ($_POST['imagem_actual'] ?? 'assets/hero-banner.png'));
        if ($imagemActual !== 'assets/hero-banner.png') {
            $safe = sanitize_upload_name($imagemActual);
            $imagemActual = $safe && is_file(BASE_PATH . '/assets/uploads/' . $safe)
                ? $safe
                : 'assets/hero-banner.png';
        }
        $imagem = $imagemActual ?: 'assets/hero-banner.png';

        if (!empty($_FILES['imagem']['name'])) {
            $up = upload_image($_FILES['imagem'], 'hero');
            if ($up['ok']) {
                $imagem = $up['filename'];
            } else {
                $error = $up['error'] ?? 'Falha no upload da imagem.';
            }
        }

        if ($error === '' && ($titulo === '' || $texto === '')) {
            $error = 'Título e texto são obrigatórios.';
        } elseif ($error === '' && !is_safe_href($href)) {
            $error = 'Link do botão inválido. Use #ancora, /caminho ou https://…';
        } elseif ($error === '' && $id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE hero_slides SET titulo=?, texto=?, nota=?, cta_texto=?, cta_href=?, imagem=?, ordem=?, activo=? WHERE id=?'
            );
            $stmt->execute([$titulo, $texto, $nota ?: null, $cta, $href, $imagem, $ordem, $activo, $id]);
            flash('success', 'Slide actualizado.');
            redirect('/No_chao/admin/hero.php');
        } elseif ($error === '') {
            $stmt = $pdo->prepare(
                'INSERT INTO hero_slides (titulo, texto, nota, cta_texto, cta_href, imagem, ordem, activo) VALUES (?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([$titulo, $texto, $nota ?: null, $cta, $href, $imagem, $ordem, $activo]);
            flash('success', 'Slide criado.');
            redirect('/No_chao/admin/hero.php');
        }

        if ($error !== '') {
            $openModal = true;
            $edit = [
                'id' => $id,
                'titulo' => $titulo,
                'texto' => $texto,
                'nota' => $nota,
                'cta_texto' => $cta,
                'cta_href' => $href,
                'ordem' => $ordem,
                'activo' => $activo,
                'imagem' => $imagem,
            ];
        }
    }

    if ($acao === 'apagar') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM hero_slides WHERE id = ?')->execute([$id]);
        flash('info', 'Slide removido.');
        redirect('/No_chao/admin/hero.php');
    }

    if ($acao === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE hero_slides SET activo = 1 - activo WHERE id = ?')->execute([$id]);
        flash('success', 'Estado do slide actualizado.');
        redirect('/No_chao/admin/hero.php');
    }
}

$edit = $edit ?? null;
if (!$edit && isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM hero_slides WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
    if ($edit) {
        $openModal = true;
    }
}
if (isset($_GET['novo'])) {
    $openModal = true;
    $edit = null;
}

$slides = $pdo->query('SELECT * FROM hero_slides ORDER BY ordem ASC, id ASC')->fetchAll();
$isEdit = !empty($edit['id']);

admin_header('Hero / Banner', 'hero');
?>

<div class="admin-page-head">
  <div>
    <h2>Hero / Banner inicial</h2>
    <p>Slides do carrossel no topo da loja. Ordem controla a sequência.</p>
  </div>
  <div class="head-actions">
    <a class="btn ghost sm" href="/No_chao/admin/site.php">← Site</a>
    <button type="button" class="btn primary sm" data-open-modal="modalHero">+ Novo slide</button>
  </div>
</div>

<div class="admin-list-card">
  <div class="list-head">
    <h3>Slides</h3>
    <span><?= count($slides) ?> no total</span>
  </div>
  <?php if (!$slides): ?>
    <div class="empty" style="border:0">Ainda sem slides. <button type="button" class="btn primary sm" data-open-modal="modalHero">Criar o primeiro</button></div>
  <?php else: ?>
    <div class="admin-scroll">
      <table class="admin-table" style="table-layout:auto">
        <thead>
          <tr>
            <th>Img</th>
            <th>Ordem</th>
            <th>Slide</th>
            <th>Estado</th>
            <th>Acções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($slides as $s): ?>
            <?php $url = hero_image_url($s['imagem'] ?? ''); ?>
            <tr class="<?= empty($s['activo']) ? 'is-inactive' : '' ?>">
              <td data-label="Imagem">
                <?php if ($url): ?>
                  <img class="hero-thumb" src="<?= e($url) ?>" alt="">
                <?php else: ?>
                  <span class="hero-thumb-ph">NC</span>
                <?php endif; ?>
              </td>
              <td data-label="Ordem"><?= (int)$s['ordem'] ?></td>
              <td data-label="Slide">
                <strong><?= e($s['titulo']) ?></strong>
                <br><small class="muted"><?= e($s['texto']) ?></small>
              </td>
              <td data-label="Estado">
                <?= !empty($s['activo']) ? '<span class="pill pill-ok">Activo</span>' : '<span class="pill pill-off">Inactivo</span>' ?>
              </td>
              <td data-label="Acções" class="col-acoes">
                <div class="acoes-cell">
                  <a class="btn ghost sm" href="?edit=<?= (int)$s['id'] ?>">Editar</a>
                  <form method="post">
                    <input type="hidden" name="acao" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <button class="btn ghost sm" type="submit"><?= !empty($s['activo']) ? 'Desactivar' : 'Activar' ?></button>
                  </form>
                  <form method="post" onsubmit="return confirm('Apagar este slide?')">
                    <input type="hidden" name="acao" value="apagar">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <button class="btn danger sm" type="submit">Apagar</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="modal-root admin-modal" id="modalHero" <?= $openModal ? 'data-auto-open="1"' : 'hidden' ?> aria-hidden="<?= $openModal ? 'false' : 'true' ?>">
  <div class="modal-backdrop" data-close-modal></div>
  <div class="modal-dialog modal-lg" role="dialog" aria-modal="true" aria-labelledby="modalHeroTitle">
    <button type="button" class="modal-close" data-close-modal aria-label="Fechar"><?= icon('close') ?></button>
    <div class="modal-body">
      <h2 id="modalHeroTitle"><?= $isEdit ? 'Editar slide' : 'Novo slide' ?></h2>
      <p class="muted">Imagem de fundo, textos e botão do carrossel.</p>
      <?php if ($error): ?>
        <p class="modal-error"><?= e($error) ?></p>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <input type="hidden" name="imagem_actual" value="<?= e($edit['imagem'] ?? 'assets/hero-banner.png') ?>">

        <div class="admin-form-grid">
          <div class="form-group full">
            <label for="titulo">Título</label>
            <input id="titulo" name="titulo" required value="<?= e($edit['titulo'] ?? '') ?>">
          </div>
          <div class="form-group full">
            <label for="texto">Texto</label>
            <textarea id="texto" name="texto" rows="2" required><?= e($edit['texto'] ?? '') ?></textarea>
          </div>
          <div class="form-group full">
            <label for="nota">Nota</label>
            <input id="nota" name="nota" value="<?= e($edit['nota'] ?? '') ?>" placeholder="Pagamento na entrega · Entregas em Bissau">
          </div>
          <div class="form-group">
            <label for="cta_texto">Texto do botão</label>
            <input id="cta_texto" name="cta_texto" value="<?= e($edit['cta_texto'] ?? 'Ver coleção') ?>">
          </div>
          <div class="form-group">
            <label for="cta_href">Link do botão</label>
            <input id="cta_href" name="cta_href" value="<?= e($edit['cta_href'] ?? '#produtos') ?>">
          </div>
          <div class="form-group">
            <label for="ordem">Ordem</label>
            <input type="number" id="ordem" name="ordem" value="<?= e((string)($edit['ordem'] ?? count($slides) + 1)) ?>">
          </div>
          <div class="form-group">
            <label for="imagem">Imagem de fundo</label>
            <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/webp,image/gif" data-preview="heroPreview">
            <div class="modal-preview" id="heroPreview">
              <?php if (!empty($edit['imagem'])): ?>
                <img src="<?= e(hero_image_url($edit['imagem'])) ?>" alt="Pré-visualização">
              <?php else: ?>
                <img src="<?= e(hero_image_url('assets/hero-banner.png')) ?>" alt="Pré-visualização">
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end">
            <label style="display:flex;align-items:center;gap:0.5rem;margin:0;padding-bottom:0.7rem">
              <input type="checkbox" name="activo" <?= !isset($edit) || !empty($edit['activo']) ? 'checked' : '' ?>>
              Activo na loja
            </label>
          </div>
        </div>

        <div class="admin-form-actions">
          <button class="btn primary" type="submit"><?= $isEdit ? 'Guardar' : 'Criar slide' ?></button>
          <button class="btn ghost" type="button" data-close-modal>Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php admin_footer('hero'); ?>
