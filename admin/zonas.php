<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

$pdo = db();
$error = '';
$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar') {
        $id = (int) ($_POST['id'] ?? 0);
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $descricao = trim((string) ($_POST['descricao'] ?? ''));
        $taxa = round((float) str_replace(',', '.', (string) ($_POST['taxa'] ?? 0)), 2);
        $tempo = trim((string) ($_POST['tempo_estimado'] ?? ''));
        $ativa = isset($_POST['ativa']) ? 1 : 0;

        if ($nome === '') {
            $error = 'Nome da zona é obrigatório.';
            $edit = compact('id', 'nome', 'descricao', 'taxa', 'tempo') + ['tempo_estimado' => $tempo, 'ativa' => $ativa];
        } elseif ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE zonas_entrega SET nome=?, descricao=?, taxa=?, tempo_estimado=?, ativa=? WHERE id=?'
            );
            $stmt->execute([$nome, $descricao ?: null, $taxa, $tempo ?: null, $ativa, $id]);
            flash('success', 'Zona actualizada.');
            redirect('/No_chao/admin/zonas.php');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO zonas_entrega (nome, descricao, taxa, tempo_estimado, ativa) VALUES (?,?,?,?,?)'
            );
            $stmt->execute([$nome, $descricao ?: null, $taxa, $tempo ?: null, $ativa]);
            flash('success', 'Zona criada.');
            redirect('/No_chao/admin/zonas.php');
        }
    }

    if ($acao === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE zonas_entrega SET ativa = 1 - ativa WHERE id = ?')->execute([$id]);
        flash('success', 'Estado da zona actualizado.');
        redirect('/No_chao/admin/zonas.php');
    }
}

if (!$edit && isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM zonas_entrega WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$zonas = $pdo->query('SELECT * FROM zonas_entrega ORDER BY ativa DESC, nome')->fetchAll();
$openModal = $error !== '' || !empty($edit) || isset($_GET['novo']);
$isEdit = !empty($edit['id']);

admin_header('Zonas de entrega', 'zonas');
?>

<div class="admin-page-head">
  <div>
    <h2>Zonas de entrega</h2>
    <p>Taxas e tempos usados no checkout e na página Sobre.</p>
  </div>
  <div class="head-actions">
    <a class="btn ghost sm" href="/No_chao/admin/site.php">← Site</a>
    <button type="button" class="btn primary sm" data-open-modal="modalZona">+ Nova zona</button>
  </div>
</div>

<div class="admin-list-card">
  <div class="list-head">
    <h3>Zonas</h3>
    <span><?= count($zonas) ?> no total</span>
  </div>
  <div class="admin-scroll">
    <table class="admin-table" style="table-layout:auto">
      <thead>
        <tr>
          <th>Zona</th>
          <th>Taxa</th>
          <th>Tempo</th>
          <th>Estado</th>
          <th>Acções</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($zonas as $z): ?>
          <tr class="<?= empty($z['ativa']) ? 'is-inactive' : '' ?>">
            <td data-label="Zona">
              <strong><?= e($z['nome']) ?></strong>
              <?php if (!empty($z['descricao'])): ?>
                <br><small class="muted"><?= e($z['descricao']) ?></small>
              <?php endif; ?>
            </td>
            <td data-label="Taxa"><?= money($z['taxa']) ?></td>
            <td data-label="Tempo"><?= e($z['tempo_estimado'] ?: '—') ?></td>
            <td data-label="Estado">
              <?= !empty($z['ativa']) ? '<span class="pill pill-ok">Activa</span>' : '<span class="pill pill-off">Inactiva</span>' ?>
            </td>
            <td data-label="Acções" class="col-acoes">
              <div class="acoes-cell">
                <a class="btn ghost sm" href="?edit=<?= (int)$z['id'] ?>">Editar</a>
                <form method="post">
                  <input type="hidden" name="acao" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$z['id'] ?>">
                  <button class="btn ghost sm" type="submit"><?= !empty($z['ativa']) ? 'Desactivar' : 'Activar' ?></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$zonas): ?>
          <tr><td colspan="5" class="muted">Sem zonas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-root admin-modal" id="modalZona" <?= $openModal ? 'data-auto-open="1"' : 'hidden' ?> aria-hidden="<?= $openModal ? 'false' : 'true' ?>">
  <div class="modal-backdrop" data-close-modal></div>
  <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalZonaTitle">
    <button type="button" class="modal-close" data-close-modal aria-label="Fechar"><?= icon('close') ?></button>
    <div class="modal-body">
      <h2 id="modalZonaTitle"><?= $isEdit ? 'Editar zona' : 'Nova zona' ?></h2>
      <?php if ($error): ?><p class="modal-error"><?= e($error) ?></p><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <div class="admin-form-grid">
          <div class="form-group">
            <label for="nome">Nome</label>
            <input id="nome" name="nome" required value="<?= e($edit['nome'] ?? '') ?>" placeholder="Ex: Bandim">
          </div>
          <div class="form-group">
            <label for="taxa">Taxa (XOF)</label>
            <input type="number" id="taxa" name="taxa" min="0" step="0.01" value="<?= e(isset($edit['taxa']) ? rtrim(rtrim(number_format((float)$edit['taxa'], 2, '.', ''), '0'), '.') : '0') ?>">
          </div>
          <div class="form-group">
            <label for="tempo_estimado">Tempo estimado</label>
            <input id="tempo_estimado" name="tempo_estimado" value="<?= e($edit['tempo_estimado'] ?? '') ?>" placeholder="40-60 min">
          </div>
          <div class="form-group full">
            <label for="descricao">Descrição</label>
            <input id="descricao" name="descricao" value="<?= e($edit['descricao'] ?? '') ?>" placeholder="Áreas cobertas">
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end">
            <label style="display:flex;align-items:center;gap:0.5rem;margin:0;padding-bottom:0.7rem">
              <input type="checkbox" name="ativa" <?= !isset($edit) || !empty($edit['ativa']) ? 'checked' : '' ?>> Zona activa
            </label>
          </div>
        </div>
        <div class="admin-form-actions">
          <button class="btn primary" type="submit"><?= $isEdit ? 'Guardar' : 'Criar zona' ?></button>
          <button class="btn ghost" type="button" data-close-modal>Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php admin_footer('zonas'); ?>
