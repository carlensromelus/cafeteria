<?php
require __DIR__ . '/pedidos_base.php';
$stmt = $conn->prepare('SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $cliente['id']); $stmt->execute(); $pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Meus pedidos — Lumora</title><link rel="stylesheet" href="pedidos.css"></head><body><main>
<nav><a href="index.php">Início</a><a href="novo_pedido.php">Novo pedido</a><a href="logout.php">Sair</a></nav><h1>Acompanhe seu pedido</h1>
<p id="conexao" role="status">Consultando os status automaticamente a cada 5 segundos.</p><div id="novidades" aria-live="polite" class="aviso"></div>
<?php if (!$pedidos): ?><article>Você ainda não fez pedidos. <a href="novo_pedido.php">Faça o primeiro pedido.</a></article><?php endif; ?>
<?php foreach ($pedidos as $pedido): ?><article data-pedido="<?= (int)$pedido['id'] ?>" data-status="<?= h($pedido['status']) ?>">
<h2>Pedido #<?= (int)$pedido['id'] ?></h2><p>Criado em <?= h($pedido['criado_em']) ?></p><h3 class="status"><?= h($status_pedidos[$pedido['status']] ?? $pedido['status']) ?></h3>
<ol class="etapas"><?php $etapas = array_slice($status_pedidos,0,4,true); $indice = array_search($pedido['status'],array_keys($etapas),true); $i=0; foreach ($etapas as $chave=>$nome): ?><li data-etapa="<?= $chave ?>" class="<?= $indice !== false && $i < $indice ? 'concluida' : ($indice !== false && $i === $indice ? 'atual' : '') ?>"><?= $nome ?></li><?php $i++; endforeach; ?></ol>
<p class="atualizacao">Última alteração: <?= h($pedido['atualizado_em']) ?></p><pre><?= h($pedido['itens']) ?></pre><strong>Total dos produtos: R$ <?= dinheiro($pedido['total']) ?></strong>
<p>Entrega: <?= h($pedido['endereco']) ?>, <?= h($pedido['numero']) ?> <?= h($pedido['complemento']) ?> — <?= h($pedido['bairro']) ?>, <?= h($pedido['cidade']) ?>/<?= h($pedido['estado']) ?>. CEP <?= h($pedido['cep']) ?></p>
</article><?php endforeach; ?></main><script src="acompanhar_pedidos.js"></script></body></html>
