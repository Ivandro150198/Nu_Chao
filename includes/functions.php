<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (!defined('NU_CHAO_SECURE_BOOT')) {
    define('NU_CHAO_SECURE_BOOT', true);

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443)
        || (getenv('VERCEL') !== false);

    if (session_status() === PHP_SESSION_NONE) {
        if (getenv('VERCEL') !== false) {
            ini_set('session.save_path', '/tmp');
        }
        $cookiePath = APP_BASE_URL === '' ? '/' : APP_BASE_URL;
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => $cookiePath,
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();
    }

    if (!headers_sent()) {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: blob:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
        if ($https) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * URL relativa à base da app (ex.: /No_chao local, '' no Vercel).
 */
function url(string $path = ''): string
{
    $base = APP_BASE_URL;
    if ($path === '' || $path === '/') {
        return $base === '' ? '/' : $base . '/';
    }
    $path = ltrim($path, '/');
    return ($base === '' ? '' : $base) . '/' . $path;
}

function app_base_prefix(): string
{
    return APP_BASE_URL === '' ? '/' : APP_BASE_URL . '/';
}

function money(float|int|string $amount): string
{
    $v = round((float) $amount, 2);
    $decimals = abs($v - round($v)) < 0.00001 ? 0 : 2;
    return number_format($v, $decimals, ',', '.') . ' ' . APP_CURRENCY;
}

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_meta(): string
{
    return '<meta name="csrf-token" content="' . e(csrf_token()) . '">';
}

function csrf_valid(?string $token = null): bool
{
    $token ??= (string) ($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $session = (string) ($_SESSION['csrf_token'] ?? '');
    return $session !== '' && $token !== '' && hash_equals($session, $token);
}

function csrf_require(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_valid()) {
        http_response_code(403);
        $apiPrefix = app_base_prefix() . 'api/';
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')
            || str_starts_with($_SERVER['SCRIPT_NAME'] ?? '', $apiPrefix)) {
            json_response(['ok' => false, 'error' => 'Pedido inválido (CSRF). Recarregue a página.'], 403);
        }
        flash('error', 'Pedido inválido. Recarregue a página e tente novamente.');
        $ref = $_SERVER['HTTP_REFERER'] ?? url('index.php');
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $sameHost = $host !== '' && (str_contains($ref, '://' . $host . '/') || str_ends_with($ref, '://' . $host));
        $baseOk = APP_BASE_URL === '' || str_contains($ref, APP_BASE_URL . '/');
        if (!str_starts_with($ref, 'http') || !$sameHost || !$baseOk) {
            $ref = url('index.php');
        }
        redirect($ref);
    }
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function rate_limit_hit(string $key, int $max, int $windowSeconds): bool
{
    $bucket = $_SESSION['_rate'][$key] ?? ['count' => 0, 'start' => time()];
    if ((time() - (int) $bucket['start']) > $windowSeconds) {
        $bucket = ['count' => 0, 'start' => time()];
    }
    $bucket['count']++;
    $_SESSION['_rate'][$key] = $bucket;
    return $bucket['count'] > $max;
}

function rate_limit_clear(string $key): void
{
    unset($_SESSION['_rate'][$key]);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'nome' => $user['nome'],
        'email' => $user['email'],
        'telefone' => $user['telefone'],
        'tipo' => $user['tipo'],
    ];
    csrf_token(); // novo token após login
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        $fallbackPath = APP_BASE_URL === '' ? '/' : APP_BASE_URL;
        setcookie(session_name(), '', time() - 42000, $params['path'] ?? $fallbackPath, $params['domain'] ?? '', (bool) ($params['secure'] ?? false), (bool) ($params['httponly'] ?? true));
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function require_login(?string $tipo = null): array
{
    $user = current_user();
    if (!$user) {
        flash('error', 'Faça login para continuar.');
        redirect(url('auth/login.php'));
    }
    if ($tipo && $user['tipo'] !== $tipo) {
        flash('error', 'Não tem permissão para aceder a esta área.');
        redirect(url('index.php'));
    }
    return $user;
}

function is_safe_href(string $href): bool
{
    $href = trim($href);
    if ($href === '') {
        return false;
    }
    if (str_starts_with($href, '#') || str_starts_with($href, '/')) {
        return !preg_match('/[\s<>"\']/', $href);
    }
    if (preg_match('#^https?://#i', $href)) {
        return (bool) filter_var($href, FILTER_VALIDATE_URL);
    }
    return false;
}

function sanitize_upload_name(?string $name): ?string
{
    if ($name === null || $name === '') {
        return null;
    }
    $base = basename($name);
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $base)) {
        return null;
    }
    return $base;
}

/**
 * @return array{ok:bool,filename?:string,error?:string}
 */
function upload_image(array $file, string $prefix = 'img'): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Falha no upload.'];
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'Imagem demasiado grande (máx. 5 MB).'];
    }
    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['ok' => false, 'error' => 'Ficheiro inválido.'];
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($map[$mime])) {
        return ['ok' => false, 'error' => 'Tipo de imagem não permitido.'];
    }
    if (@getimagesize($tmp) === false) {
        return ['ok' => false, 'error' => 'Ficheiro não é uma imagem válida.'];
    }
    $nome = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
    $destino = BASE_PATH . '/assets/uploads/' . $nome;
    if (!move_uploaded_file($tmp, $destino)) {
        return ['ok' => false, 'error' => 'Não foi possível guardar a imagem.'];
    }
    return ['ok' => true, 'filename' => $nome];
}

function devolver_stock_pedido(PDO $pdo, int $pedidoId): void
{
    $itens = $pdo->prepare('SELECT produto_id, quantidade FROM itens_pedido WHERE pedido_id = ?');
    $itens->execute([$pedidoId]);
    $upd = $pdo->prepare('UPDATE produtos SET stock = stock + ? WHERE id = ?');
    foreach ($itens->fetchAll() as $item) {
        $upd->execute([(int) $item['quantidade'], (int) $item['produto_id']]);
    }
}

/**
 * Recalcula o carrinho com preços/stock actuais da BD.
 *
 * @return array{ok:bool,items:array,total:float,error?:string}
 */
function cart_revalidar(): array
{
    $cart = cart();
    if (!$cart) {
        return ['ok' => false, 'items' => [], 'total' => 0.0, 'error' => 'Carrinho vazio.'];
    }
    $stmt = db()->prepare('SELECT * FROM produtos WHERE id = ? AND ativo = 1 LIMIT 1');
    $novo = [];
    $total = 0.0;
    foreach ($cart as $key => $item) {
        $stmt->execute([(int) $item['produto_id']]);
        $produto = $stmt->fetch();
        if (!$produto) {
            return ['ok' => false, 'items' => [], 'total' => 0.0, 'error' => 'Produto indisponível: ' . ($item['nome'] ?? '')];
        }
        $qty = max(1, (int) $item['quantidade']);
        $stock = (int) $produto['stock'];
        if ($stock < $qty) {
            return ['ok' => false, 'items' => [], 'total' => 0.0, 'error' => 'Stock insuficiente para: ' . $produto['nome']];
        }
        $preco = produto_preco_efectivo($produto);
        $item['preco'] = $preco;
        $item['nome'] = $produto['nome'];
        $item['imagem'] = $produto['imagem'];
        $item['quantidade'] = $qty;
        $novo[$key] = $item;
        $total += $preco * $qty;
    }
    $_SESSION['cart'] = $novo;
    return ['ok' => true, 'items' => $novo, 'total' => $total];
}

function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    $count = 0;
    foreach (cart() as $item) {
        $count += (int) $item['quantidade'];
    }
    return $count;
}

function cart_total(): float
{
    $total = 0.0;
    foreach (cart() as $item) {
        $total += (float) $item['preco'] * (int) $item['quantidade'];
    }
    return $total;
}

function gerar_codigo_pedido(): string
{
    return 'NC' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

function status_pedido_label(string $status): string
{
    return match ($status) {
        'PENDENTE' => 'Pendente',
        'CONFIRMADO' => 'Confirmado',
        'A_CAMINHO' => 'A caminho',
        'ENTREGUE' => 'Entregue',
        'CANCELADO' => 'Cancelado',
        default => $status,
    };
}

function status_pagamento_label(string $status): string
{
    return match ($status) {
        'PENDENTE' => 'A pagar na entrega',
        'PAGO_NA_ENTREGA' => 'Pago na entrega',
        'CANCELADO' => 'Cancelado',
        default => $status,
    };
}

function metodo_pagamento_label(string $metodo): string
{
    return match ($metodo) {
        'DINHEIRO' => 'Dinheiro',
        'TPA' => 'TPA / Cartão',
        'MOBILE_MONEY' => 'Mobile Money',
        'Orange Money' => 'Orange Money',
        default => $metodo,
    };
}

/**
 * Passos do acompanhamento do pedido para a timeline do cliente.
 *
 * @return list<array{key:string,label:string,desc:string,state:string}>
 */
function pedido_tracking_steps(string $status): array
{
    $ordem = ['PENDENTE', 'CONFIRMADO', 'A_CAMINHO', 'ENTREGUE'];
    $labels = [
        'PENDENTE' => ['Pedido recebido', 'O seu pedido foi registado na Nu Chao.'],
        'CONFIRMADO' => ['Confirmado', 'A loja confirmou e prepara a entrega.'],
        'A_CAMINHO' => ['A caminho', 'O entregador está a caminho da sua morada.'],
        'ENTREGUE' => ['Entregue', 'Pedido entregue e pagamento na entrega registado.'],
    ];

    if ($status === 'CANCELADO') {
        return [
            ['key' => 'PENDENTE', 'label' => 'Pedido recebido', 'desc' => 'O pedido foi criado.', 'state' => 'done'],
            ['key' => 'CANCELADO', 'label' => 'Cancelado', 'desc' => 'Este pedido foi cancelado.', 'state' => 'cancelled'],
        ];
    }

    $idx = array_search($status, $ordem, true);
    if ($idx === false) {
        $idx = 0;
    }

    $steps = [];
    foreach ($ordem as $i => $key) {
        if ($i < $idx) {
            $state = 'done';
        } elseif ($i === $idx) {
            $state = 'current';
        } else {
            $state = 'todo';
        }
        $steps[] = [
            'key' => $key,
            'label' => $labels[$key][0],
            'desc' => $labels[$key][1],
            'state' => $state,
        ];
    }
    return $steps;
}

function cliente_pode_cancelar(array $pedido): bool
{
    return in_array($pedido['status_pedido'] ?? '', ['PENDENTE', 'CONFIRMADO'], true);
}

/**
 * Monta a mensagem do pedido para enviar no WhatsApp.
 *
 * @param array $pedido Dados do pedido (codigo, endereco, etc.)
 * @param array $itens Lista de itens com nome, tamanho, quantidade, subtotal
 * @param string $zonaNome Nome da zona de entrega
 * @param array $cliente Dados do cliente (nome, telefone)
 */
function montar_mensagem_pedido(array $pedido, array $itens, string $zonaNome, array $cliente): string
{
    $linhas = [];
    $linhas[] = '*Novo pedido — ' . APP_NAME . '*';
    $linhas[] = 'Código: *' . ($pedido['codigo'] ?? '') . '*';
    $linhas[] = '';
    $linhas[] = '*Cliente*';
    $linhas[] = ($cliente['nome'] ?? '') . ' · ' . ($cliente['telefone'] ?? ($pedido['telefone_contacto'] ?? ''));
    $linhas[] = '';
    $linhas[] = '*Entrega*';
    $linhas[] = 'Zona: ' . $zonaNome;
    $linhas[] = 'Morada: ' . ($pedido['endereco'] ?? '');
    if (!empty($pedido['ponto_referencia'])) {
        $linhas[] = 'Ref: ' . $pedido['ponto_referencia'];
    }
    $linhas[] = 'Contacto: ' . ($pedido['telefone_contacto'] ?? '');
    $linhas[] = '';
    $linhas[] = '*Itens*';
    foreach ($itens as $item) {
        $nome = $item['nome'] ?? $item['produto_nome'] ?? 'Item';
        $tam = $item['tamanho'] ?? 'Único';
        $qtd = (int) ($item['quantidade'] ?? 1);
        $sub = money($item['subtotal'] ?? (($item['preco'] ?? 0) * $qtd));
        $linhas[] = "• {$nome} ({$tam}) × {$qtd} — {$sub}";
    }
    $linhas[] = '';
    $linhas[] = 'Produtos: ' . money($pedido['total_produtos'] ?? 0);
    $linhas[] = 'Entrega: ' . money($pedido['taxa_entrega'] ?? 0);
    $linhas[] = '*Total a pagar na entrega: ' . money($pedido['valor_total'] ?? 0) . '*';
    $linhas[] = 'Pagamento: ' . metodo_pagamento_label((string) ($pedido['metodo_pagamento'] ?? 'DINHEIRO'));
    if (!empty($pedido['precisa_troco_para'])) {
        $linhas[] = 'Troco para: ' . money($pedido['precisa_troco_para']);
    }
    if (!empty($pedido['observacoes'])) {
        $linhas[] = '';
        $linhas[] = 'Obs: ' . $pedido['observacoes'];
    }
    $linhas[] = '';
    $linhas[] = '_Pedido registado no site com pagamento na entrega._';

    return implode("\n", $linhas);
}

function whatsapp_url(string $mensagem, ?string $numero = null): string
{
    $num = preg_replace('/\D+/', '', $numero ?: whatsapp_numero());
    return 'https://wa.me/' . $num . '?text=' . rawurlencode($mensagem);
}

function redirect_whatsapp(string $mensagem, ?string $numero = null): void
{
    header('Location: ' . whatsapp_url($mensagem, $numero));
    exit;
}

function config_get(string $chave, ?string $default = null): ?string
{
    static $cache = null;
    static $version = -1;
    $current = (int) ($GLOBALS['__nu_chao_config_version'] ?? 0);
    if ($cache === null || $version !== $current) {
        $cache = [];
        $version = $current;
        try {
            $rows = db()->query('SELECT chave, valor FROM configuracoes')->fetchAll();
            foreach ($rows as $row) {
                $cache[$row['chave']] = (string) $row['valor'];
            }
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return array_key_exists($chave, $cache) ? $cache[$chave] : $default;
}

function config_set(string $chave, string $valor): void
{
    $stmt = db()->prepare(
        'INSERT INTO configuracoes (chave, valor) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE valor = VALUES(valor)'
    );
    $stmt->execute([$chave, $valor]);
    $GLOBALS['__nu_chao_config_version'] = ((int) ($GLOBALS['__nu_chao_config_version'] ?? 0)) + 1;
}

function config_bool(string $chave, bool $default = false): bool
{
    $v = config_get($chave);
    if ($v === null) {
        return $default;
    }
    return in_array(strtolower($v), ['1', 'true', 'sim', 'yes', 'on'], true);
}

function site_nome(): string
{
    return trim((string) (config_get('site_nome', APP_NAME) ?? APP_NAME)) ?: APP_NAME;
}

function site_cfg(string $chave, string $default = ''): string
{
    return (string) (config_get($chave, $default) ?? $default);
}

function hero_image_url(?string $imagem): string
{
    $img = trim((string) $imagem);
    if ($img === '') {
        return APP_BASE_URL . '/assets/hero-banner.png';
    }
    // Já é URL absoluta ou caminho com /
    if (preg_match('#^(https?:)?//#i', $img) || str_starts_with($img, '/')) {
        return $img;
    }
    // Ficheiro só com nome → uploads
    if (!str_contains($img, '/')) {
        $safe = sanitize_upload_name($img);
        if (!$safe) {
            return APP_BASE_URL . '/assets/hero-banner.png';
        }
        $url = APP_BASE_URL . '/assets/uploads/' . $safe;
        $path = BASE_PATH . '/assets/uploads/' . $safe;
        if (is_file($path)) {
            $url .= '?v=' . filemtime($path);
        }
        return $url;
    }
    // Caminho relativo tipo assets/...
    $rel = ltrim($img, '/');
    $url = APP_BASE_URL . '/' . $rel;
    $path = BASE_PATH . '/' . $rel;
    if (is_file($path)) {
        $url .= '?v=' . filemtime($path);
    }
    return $url;
}

/**
 * @return list<array{title:string,text:string,note:string,cta:string,cta_href:string,image:string,image_url:string}>
 */
function hero_slides_activos(): array
{
    $fallback = [
        [
            'title' => site_nome(),
            'text' => 'Roupas e acessórios com alma da Guiné-Bissau. Encomende online e pague na entrega.',
            'note' => 'Pagamento na entrega · Entregas em Bissau',
            'cta' => 'Ver coleção',
            'cta_href' => '#produtos',
            'image' => 'assets/hero-banner.png',
            'image_url' => hero_image_url('assets/hero-banner.png'),
        ],
    ];
    try {
        $rows = db()->query(
            'SELECT * FROM hero_slides WHERE activo = 1 ORDER BY ordem ASC, id ASC'
        )->fetchAll();
        if (!$rows) {
            return $fallback;
        }
        $slides = [];
        foreach ($rows as $r) {
            $img = (string) ($r['imagem'] ?: 'assets/hero-banner.png');
            $slides[] = [
                'title' => (string) $r['titulo'],
                'text' => (string) $r['texto'],
                'note' => (string) ($r['nota'] ?? ''),
                'cta' => (string) ($r['cta_texto'] ?: 'Ver coleção'),
                'cta_href' => (string) ($r['cta_href'] ?: '#produtos'),
                'image' => $img,
                'image_url' => hero_image_url($img),
            ];
        }
        return $slides;
    } catch (Throwable $e) {
        return $fallback;
    }
}

/**
 * @param list<string> $chaves
 */
function config_save_many(array $chaves, array $post): void
{
    foreach ($chaves as $chave) {
        $valor = trim((string) ($post[$chave] ?? ''));
        config_set($chave, $valor);
    }
}

function nl2p(string $texto): string
{
    $partes = preg_split("/\n\s*\n/", trim($texto)) ?: [];
    $html = '';
    foreach ($partes as $p) {
        $html .= '<p class="muted">' . nl2br(e(trim($p))) . '</p>';
    }
    return $html;
}

/**
 * @return list<string>
 */
function linhas_lista(string $texto): array
{
    $linhas = preg_split("/\r\n|\n|\r/", $texto) ?: [];
    return array_values(array_filter(array_map('trim', $linhas), fn($l) => $l !== ''));
}

function produto_em_promocao(array $produto): bool
{
    if (empty($produto['em_promocao'])) {
        return false;
    }
    $promo = (float) ($produto['preco_promocional'] ?? 0);
    $preco = (float) ($produto['preco'] ?? 0);
    return $promo > 0 && $promo < $preco;
}

function produto_preco_efectivo(array $produto): float
{
    if (produto_em_promocao($produto)) {
        return (float) $produto['preco_promocional'];
    }
    return (float) ($produto['preco'] ?? 0);
}

function produto_desconto_percent(array $produto): int
{
    if (!produto_em_promocao($produto)) {
        return 0;
    }
    $preco = (float) $produto['preco'];
    if ($preco <= 0) {
        return 0;
    }
    return (int) round((1 - ((float) $produto['preco_promocional'] / $preco)) * 100);
}

function produto_stock_baixo(array $produto): bool
{
    $stock = (int) ($produto['stock'] ?? 0);
    $alerta = (int) ($produto['stock_alerta'] ?? config_get('stock_alerta_global', '5'));
    return $stock > 0 && $stock <= $alerta;
}

function produto_max_imagens(): int
{
    return 8;
}

function ensure_produto_imagens_table(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $pdo = db();
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS produto_imagens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                produto_id INT NOT NULL,
                ficheiro VARCHAR(255) NOT NULL,
                ordem INT NOT NULL DEFAULT 0,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_produto_ordem (produto_id, ordem),
                FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
        $rows = $pdo->query(
            "SELECT p.id, p.imagem FROM produtos p
             WHERE p.imagem IS NOT NULL AND p.imagem != ''
               AND NOT EXISTS (SELECT 1 FROM produto_imagens pi WHERE pi.produto_id = p.id)"
        )->fetchAll();
        if ($rows) {
            $ins = $pdo->prepare('INSERT INTO produto_imagens (produto_id, ficheiro, ordem) VALUES (?,?,0)');
            foreach ($rows as $r) {
                $safe = sanitize_upload_name((string) $r['imagem']);
                if ($safe) {
                    $ins->execute([(int) $r['id'], $safe]);
                }
            }
        }
    } catch (Throwable $e) {
        error_log('Nu Chao produto_imagens: ' . $e->getMessage());
    }
}

function produto_imagem_url(?string $ficheiro): string
{
    $safe = sanitize_upload_name($ficheiro);
    if (!$safe) {
        return APP_BASE_URL . '/assets/logo-nc.png';
    }
    $url = APP_BASE_URL . '/assets/uploads/' . $safe;
    $path = BASE_PATH . '/assets/uploads/' . $safe;
    if (is_file($path)) {
        $url .= '?v=' . filemtime($path);
    }
    return $url;
}

/**
 * @return list<array{id:int,produto_id:int,ficheiro:string,ordem:int,url:string}>
 */
function produto_imagens(int $produtoId): array
{
    ensure_produto_imagens_table();
    if ($produtoId < 1) {
        return [];
    }
    try {
        $stmt = db()->prepare(
            'SELECT id, produto_id, ficheiro, ordem FROM produto_imagens WHERE produto_id = ? ORDER BY ordem ASC, id ASC'
        );
        $stmt->execute([$produtoId]);
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int) $r['id'],
                'produto_id' => (int) $r['produto_id'],
                'ficheiro' => (string) $r['ficheiro'],
                'ordem' => (int) $r['ordem'],
                'url' => produto_imagem_url((string) $r['ficheiro']),
            ];
        }
        return $out;
    } catch (Throwable $e) {
        return [];
    }
}

function produto_imagem_capa(array $produto): ?string
{
    $id = (int) ($produto['id'] ?? 0);
    if ($id > 0) {
        $imgs = produto_imagens($id);
        if ($imgs) {
            return $imgs[0]['ficheiro'];
        }
    }
    $legacy = sanitize_upload_name($produto['imagem'] ?? null);
    return $legacy;
}

function produto_sync_capa(int $produtoId): void
{
    ensure_produto_imagens_table();
    $imgs = produto_imagens($produtoId);
    $capa = $imgs[0]['ficheiro'] ?? null;
    db()->prepare('UPDATE produtos SET imagem = ? WHERE id = ?')->execute([$capa, $produtoId]);
}

/**
 * Faz upload de várias imagens a partir de $_FILES['imagens'].
 *
 * @return array{ok:list<string>,errors:list<string>}
 */
function upload_images_multi(array $files, string $prefix = 'p'): array
{
    $ok = [];
    $errors = [];
    if (!isset($files['name'])) {
        return ['ok' => $ok, 'errors' => $errors];
    }
    $names = is_array($files['name']) ? $files['name'] : [$files['name']];
    $types = is_array($files['type'] ?? null) ? $files['type'] : [($files['type'] ?? '')];
    $tmps = is_array($files['tmp_name'] ?? null) ? $files['tmp_name'] : [($files['tmp_name'] ?? '')];
    $errs = is_array($files['error'] ?? null) ? $files['error'] : [($files['error'] ?? UPLOAD_ERR_NO_FILE)];
    $sizes = is_array($files['size'] ?? null) ? $files['size'] : [($files['size'] ?? 0)];

    foreach ($names as $i => $name) {
        if (($errs[$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || $name === '') {
            continue;
        }
        $up = upload_image([
            'name' => $name,
            'type' => $types[$i] ?? '',
            'tmp_name' => $tmps[$i] ?? '',
            'error' => $errs[$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $sizes[$i] ?? 0,
        ], $prefix);
        if ($up['ok']) {
            $ok[] = $up['filename'];
        } else {
            $errors[] = $up['error'] ?? 'Falha no upload.';
        }
    }
    return ['ok' => $ok, 'errors' => $errors];
}

function produto_add_imagens(int $produtoId, array $filenames): int
{
    ensure_produto_imagens_table();
    if ($produtoId < 1 || !$filenames) {
        return 0;
    }
    $actual = count(produto_imagens($produtoId));
    $max = produto_max_imagens();
    $slot = max(0, $max - $actual);
    if ($slot < 1) {
        return 0;
    }
    $pdo = db();
    $stmtOrd = $pdo->prepare('SELECT COALESCE(MAX(ordem), -1) FROM produto_imagens WHERE produto_id = ?');
    $stmtOrd->execute([$produtoId]);
    $ordem = (int) $stmtOrd->fetchColumn();
    $ins = $pdo->prepare('INSERT INTO produto_imagens (produto_id, ficheiro, ordem) VALUES (?,?,?)');
    $added = 0;
    foreach (array_slice($filenames, 0, $slot) as $file) {
        $safe = sanitize_upload_name($file);
        if (!$safe) {
            continue;
        }
        $ordem++;
        $ins->execute([$produtoId, $safe, $ordem]);
        $added++;
    }
    if ($added > 0) {
        produto_sync_capa($produtoId);
    }
    return $added;
}

function produto_remover_imagem(int $imagemId, int $produtoId = 0): bool
{
    ensure_produto_imagens_table();
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM produto_imagens WHERE id = ?');
    $stmt->execute([$imagemId]);
    $row = $stmt->fetch();
    if (!$row) {
        return false;
    }
    if ($produtoId > 0 && (int) $row['produto_id'] !== $produtoId) {
        return false;
    }
    $pid = (int) $row['produto_id'];
    $pdo->prepare('DELETE FROM produto_imagens WHERE id = ?')->execute([$imagemId]);
    $path = BASE_PATH . '/assets/uploads/' . basename((string) $row['ficheiro']);
    $still = $pdo->prepare('SELECT COUNT(*) FROM produto_imagens WHERE ficheiro = ?');
    $still->execute([$row['ficheiro']]);
    if ((int) $still->fetchColumn() === 0 && is_file($path)) {
        @unlink($path);
    }
    produto_sync_capa($pid);
    return true;
}

function produto_definir_capa(int $imagemId, int $produtoId): bool
{
    ensure_produto_imagens_table();
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM produto_imagens WHERE id = ? AND produto_id = ?');
    $stmt->execute([$imagemId, $produtoId]);
    $row = $stmt->fetch();
    if (!$row) {
        return false;
    }
    $pdo->prepare('UPDATE produto_imagens SET ordem = ordem + 1 WHERE produto_id = ?')->execute([$produtoId]);
    $pdo->prepare('UPDATE produto_imagens SET ordem = 0 WHERE id = ?')->execute([$imagemId]);
    produto_sync_capa($produtoId);
    return true;
}

/**
 * Promoções activas no momento (alertas / banners).
 *
 * @return list<array>
 */
function promocoes_activas(?string $tipo = null): array
{
    try {
        $sql = "SELECT * FROM promocoes
                WHERE activo = 1
                  AND (data_inicio IS NULL OR data_inicio <= NOW())
                  AND (data_fim IS NULL OR data_fim >= NOW())";
        $params = [];
        if ($tipo) {
            $sql .= ' AND tipo = ?';
            $params[] = $tipo;
        }
        $sql .= ' ORDER BY id DESC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function whatsapp_numero(): string
{
    $cfg = config_get('whatsapp_loja');
    if ($cfg) {
        $n = preg_replace('/\D+/', '', $cfg);
        if ($n) {
            return $n;
        }
    }
    return preg_replace('/\D+/', '', APP_WHATSAPP) ?: '245955000000';
}
