<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

ensure_produto_imagens_table();

$pdo = db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'remover_imagem') {
        $pid = (int) ($_POST['produto_id'] ?? 0);
        $iid = (int) ($_POST['imagem_id'] ?? 0);
        if (produto_remover_imagem($iid, $pid)) {
            flash('info', 'Imagem removida.');
        }
        redirect(url('admin/produtos.php?edit=') . $pid);
    }

    if ($acao === 'definir_capa') {
        $pid = (int) ($_POST['produto_id'] ?? 0);
        $iid = (int) ($_POST['imagem_id'] ?? 0);
        if (produto_definir_capa($iid, $pid)) {
            flash('success', 'Imagem principal actualizada.');
        }
        redirect(url('admin/produtos.php?edit=') . $pid);
    }

    if ($acao === 'salvar') {
        $id = (int) ($_POST['id'] ?? 0);
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $descricao = trim((string) ($_POST['descricao'] ?? ''));
        $categoria = $_POST['categoria'] === 'ACESSORIO' ? 'ACESSORIO' : 'ROUPA';
        $preco = round((float) str_replace(',', '.', (string) ($_POST['preco'] ?? 0)), 2);
        $precoPromo = trim((string) ($_POST['preco_promocional'] ?? ''));
        $precoPromocional = $precoPromo !== ''
            ? round((float) str_replace(',', '.', $precoPromo), 2)
            : null;
        $emPromocao = isset($_POST['em_promocao']) ? 1 : 0;
        $stock = (int) ($_POST['stock'] ?? 0);
        $stockAlerta = max(0, (int) ($_POST['stock_alerta'] ?? 5));
        $tamanhos = trim((string) ($_POST['tamanhos'] ?? 'Único'));
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if ($emPromocao && ($precoPromocional === null || $precoPromocional <= 0 || $precoPromocional >= $preco)) {
            $error = 'Preço promocional deve ser menor que o preço normal.';
        }

        $uploads = ['ok' => [], 'errors' => []];
        if ($error === '' && !empty($_FILES['imagens']['name']) && (is_array($_FILES['imagens']['name']) ? $_FILES['imagens']['name'][0] !== '' : $_FILES['imagens']['name'] !== '')) {
            $uploads = upload_images_multi($_FILES['imagens'], 'p');
            if ($uploads['errors'] && !$uploads['ok']) {
                $error = $uploads['errors'][0];
            }
        }

        if ($error === '' && ($nome === '' || $preco <= 0)) {
            $error = 'Nome e preço são obrigatórios.';
        } elseif ($error === '' && $id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE produtos SET nome=?, descricao=?, categoria=?, preco=?, preco_promocional=?, em_promocao=?, stock=?, stock_alerta=?, tamanhos=?, ativo=? WHERE id=?'
            );
            $stmt->execute([$nome, $descricao, $categoria, $preco, $precoPromocional, $emPromocao, $stock, $stockAlerta, $tamanhos, $ativo, $id]);
            if ($uploads['ok']) {
                $added = produto_add_imagens($id, $uploads['ok']);
                if ($added < count($uploads['ok'])) {
                    flash('info', 'Produto guardado. Algumas imagens não foram adicionadas (máx. ' . produto_max_imagens() . ').');
                    redirect(url('admin/produtos.php?edit=') . $id);
                }
            }
            flash('success', 'Produto actualizado.');
            redirect(url('admin/produtos.php'));
        } elseif ($error === '') {
            $stmt = $pdo->prepare(
                'INSERT INTO produtos (nome, descricao, categoria, preco, preco_promocional, em_promocao, stock, stock_alerta, tamanhos, imagem, ativo) VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([$nome, $descricao, $categoria, $preco, $precoPromocional, $emPromocao, $stock, $stockAlerta, $tamanhos, null, $ativo]);
            $id = (int) $pdo->lastInsertId();
            if ($uploads['ok']) {
                produto_add_imagens($id, $uploads['ok']);
            }
            flash('success', 'Produto criado.');
            redirect(url('admin/produtos.php'));
        }

        if ($error !== '') {
            $edit = [
                'id' => $id,
                'nome' => $nome,
                'descricao' => $descricao,
                'categoria' => $categoria,
                'preco' => $preco,
                'preco_promocional' => $precoPromocional,
                'em_promocao' => $emPromocao,
                'stock' => $stock,
                'stock_alerta' => $stockAlerta,
                'tamanhos' => $tamanhos,
                'ativo' => $ativo,
            ];
        }
    }

    if ($acao === 'apagar') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE produtos SET ativo = 0 WHERE id = ?')->execute([$id]);
        flash('info', 'Produto desactivado.');
        redirect(url('admin/produtos.php'));
    }
}

$edit = $edit ?? null;
if (!$edit && isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$editImagens = !empty($edit['id']) ? produto_imagens((int) $edit['id']) : [];
$produtos = $pdo->query('SELECT * FROM produtos ORDER BY ativo DESC, nome')->fetchAll();
$imgCounts = [];
try {
    foreach ($pdo->query('SELECT produto_id, COUNT(*) AS c FROM produto_imagens GROUP BY produto_id') as $row) {
        $imgCounts[(int) $row['produto_id']] = (int) $row['c'];
    }
} catch (Throwable $e) {
    $imgCounts = [];
}
$totalActivos = count(array_filter($produtos, fn($p) => !empty($p['ativo'])));
$emPromo = count(array_filter($produtos, fn($p) => produto_em_promocao($p)));
$openModal = $error !== '' || !empty($edit) || isset($_GET['novo']);
$isEdit = !empty($edit['id']);

admin_header('Produtos', 'produtos');
?>

<div class="admin-page-head">
  <div>
    <h2>Gestão de produtos</h2>
    <p>Stock, preços, várias imagens e promoções · <?= $emPromo ?> em promoção.</p>
  </div>
  <div class="head-actions">
    <button type="button" class="btn primary sm" data-open-modal="modalProduto">+ Novo produto</button>
  </div>
</div>

<div class="admin-list-card">
  <div class="list-head">
    <h3>Lista de produtos</h3>
    <span><?= count($produtos) ?> no total · <?= $totalActivos ?> activos</span>
  </div>

  <?php if (!$produtos): ?>
    <div class="empty" style="border:0">Ainda não há produtos. <button type="button" class="btn primary sm" data-open-modal="modalProduto">Criar produto</button></div>
  <?php else: ?>
    <div class="admin-scroll">
      <table class="admin-table">
        <thead>
          <tr>
            <th class="col-produto">Produto</th>
            <th class="col-cat">Categoria</th>
            <th class="col-preco">Preço</th>
            <th class="col-stock">Stock</th>
            <th class="col-tam">Tamanhos</th>
            <th class="col-estado">Estado</th>
            <th class="col-acoes">Acções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($produtos as $p): ?>
            <?php
              $low = produto_stock_baixo($p) || (int)$p['stock'] < 1;
              $capa = produto_imagem_capa($p);
              $nImg = count(produto_imagens((int) $p['id']));
            ?>
            <tr class="<?= empty($p['ativo']) ? 'is-inactive' : '' ?>">
              <td class="col-produto" data-label="Produto">
                <div class="produto-cell">
                  <div class="produto-thumb">
                    <?php if ($capa): ?>
                      <img src="<?= e(produto_imagem_url($capa)) ?>" alt="">
                    <?php else: ?>
                      <span>NC</span>
                    <?php endif; ?>
                    <?php if ($nImg > 1): ?>
                      <span class="thumb-count"><?= $nImg ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="produto-meta">
                    <strong><?= e($p['nome']) ?></strong>
                    <?php if (produto_em_promocao($p)): ?>
                      <small class="promo-tag">−<?= produto_desconto_percent($p) ?>% promo</small>
                    <?php elseif (!empty($p['descricao'])): ?>
                      <small><?= e($p['descricao']) ?></small>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td class="col-cat" data-label="Categoria">
                <span class="pill pill-cat"><?= $p['categoria'] === 'ROUPA' ? 'Roupa' : 'Acessório' ?></span>
              </td>
              <td class="col-preco" data-label="Preço">
                <?php if (produto_em_promocao($p)): ?>
                  <span class="price-old"><?= money($p['preco']) ?></span><br>
                  <strong><?= money($p['preco_promocional']) ?></strong>
                <?php else: ?>
                  <?= money($p['preco']) ?>
                <?php endif; ?>
              </td>
              <td class="col-stock" data-label="Stock">
                <span class="<?= $low ? 'stock-low' : 'stock-ok' ?>"><?= (int) $p['stock'] ?></span>
              </td>
              <td class="col-tam" data-label="Tamanhos"><?= e($p['tamanhos'] ?: 'Único') ?></td>
              <td class="col-estado" data-label="Estado">
                <?php if (!empty($p['ativo'])): ?>
                  <span class="pill pill-ok">Activo</span>
                <?php else: ?>
                  <span class="pill pill-off">Inactivo</span>
                <?php endif; ?>
              </td>
              <td class="col-acoes" data-label="Acções">
                <div class="acoes-cell">
                  <a class="btn ghost sm" href="<?= url('loja/produto.php?id=' . (int) $p['id']) ?>" target="_blank" rel="noopener">Ver</a>
                  <a class="btn ghost sm" href="?edit=<?= (int)$p['id'] ?>">Editar</a>
                  <?php if (!empty($p['ativo'])): ?>
                    <form method="post" onsubmit="return confirm('Desactivar este produto?')">
                      <input type="hidden" name="acao" value="apagar">
                      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                      <button class="btn danger sm" type="submit">Desactivar</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="modal-root admin-modal" id="modalProduto" <?= $openModal ? 'data-auto-open="1"' : 'hidden' ?> aria-hidden="<?= $openModal ? 'false' : 'true' ?>">
  <div class="modal-backdrop" data-close-modal></div>
  <div class="modal-dialog modal-lg" role="dialog" aria-modal="true" aria-labelledby="modalProdutoTitle">
    <button type="button" class="modal-close" data-close-modal aria-label="Fechar"><?= icon('close') ?></button>
    <div class="modal-body">
      <h2 id="modalProdutoTitle"><?= $isEdit ? 'Editar produto' : 'Novo produto' ?></h2>
      <?php if ($error): ?><p class="modal-error"><?= e($error) ?></p><?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">

        <div class="admin-form-grid">
          <div class="form-group full">
            <label for="nome">Nome</label>
            <input id="nome" name="nome" required value="<?= e($edit['nome'] ?? '') ?>" placeholder="Ex: Camisa em tecido africano">
          </div>
          <div class="form-group full">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" rows="3"><?= e($edit['descricao'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label for="categoria">Categoria</label>
            <select id="categoria" name="categoria">
              <option value="ROUPA" <?= (($edit['categoria'] ?? '') === 'ROUPA') ? 'selected' : '' ?>>Roupa</option>
              <option value="ACESSORIO" <?= (($edit['categoria'] ?? '') === 'ACESSORIO') ? 'selected' : '' ?>>Acessório</option>
            </select>
          </div>
          <div class="form-group">
            <label for="preco">Preço normal (XOF)</label>
            <input type="number" id="preco" name="preco" min="0.01" step="0.01" required value="<?= e(isset($edit['preco']) ? rtrim(rtrim(number_format((float)$edit['preco'], 2, '.', ''), '0'), '.') : '') ?>">
          </div>
          <div class="form-group">
            <label for="preco_promocional">Preço promoção (XOF)</label>
            <input type="number" id="preco_promocional" name="preco_promocional" min="0" step="0.01" value="<?= e(isset($edit['preco_promocional']) && $edit['preco_promocional'] !== null && $edit['preco_promocional'] !== '' ? rtrim(rtrim(number_format((float)$edit['preco_promocional'], 2, '.', ''), '0'), '.') : '') ?>">
          </div>
          <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" min="0" value="<?= e((string)($edit['stock'] ?? '0')) ?>">
          </div>
          <div class="form-group">
            <label for="stock_alerta">Alerta stock baixo</label>
            <input type="number" id="stock_alerta" name="stock_alerta" min="0" value="<?= e((string)($edit['stock_alerta'] ?? '5')) ?>">
          </div>
          <div class="form-group">
            <label for="tamanhos">Tamanhos</label>
            <input id="tamanhos" name="tamanhos" value="<?= e($edit['tamanhos'] ?? 'S,M,L,XL') ?>">
          </div>
          <div class="form-group full">
            <label for="imagens">Imagens (até <?= produto_max_imagens() ?>, pode seleccionar várias)</label>
            <input type="file" id="imagens" name="imagens[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-preview-multi="prodPreviewMulti">
            <div class="admin-img-preview" id="prodPreviewMulti"></div>
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end">
            <label style="display:flex;align-items:center;gap:0.5rem;margin:0;padding-bottom:0.7rem">
              <input type="checkbox" name="em_promocao" <?= !empty($edit['em_promocao']) ? 'checked' : '' ?>> Em promoção
            </label>
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end">
            <label style="display:flex;align-items:center;gap:0.5rem;margin:0;padding-bottom:0.7rem">
              <input type="checkbox" name="ativo" <?= !isset($edit) || !empty($edit['ativo']) ? 'checked' : '' ?>> Activo na loja
            </label>
          </div>
        </div>

        <div class="admin-form-actions">
          <button class="btn primary" type="submit"><?= $isEdit ? 'Guardar' : 'Criar produto' ?></button>
          <button class="btn ghost" type="button" data-close-modal>Cancelar</button>
        </div>
      </form>

      <?php if ($editImagens): ?>
        <div class="admin-img-manage">
          <h3 style="margin:1rem 0 0.5rem;font-size:1rem">Imagens actuais</h3>
          <div class="admin-img-grid">
            <?php foreach ($editImagens as $i => $img): ?>
              <div class="admin-img-item<?= $i === 0 ? ' is-cover' : '' ?>">
                <img src="<?= e($img['url']) ?>" alt="">
                <?php if ($i === 0): ?><span class="img-cover-label">Principal</span><?php endif; ?>
                <div class="admin-img-actions">
                  <?php if ($i !== 0): ?>
                    <form method="post">
                      <?= csrf_field() ?>
                      <input type="hidden" name="acao" value="definir_capa">
                      <input type="hidden" name="produto_id" value="<?= (int)$edit['id'] ?>">
                      <input type="hidden" name="imagem_id" value="<?= (int)$img['id'] ?>">
                      <button class="btn ghost sm" type="submit">Principal</button>
                    </form>
                  <?php endif; ?>
                  <form method="post" onsubmit="return confirm('Remover esta imagem?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="acao" value="remover_imagem">
                    <input type="hidden" name="produto_id" value="<?= (int)$edit['id'] ?>">
                    <input type="hidden" name="imagem_id" value="<?= (int)$img['id'] ?>">
                    <button class="btn danger sm" type="submit">Remover</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php admin_footer('produtos'); ?>
