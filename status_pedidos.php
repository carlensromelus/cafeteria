<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
try {
    require __DIR__ . '/pedidos_base.php';
    session_write_close();
    // O usuário da sessão limita a consulta: nunca aceitamos um usuário pelo navegador.
    $stmt = $conn->prepare('SELECT id, status, atualizado_em FROM pedidos WHERE usuario_id = ? ORDER BY id DESC');
    $stmt->bind_param('i', $cliente['id']); $stmt->execute();
    echo json_encode(['pedidos' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)], JSON_UNESCAPED_UNICODE);
} catch (Throwable $erro) {
    http_response_code(500); echo json_encode(['erro' => 'Não foi possível consultar os pedidos.']);
}
