<?php
require_once __DIR__ . '/bootstrap.php';
require_admin($conn);
$statuses = ['recebido','em_preparo','pronto','entregue','cancelado'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = filter_var($_POST['pedido_id'] ?? null, FILTER_VALIDATE_INT);
    $status = (string)($_POST['status'] ?? '');
    if (!$id || $id < 1 || !in_array($status,$statuses,true)) $error = 'Dados inválidos.';
    else {
        try {
            $conn->begin_transaction();
            $stmt = $conn->prepare('SELECT status FROM pedidos WHERE id = ? FOR UPDATE');
            $stmt->bind_param('i',$id); $stmt->execute(); $order = $stmt->get_result()->fetch_assoc();
            if (!$order) throw new RuntimeException('Pedido não encontrado.');
            if ($order['status'] === 'cancelado' && $status !== 'cancelado') throw new RuntimeException('Pedido cancelado não pode ser reaberto.');
            if ($status === 'cancelado' && $order['status'] !== 'cancelado') {
                $stock = $conn->prepare('UPDATE produtos SET estoque = estoque + ? WHERE id = ?');
                $lines = $conn->prepare('SELECT produto_id,quantidade FROM itens_pedido WHERE pedido_id = ?');
                $lines->bind_param('i',$id); $lines->execute();
                foreach ($lines->get_result() as $line) if ($line['produto_id'] !== null) {
                    $pid=(int)$line['produto_id']; $qty=(int)$line['quantidade'];
                    $stock->bind_param('ii',$qty,$pid); $stock->execute();
                }
            }
            $update = $conn->prepare('UPDATE pedidos SET status = ? WHERE id = ?');
            $update->bind_param('si',$status,$id); $update->execute(); $conn->commit();
            header('Location: admin_pedidos.php'); exit;
        } catch (Throwable $e) { $conn->rollback(); $error = $e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível atualizar o pedido.'; }
    }
}
$orders = $conn->query('SELECT p.*,u.nome AS nome_usuario,u.email FROM pedidos p JOIN usuarios u ON u.id=p.usuario_id ORDER BY p.id DESC LIMIT 100');
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Pedidos | Administração</title><link rel="stylesheet" href="upgrade.css"></head><body><header class="bar"><a href="admin.php">☕ Administração</a><nav><a href="admin.php">Produtos e mensagens</a><a href="index.php">Ver site</a><a href="logout.php">Sair</a></nav></header><main class="wrap"><h1>Pedidos recentes</h1><?php if ($error): ?><p class="alert"><?= h($error) ?></p><?php endif; ?><?php foreach ($orders as $o): ?><article class="panel" id="pedido-<?= (int)$o['id'] ?>"><h2>#<?= (int)$o['id'] ?> · <?= money($o['total']) ?></h2><p><?= h($o['nome_usuario']) ?> · <?= h($o['email']) ?> · <?= h($o['criado_em']) ?> · <?= h($o['tipo_entrega']) ?></p><?php if ($o['tipo_entrega'] === 'delivery'): ?><p><strong>Recebedor:</strong> <?= h($o['recebedor'] ?? '') ?> · <strong>Telefone:</strong> <?= h($o['telefone'] ?? '') ?></p><p><strong>Endereço:</strong> <?= h(!empty($o['rua']) ? $o['rua'] . ', ' . $o['numero'] . ($o['complemento'] ? ', ' . $o['complemento'] : '') . ' — ' . $o['bairro'] . ', ' . $o['cidade'] . '/' . $o['estado'] : $o['endereco']) ?> · CEP <?= h($o['cep'] ?? '') ?></p><p><strong>Referência:</strong> <?= h($o['referencia'] ?? '') ?></p><?php endif; ?><p><strong>Observações:</strong> <?= h($o['observacoes']) ?></p><p>Pagamento <?= h(str_replace('_',' ', $o['forma_pagamento'])) ?></p><form method="post"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><input type="hidden" name="pedido_id" value="<?= (int)$o['id'] ?>"><label>Status <select name="status" <?= $o['status']==='cancelado'?'disabled':'' ?>><?php foreach ($statuses as $status): ?><option value="<?= h($status) ?>" <?= $o['status']===$status?'selected':'' ?>><?= h(str_replace('_',' ', $status)) ?></option><?php endforeach; ?></select></label><?php if ($o['status'] !== 'cancelado'): ?><button>Salvar status</button><?php endif; ?></form></article><?php endforeach; ?></main></body></html>
