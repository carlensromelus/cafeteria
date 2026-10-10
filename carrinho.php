<?php
require_once __DIR__ . '/bootstrap.php';
$user = require_user($conn);
$_SESSION['carrinho'] ??= [];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = (string)($_POST['acao'] ?? '');
    $id = filter_var($_POST['produto_id'] ?? null, FILTER_VALIDATE_INT);
    if ($action === 'adicionar' && $id && $id > 0) {
        $stmt = $conn->prepare('SELECT estoque FROM produtos WHERE id = ? AND ativo = 1');
        $stmt->bind_param('i', $id); $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        if ($p && (int)$p['estoque'] > 0) {
            $_SESSION['carrinho'][$id] = min((int)$p['estoque'], (int)($_SESSION['carrinho'][$id] ?? 0) + 1);
            header('Location: carrinho.php'); exit;
        }
        $error = 'Produto indisponível.';
    } elseif ($action === 'atualizar' && $id && $id > 0) {
        $qtd = filter_var($_POST['quantidade'] ?? null, FILTER_VALIDATE_INT);
        if ($qtd !== false && $qtd >= 0 && $qtd <= 99) {
            $stmt = $conn->prepare('SELECT estoque FROM produtos WHERE id = ? AND ativo = 1');
            $stmt->bind_param('i', $id); $stmt->execute();
            $p = $stmt->get_result()->fetch_assoc();
            $new = $p ? min($qtd, (int)$p['estoque']) : 0;
            if ($new) $_SESSION['carrinho'][$id] = $new;
            else unset($_SESSION['carrinho'][$id]);
            header('Location: carrinho.php'); exit;
        }
        $error = 'Quantidade inválida.';
    }
}
$items = []; $total = 0;
$stmt = $conn->prepare('SELECT id, nome, preco, imagem, estoque FROM produtos WHERE id = ? AND ativo = 1');
foreach ($_SESSION['carrinho'] as $id => $qtd) {
    $id = (int)$id; $stmt->bind_param('i', $id); $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    if (!$p || (int)$p['estoque'] < 1) { unset($_SESSION['carrinho'][$id]); continue; }
    $qtd = min((int)$qtd, (int)$p['estoque']); $_SESSION['carrinho'][$id] = $qtd;
    $p['quantidade'] = $qtd; $items[] = $p; $total += (float)$p['preco'] * $qtd;
}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Carrinho | Lumora Café</title><link rel="stylesheet" href="upgrade.css"></head><body>
<header class="bar"><a href="index.php">☕ Lumora Café</a><nav><a href="index.php#cardapio">Cardápio</a><a href="pedidos.php">Meus pedidos</a><a href="logout.php">Sair</a></nav></header>
<main class="wrap"><h1>Seu carrinho</h1><?php if ($error): ?><p class="alert"><?= h($error) ?></p><?php endif; ?>
<?php if (!$items): ?><p>Seu carrinho está vazio. <a href="index.php#cardapio">Explore o cardápio</a>.</p><?php endif; ?>
<?php foreach ($items as $p): ?><article class="row"><div><strong><?= h($p['nome']) ?></strong><p><?= money($p['preco']) ?> cada · até <?= (int)$p['estoque'] ?> disponíveis</p></div><form method="post"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><input type="hidden" name="acao" value="atualizar"><input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>"><label>Quantidade <input type="number" min="0" max="99" name="quantidade" value="<?= (int)$p['quantidade'] ?>" required></label><button>Atualizar</button></form></article><?php endforeach; ?>
<?php if ($items): ?><div class="row"><strong>Total: <?= money($total) ?></strong><a class="button" href="checkout.php">Finalizar pedido</a></div><?php endif; ?></main></body></html>
