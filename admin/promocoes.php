<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

$pdo = db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar') {
        $id = (int) ($_POST['id'] ?? 0);
        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $mensagem = trim((string) ($_POST['mensagem'] ?? ''));
        $tipo = $_POST['tipo'] ?? 'ALERTA';
        if (!in_array($tipo, ['BANNER', 'ALERTA', 'CUPOM'], true)) {
            $tipo = 'ALERTA';
        }
        $desconto = $_POST['desconto_percent'] !== '' ? (int) $_POST['desconto_percent'] : null;
        $cupom = trim((string) ($_POST['codigo_cupom'] ?? '')) ?: null;
        $produtoId = (int) ($_POST['produto_id'] ?? 0) ?: null;
        $inicio = trim((string) ($_POST['data_inicio'] ?? '')) ?: null;
        $fim = trim((string) ($_POST['data_fim'] ?? '')) ?: null;
        $activo = isset($_POST['activo']) ? 1 : 0;

        if ($titulo === '' || $mensagem === '') {
            $error = 'Título e mensagem são obrigatórios.';
            $edit = [
                'id' => $id,
                'titulo' => $titulo,
                'mensagem' => $mensagem,
                'tipo' => $tipo,
                'desconto_percent' => $desconto,
                'codigo_cupom' => $cupom,
                'produto_id' => $produtoId,
                'data_inicio' => $inicio,
                'data_fim' => $fim,
                'activo' => $activo,
            ];
        } elseif ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE promocoes SET titulo=?, mensagem=?, tipo=?, desconto_percent=?, codigo_cupom=?, produto_id=?, data_inicio=?, data_fim=?, activo=? WHERE id=?'
            );
            $stmt->execute([$titulo, $mensagem, $tipo, $desconto, $cupom, $produtoId, $inicio, $fim, $activo, $id]);
            flash('success', 'Promoção actualizada.');
            redirect('/No_chao/admin/promocoes.php');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO promocoes (titulo, mensagem, tipo, desconto_percent, codigo_cupom, produto_id, data_inicio, data_fim, activo)
                 VALUES (?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([$titulo, $mensagem, $tipo, $desconto, $cupom, $produtoId, $inicio, $fim, $activo]);
            flash('success', 'Promoção criada.');
            redirect('/No_chao/admin/promocoes.php');
        }
    }

    if ($acao === 'apagar') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM promocoes WHERE id = ?')->execute([$id]);
        flash('info', 'Promoção removida.');
        redirect('/No_chao/admin/promocoes.php');
    }

    if ($acao === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE promocoes SET activo = 1 - activo WHERE id = ?')->execute([$id]);
        flash('success', 'Estado da promoção actualizado.');
        redirect('/No_chao/admin/promocoes.php');
    }
}

$edit = $edit ?? null;
if (!$edit && isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM promocoes WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$promocoes = $pdo->query('SELECT pr.*, p.nome AS produto_nome FROM promocoes pr LEFT JOIN produtos p ON p.id = pr.produto_id ORDER BY pr.activo DESC, pr.id DESC')->fetchAll();
$produtos = $pdo->query('SELECT id, nome FROM produtos WHERE ativo = 1 ORDER BY nome')->fetchAll();
$openModal = $error !== '' || !empty($edit) || isset($_GET['novo']);
$isEdit = !empty($edit['id']);

admin_header('Promoções', 'promocoes');
?>

<div class="admin-page-head">
  <div>
    <h2>Promoções e alertas</h2>
    <p>Alertas no topo da loja e promoções. Produtos em desconto também em Produtos.</p>
  </div>
  <div class="head-actions">
    <button type="button" class="btn primary sm" data-open-modal="modalPromo">+ Nova promoção</button>
  </div>
</div>

<div class="admin-list-card">
  <div class="list-head">
    <h3>Promoções</h3>
    <span><?= count($promocoes) ?> no total</span>
  </div>
  <?php if (!$promocoes): ?>
    <div class="empty" style="border:0">Ainda não há promoções. <button type="button" class="btn primary sm" data-open-modal="modalPromo">Criar</button></div>
  <?php else: ?>
    <div class="admin-scroll">
      <table class="admin-table" style="table-layout:auto">
        <thead>
          <tr>
            <th>Título</th>
            <th>Tipo</th>
            <th>Período</th>
            <th>Estado</th>
            <th>Acções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($promocoes as $pr): ?>
            <tr class="<?= empty($pr['activo']) ? 'is-inactive' : '' ?>">
              <td data-label="Título">
                <strong><?= e($pr['titulo']) ?></strong>
                <br><small class="muted"><?= e($pr['mensagem']) ?></small>
              </td>
              <td data-label="Tipo"><span class="pill pill-cat"><?= e($pr['tipo']) ?></span></td>
              <td data-label="Período">
                <small>
                  <?= $pr['data_inicio'] ? e(date('d/m/Y H:i', strtotime($pr['data_inicio']))) : '—' ?>
                  →
                  <?= $pr['data_fim'] ? e(date('d/m/Y H:i', strtotime($pr['data_fim']))) : '—' ?>
                </small>
              </td>
              <td data-label="Estado">
                <?= !empty($pr['activo']) ? '<span class="pill pill-ok">Activa</span>' : '<span class="pill pill-off">Inactiva</span>' ?>
              </td>
              <td data-label="Acções" class="col-acoes">
                <div class="acoes-cell">
                  <a class="btn ghost sm" href="?edit=<?= (int)$pr['id'] ?>">Editar</a>
                  <form method="post">
                    <input type="hidden" name="acao" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$pr['id'] ?>">
                    <button class="btn ghost sm" type="submit"><?= !empty($pr['activo']) ? 'Desactivar' : 'Activar' ?></button>
                  </form>
                  <form method="post" onsubmit="return confirm('Apagar esta promoção?')">
                    <input type="hidden" name="acao" value="apagar">
                    <input type="hidden" name="id" value="<?= (int)$pr['id'] ?>">
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

<div class="modal-root admin-modal" id="modalPromo" <?= $openModal ? 'data-auto-open="1"' : 'hidden' ?> aria-hidden="<?= $openModal ? 'false' : 'true' ?>">
  <div class="modal-backdrop" data-close-modal></div>
  <div class="modal-dialog modal-lg" role="dialog" aria-modal="true" aria-labelledby="modalPromoTitle">
    <button type="button" class="modal-close" data-close-modal aria-label="Fechar"><?= icon('close') ?></button>
    <div class="modal-body">
      <h2 id="modalPromoTitle"><?= $isEdit ? 'Editar promoção' : 'Nova promoção' ?></h2>
      <?php if ($error): ?><p class="modal-error"><?= e($error) ?></p><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <div class="admin-form-grid">
          <div class="form-group full">
            <label for="titulo">Título</label>
            <input id="titulo" name="titulo" required value="<?= e($edit['titulo'] ?? '') ?>">
          </div>
          <div class="form-group full">
            <label for="mensagem">Mensagem</label>
            <textarea id="mensagem" name="mensagem" rows="2" required><?= e($edit['mensagem'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo">
              <?php foreach (['ALERTA' => 'Alerta (barra topo)', 'BANNER' => 'Banner destaque', 'CUPOM' => 'Cupom'] as $k => $label): ?>
                <option value="<?= $k ?>" <?= (($edit['tipo'] ?? 'ALERTA') === $k) ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="desconto_percent">Desconto %</label>
            <input type="number" id="desconto_percent" name="desconto_percent" min="0" max="90" value="<?= e((string)($edit['desconto_percent'] ?? '')) ?>">
          </div>
          <div class="form-group">
            <label for="codigo_cupom">Código cupom</label>
            <input id="codigo_cupom" name="codigo_cupom" value="<?= e($edit['codigo_cupom'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="produto_id">Produto (opcional)</label>
            <select id="produto_id" name="produto_id">
              <option value="0">— Nenhum —</option>
              <?php foreach ($produtos as $p): ?>
                <option value="<?= (int)$p['id'] ?>" <?= ((int)($edit['produto_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>><?= e($p['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="data_inicio">Início</label>
            <input type="datetime-local" id="data_inicio" name="data_inicio" value="<?= e(isset($edit['data_inicio']) && $edit['data_inicio'] ? date('Y-m-d\TH:i', strtotime((string)$edit['data_inicio'])) : '') ?>">
          </div>
          <div class="form-group">
            <label for="data_fim">Fim</label>
            <input type="datetime-local" id="data_fim" name="data_fim" value="<?= e(isset($edit['data_fim']) && $edit['data_fim'] ? date('Y-m-d\TH:i', strtotime((string)$edit['data_fim'])) : '') ?>">
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end">
            <label style="display:flex;align-items:center;gap:0.5rem;margin:0;padding-bottom:0.7rem">
              <input type="checkbox" name="activo" <?= !isset($edit) || !empty($edit['activo']) ? 'checked' : '' ?>> Activa
            </label>
          </div>
        </div>
        <div class="admin-form-actions">
          <button class="btn primary" type="submit"><?= $isEdit ? 'Guardar' : 'Criar promoção' ?></button>
          <button class="btn ghost" type="button" data-close-modal>Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php admin_footer('promocoes'); ?>
