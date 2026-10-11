<?php
// Confere o usuário no banco, inclusive para sessões de login antigas.
session_start();
require __DIR__ . '/conexao.php';
function h($texto) { return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8'); }
function dinheiro($valor) { return number_format((float)$valor, 2, ',', '.'); }
if (empty($_SESSION['logado']) || empty($_SESSION['usuario'])) {
    if (basename($_SERVER['SCRIPT_NAME']) === 'status_pedidos.php' || basename($_SERVER['SCRIPT_NAME']) === 'admin_atualizacoes.php') {
        http_response_code(401); header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'Faça login novamente.']); exit;
    }
    header('Location: login.php'); exit;
}
$stmt = $conn->prepare('SELECT id, nome, email, role FROM usuarios WHERE email = ?');
$stmt->bind_param('s', $_SESSION['usuario']); $stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
if (!$cliente) { http_response_code(401); exit; }
if (empty($_SESSION['csrf_pedidos'])) { $_SESSION['csrf_pedidos'] = bin2hex(random_bytes(32)); }
$status_pedidos = ['recebido' => 'Pedido recebido', 'preparando' => 'Em preparação',
    'saiu_entrega' => 'Saiu para entrega', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado'];
function conferir_formulario() {
    if (!hash_equals($_SESSION['csrf_pedidos'], (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403); exit('Formulário expirado. Volte e atualize a página.');
    }
}
function exigir_admin($cliente) {
    if ($cliente['role'] !== 'admin') { http_response_code(403); exit('Acesso exclusivo do administrador.'); }
}
