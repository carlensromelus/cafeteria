<?php
// Renderização compartilhada pelo painel e pela consulta automática.
function mostrar_mensagem($m) { ?>
<article class="card"><div class="info"><h3><?= h($m['nome']) ?></h3><p><strong>Email:</strong> <?= h($m['email']) ?></p><p><strong>Assunto:</strong> <?= h($m['assunto']) ?></p><p><?= nl2br(h($m['mensagem'])) ?></p><p><?= h($m['data_envio']) ?></p><a class="excluir" href="admin.php?excluir_msg=<?= (int)$m['id'] ?>" onclick="return confirm('Excluir mensagem?')">Excluir</a></div></article>
<?php }
function mostrar_pedido($pedido, $status_pedidos, $csrf) { ?>
<article data-admin-pedido="<?= (int)$pedido['id'] ?>"><h2>Pedido #<?= (int)$pedido['id'] ?> — <?= h($pedido['nome_cliente']) ?></h2><p><?= h($pedido['criado_em']) ?> • Telefone: <?= h($pedido['telefone']) ?></p><pre><?= h($pedido['itens']) ?></pre><p>Total dos produtos: R$ <?= dinheiro($pedido['total']) ?></p><p><strong>Endereço:</strong> <?= h($pedido['endereco']) ?>, <?= h($pedido['numero']) ?> — <?= h($pedido['complemento']) ?> — <?= h($pedido['bairro']) ?>, <?= h($pedido['cidade']) ?>/<?= h($pedido['estado']) ?> • CEP <?= h($pedido['cep']) ?></p>
<form method="post" action="admin_pedidos.php"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="id" value="<?= (int)$pedido['id'] ?>"><label>Status <select name="status"><?php foreach ($status_pedidos as $chave=>$nome): ?><option value="<?= $chave ?>" <?= $pedido['status'] === $chave ? 'selected' : '' ?>><?= $nome ?></option><?php endforeach; ?></select></label><button>Salvar status</button></form></article>
<?php }
