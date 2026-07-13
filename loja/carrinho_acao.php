<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/No_chao/index.php');
}

csrf_require();

$acao = $_POST['acao'] ?? '';
$cart = cart();

if ($acao === 'add') {
    if (!config_bool('loja_aberta', true)) {
        flash('error', config_get('mensagem_loja_fechada', 'Loja temporariamente fechada.') ?? 'Loja temporariamente fechada.');
        redirect('/No_chao/index.php#produtos');
    }

    $produtoId = (int) ($_POST['produto_id'] ?? 0);
    $tamanho = trim((string) ($_POST['tamanho'] ?? 'Único'));
    if (mb_strlen($tamanho) > 20) {
        $tamanho = 'Único';
    }

    $stmt = db()->prepare('SELECT * FROM produtos WHERE id = ? AND ativo = 1 LIMIT 1');
    $stmt->execute([$produtoId]);
    $produto = $stmt->fetch();

    if (!$produto || (int) $produto['stock'] < 1) {
        flash('error', 'Produto indisponível.');
        redirect('/No_chao/index.php#produtos');
    }

    $key = $produtoId . '|' . $tamanho;
    $qty = isset($cart[$key]) ? ((int) $cart[$key]['quantidade'] + 1) : 1;
    if ($qty > (int) $produto['stock']) {
        flash('error', 'Stock insuficiente para ' . $produto['nome'] . '.');
        redirect('/No_chao/loja/carrinho.php');
    }

    if (isset($cart[$key])) {
        $cart[$key]['quantidade'] = $qty;
        $cart[$key]['preco'] = produto_preco_efectivo($produto);
    } else {
        $cart[$key] = [
            'produto_id' => $produtoId,
            'nome' => $produto['nome'],
            'preco' => produto_preco_efectivo($produto),
            'tamanho' => $tamanho,
            'quantidade' => 1,
            'imagem' => $produto['imagem'],
        ];
    }
    $_SESSION['cart'] = $cart;
    flash('success', 'Adicionado ao carrinho.');
    $redir = trim((string) ($_POST['redirect'] ?? ''));
    if ($redir !== '' && str_starts_with($redir, '/No_chao/') && !str_contains($redir, '//') && !preg_match('/[\s<>"\']/', $redir)) {
        redirect($redir);
    }
    redirect('/No_chao/loja/carrinho.php');
}

if ($acao === 'update') {
    $key = (string) ($_POST['key'] ?? '');
    $qty = max(0, (int) ($_POST['quantidade'] ?? 0));
    if (isset($cart[$key])) {
        if ($qty === 0) {
            unset($cart[$key]);
        } else {
            $stmt = db()->prepare('SELECT * FROM produtos WHERE id = ? AND ativo = 1 LIMIT 1');
            $stmt->execute([(int) $cart[$key]['produto_id']]);
            $produto = $stmt->fetch();
            if (!$produto || $qty > (int) $produto['stock']) {
                flash('error', 'Stock insuficiente.');
                redirect('/No_chao/loja/carrinho.php');
            }
            $cart[$key]['quantidade'] = $qty;
            $cart[$key]['preco'] = produto_preco_efectivo($produto);
        }
        $_SESSION['cart'] = $cart;
    }
    redirect('/No_chao/loja/carrinho.php');
}

if ($acao === 'remove') {
    $key = (string) ($_POST['key'] ?? '');
    unset($cart[$key]);
    $_SESSION['cart'] = $cart;
    flash('info', 'Item removido.');
    redirect('/No_chao/loja/carrinho.php');
}

if ($acao === 'clear') {
    $_SESSION['cart'] = [];
    flash('info', 'Carrinho limpo.');
    redirect('/No_chao/loja/carrinho.php');
}

redirect('/No_chao/index.php');
