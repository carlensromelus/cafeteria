<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
try {
    require __DIR__ . '/pedidos_base.php';
    if ($cliente['role'] !== 'admin') { http_response_code(403); echo json_encode(['erro'=>'Acesso restrito.']); exit; }
    require __DIR__ . '/admin_cards.php';
    $csrf = $_SESSION['csrf_pedidos']; session_write_close();
    $de_pedidos = max(0, (int)($_GET['pedidos'] ?? 0));
    $de_mensagens = max(0, (int)($_GET['mensagens'] ?? 0));
    $pedidos = $conn->query('SELECT * FROM pedidos ORDER BY id DESC')->fetch_all(MYSQLI_ASSOC);
    $mensagens = $conn->query('SELECT * FROM mensagens ORDER BY id DESC')->fetch_all(MYSQLI_ASSOC);
    $cards = []; $eventos = []; $ultimo_pedido = $de_pedidos; $ultima_mensagem = $de_mensagens;
    foreach ($pedidos as $pedido) {
        ob_start(); mostrar_pedido($pedido, $status_pedidos, $csrf); $html = ob_get_clean();
        $cards[] = ['id'=>(int)$pedido['id'], 'html'=>$html];
        $ultimo_pedido = max($ultimo_pedido, (int)$pedido['id']);
        if ($pedido['id'] > $de_pedidos) { $eventos[] = ['tipo'=>'pedido','texto'=>'Novo pedido #' . $pedido['id'] . ' de ' . $pedido['nome_cliente']]; }
    }
    ob_start();
    foreach ($mensagens as $m) {
        mostrar_mensagem($m); $ultima_mensagem = max($ultima_mensagem, (int)$m['id']);
        if ($m['id'] > $de_mensagens) { $eventos[] = ['tipo'=>'mensagem','texto'=>'Nova mensagem #' . $m['id'] . ' de ' . $m['nome'] . ': ' . $m['assunto']]; }
    }
    $html_mensagens = ob_get_clean();
    echo json_encode(['pedidos'=>$cards,'mensagens_html'=>$html_mensagens,'eventos'=>$eventos,'ultimo_pedido'=>$ultimo_pedido,'ultima_mensagem'=>$ultima_mensagem], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $erro) {
    http_response_code(500); echo json_encode(['erro'=>'Não foi possível atualizar o painel.']);
}
