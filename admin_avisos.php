<?php
$inicio_pedidos = (int)$conn->query('SELECT COALESCE(MAX(id),0) AS id FROM pedidos')->fetch_assoc()['id'];
$inicio_mensagens = (int)$conn->query('SELECT COALESCE(MAX(id),0) AS id FROM mensagens')->fetch_assoc()['id'];
?>
<section class="admin-avisos" id="admin-avisos" data-pedidos="<?= $inicio_pedidos ?>" data-mensagens="<?= $inicio_mensagens ?>">
<strong>Notificações</strong> <a href="admin_pedidos.php">Novos pedidos: <span id="avisos-pedidos">0</span></a>
<a href="admin.php#mensagens">Novas mensagens: <span id="avisos-mensagens">0</span></a>
<button type="button" id="limpar-avisos">Marcar avisos como vistos</button>
<p id="admin-conexao" role="status">Atualização automática a cada 5 segundos.</p>
<div id="admin-eventos" aria-live="polite"></div></section>
