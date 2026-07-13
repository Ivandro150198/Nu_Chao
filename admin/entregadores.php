<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($acao === 'criar') {
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $telefone = trim((string) ($_POST['telefone'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');
        if ($nome && $email && $telefone && strlen($senha) >= 6) {
            try {
                // Criado pelo admin já nasce aprovado
                $pdo->prepare(
                    "INSERT INTO usuarios (nome, email, telefone, senha_hash, tipo, ativo, aprovado)
                     VALUES (?,?,?,?, 'ENTREGADOR', 1, 1)"
                )->execute([$nome, $email, $telefone, password_hash($senha, PASSWORD_DEFAULT)]);
                flash('success', 'Entregador criado e aprovado.');
            } catch (PDOException $e) {
                flash('error', 'Email já existe ou dados inválidos.');
            }
        } else {
            flash('error', 'Preencha todos os campos (senha mín. 6).');
        }
        redirect('/No_chao/admin/entregadores.php');
    }

    if ($acao === 'aprovar' && $id > 0) {
        $pdo->prepare(
            "UPDATE usuarios SET aprovado = 1, ativo = 1 WHERE id = ? AND tipo = 'ENTREGADOR'"
        )->execute([$id]);
        flash('success', 'Entregador aprovado. Já pode entrar na área de entregas.');
        redirect('/No_chao/admin/entregadores.php');
    }

    if ($acao === 'rejeitar' && $id > 0) {
        $pdo->prepare(
            "UPDATE usuarios SET aprovado = 0, ativo = 0 WHERE id = ? AND tipo = 'ENTREGADOR'"
        )->execute([$id]);
        flash('info', 'Pedido de entregador rejeitado.');
        redirect('/No_chao/admin/entregadores.php');
    }

    if ($acao === 'toggle' && $id > 0) {
        $pdo->prepare(
            "UPDATE usuarios SET ativo = IF(ativo=1,0,1) WHERE id = ? AND tipo = 'ENTREGADOR' AND aprovado = 1"
        )->execute([$id]);
        flash('info', 'Estado do entregador actualizado.');
        redirect('/No_chao/admin/entregadores.php');
    }
}

$pendentes = $pdo->query(
    "SELECT * FROM usuarios
     WHERE tipo = 'ENTREGADOR' AND aprovado = 0 AND ativo = 1
     ORDER BY criado_em DESC"
)->fetchAll();

$lista = $pdo->query(
    "SELECT u.*,
            (SELECT COUNT(*) FROM pedidos p WHERE p.entregador_id = u.id) AS total_entregas,
            (SELECT COUNT(*) FROM pedidos p WHERE p.entregador_id = u.id AND p.status_pedido = 'A_CAMINHO') AS em_curso
     FROM usuarios u
     WHERE u.tipo = 'ENTREGADOR' AND (u.aprovado = 1 OR u.ativo = 0)
     ORDER BY u.aprovado DESC, u.ativo DESC, u.nome"
)->fetchAll();

admin_header('Entregadores', 'entregadores');
$openEntrega = isset($_GET['novo']);
?>

<div class="admin-page-head">
  <div>
    <h2>Entregadores</h2>
    <p>Aprove pedidos de cadastro e gira as contas de deliver.</p>
  </div>
  <div class="head-actions">
    <button type="button" class="btn primary sm" data-open-modal="modalEntregador">+ Novo entregador</button>
  </div>
</div>

<div class="admin-stack">
  <?php if ($pendentes): ?>
    <div class="admin-list-card">
      <div class="list-head">
        <h3>Pedidos a aprovar</h3>
        <span><?= count($pendentes) ?> pendente(s)</span>
      </div>
      <div class="admin-scroll">
        <table class="admin-table" style="table-layout:auto">
          <thead>
            <tr>
              <th>Nome</th>
              <th>Contacto</th>
              <th>Registado em</th>
              <th>Estado</th>
              <th>Acções</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pendentes as $e): ?>
              <tr>
                <td data-label="Nome">
                  <strong><?= e($e['nome']) ?></strong><br>
                  <small class="muted"><?= e($e['email']) ?></small>
                </td>
                <td data-label="Contacto"><?= e($e['telefone']) ?></td>
                <td data-label="Data"><?= e(date('d/m/Y H:i', strtotime($e['criado_em']))) ?></td>
                <td data-label="Estado"><span class="pill" style="color:var(--accent);border-color:rgba(226,177,90,.4)">Pendente</span></td>
                <td data-label="Acções" class="col-acoes">
                  <div class="acoes-cell">
                    <form method="post">
                      <input type="hidden" name="acao" value="aprovar">
                      <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                      <button class="btn primary sm" type="submit">Aprovar</button>
                    </form>
                    <form method="post" onsubmit="return confirm('Rejeitar este entregador?')">
                      <input type="hidden" name="acao" value="rejeitar">
                      <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                      <button class="btn danger sm" type="submit">Rejeitar</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <div class="admin-list-card">
    <div class="list-head">
      <h3>Entregadores</h3>
      <span><?= count($lista) ?> contas</span>
    </div>
    <div class="admin-scroll">
      <table class="admin-table" style="table-layout:auto">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Contacto</th>
            <th>Entregas</th>
            <th>Em curso</th>
            <th>Estado</th>
            <th>Acções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista as $e): ?>
            <tr class="<?= empty($e['ativo']) ? 'is-inactive' : '' ?>">
              <td data-label="Nome">
                <strong><?= e($e['nome']) ?></strong><br>
                <small class="muted"><?= e($e['email']) ?></small>
              </td>
              <td data-label="Contacto"><?= e($e['telefone']) ?></td>
              <td data-label="Entregas"><?= (int) $e['total_entregas'] ?></td>
              <td data-label="Em curso"><?= (int) $e['em_curso'] ?></td>
              <td data-label="Estado">
                <?php if (!empty($e['aprovado']) && !empty($e['ativo'])): ?>
                  <span class="pill pill-ok">Aprovado</span>
                <?php elseif (!empty($e['aprovado']) && empty($e['ativo'])): ?>
                  <span class="pill pill-off">Desactivado</span>
                <?php else: ?>
                  <span class="pill pill-off">Rejeitado</span>
                <?php endif; ?>
              </td>
              <td data-label="Acções" class="col-acoes">
                <div class="acoes-cell">
                  <?php if (!empty($e['aprovado'])): ?>
                    <form method="post">
                      <input type="hidden" name="acao" value="toggle">
                      <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                      <button class="btn ghost sm" type="submit"><?= !empty($e['ativo']) ? 'Desactivar' : 'Activar' ?></button>
                    </form>
                  <?php else: ?>
                    <form method="post">
                      <input type="hidden" name="acao" value="aprovar">
                      <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                      <button class="btn primary sm" type="submit">Aprovar</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$lista): ?>
            <tr><td colspan="6" class="muted">Ainda sem entregadores aprovados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal-root admin-modal" id="modalEntregador" <?= $openEntrega ? 'data-auto-open="1"' : 'hidden' ?> aria-hidden="<?= $openEntrega ? 'false' : 'true' ?>">
  <div class="modal-backdrop" data-close-modal></div>
  <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalEntregadorTitle">
    <button type="button" class="modal-close" data-close-modal aria-label="Fechar"><?= icon('close') ?></button>
    <div class="modal-body">
      <h2 id="modalEntregadorTitle">Novo entregador</h2>
      <p class="muted">Conta criada já aprovada para entregas.</p>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="criar">
        <div class="admin-form-grid">
          <div class="form-group">
            <label>Nome</label>
            <input name="nome" required>
          </div>
          <div class="form-group">
            <label>Email (login)</label>
            <input type="email" name="email" required>
          </div>
          <div class="form-group">
            <label>Telefone</label>
            <input name="telefone" required>
          </div>
          <div class="form-group">
            <label>Palavra-passe</label>
            <input type="password" name="senha" required minlength="6">
          </div>
        </div>
        <div class="admin-form-actions">
          <button class="btn primary" type="submit">Criar e aprovar</button>
          <button class="btn ghost" type="button" data-close-modal>Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php admin_footer('entregadores'); ?>
