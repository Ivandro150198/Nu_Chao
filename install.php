<?php
declare(strict_types=1);

/**
 * Instalação rápida: cria a base de dados e importa o schema.
 * Abra: http://localhost/No_chao/install.php
 * Após instalar, este ficheiro fica bloqueado por install.lock
 */

$lockFile = __DIR__ . '/config/install.lock';
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$sqlFile = __DIR__ . '/sql/schema.sql';

$messages = [];
$ok = false;
$blocked = is_file($lockFile);

if ($blocked) {
    $messages[] = 'A instalação já foi concluída. Remova config/install.lock apenas se quiser reinstalar (apaga dados).';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = (string) ($_POST['confirm'] ?? '');
    if ($confirm !== 'INSTALAR') {
        $messages[] = 'Escreva INSTALAR para confirmar.';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $sql = file_get_contents($sqlFile);
            if ($sql === false) {
                throw new RuntimeException('Não foi possível ler sql/schema.sql');
            }
            $pdo->exec($sql);
            if (!is_dir(__DIR__ . '/config')) {
                mkdir(__DIR__ . '/config', 0755, true);
            }
            file_put_contents($lockFile, 'installed_at=' . date('c') . "\n");
            $ok = true;
            $messages[] = 'Base de dados nu_chao criada com sucesso.';
            $messages[] = 'Contas demo criadas. Altere as palavras-passe após o primeiro acesso.';
            $messages[] = 'Por segurança, mantenha install.lock e não partilhe credenciais.';
        } catch (Throwable $e) {
            $messages[] = 'Erro na instalação. Verifique o MySQL (XAMPP) e tente novamente.';
            error_log('Nu Chao install: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Instalar Nu Chao</title>
  <style>
    body { font-family: Georgia, serif; background: #0f2e1f; color: #f4f0e6; min-height: 100vh; display: grid; place-items: center; margin: 0; }
    .box { background: #163828; border: 1px solid #2d5a42; padding: 2rem; max-width: 480px; width: 90%; }
    h1 { margin-top: 0; font-size: 1.6rem; color: #c8e6c9; }
    li { margin: .4rem 0; }
    a { color: #9fdfb0; }
    .ok { color: #9fdfb0; }
    .err { color: #f5b7b1; }
    input, button { width: 100%; padding: .7rem; margin-top: .5rem; box-sizing: border-box; }
    button { background: #2d5a42; color: #fff; border: 0; cursor: pointer; font: inherit; }
    label { display: block; margin-top: 1rem; font-size: .9rem; }
  </style>
</head>
<body>
  <div class="box">
    <h1>Instalação Nu Chao</h1>
    <?php if ($messages): ?>
      <ul class="<?= $ok ? 'ok' : 'err' ?>">
        <?php foreach ($messages as $m): ?>
          <li><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($ok): ?>
      <p><a href="index.php">Abrir a loja</a> · <a href="login.php">Entrar</a></p>
    <?php elseif (!$blocked): ?>
      <p>Isto cria/recria a base de dados <strong>nu_chao</strong> e apaga dados existentes.</p>
      <form method="post">
        <label for="confirm">Escreva <strong>INSTALAR</strong> para confirmar</label>
        <input id="confirm" name="confirm" required autocomplete="off" placeholder="INSTALAR">
        <button type="submit">Instalar agora</button>
      </form>
    <?php else: ?>
      <p><a href="index.php">Voltar à loja</a></p>
    <?php endif; ?>
  </div>
</body>
</html>
