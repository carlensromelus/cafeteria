<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/conexao.php';
function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function check_csrf(): void {
    if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403); exit('Formulário expirado. Recarregue a página.');
    }
}
function current_user(mysqli $conn): ?array {
    $id = (int)($_SESSION['user_id'] ?? 0);
    if (!$id && !empty($_SESSION['usuario'])) {
        $stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $_SESSION['usuario']); $stmt->execute();
        $id = (int)($stmt->get_result()->fetch_assoc()['id'] ?? 0);
        if ($id) $_SESSION['user_id'] = $id;
    }
    if (!$id) return null;
    $stmt = $conn->prepare('SELECT id, nome, email, role FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $id); $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}
function require_user(mysqli $conn): array {
    $user = current_user($conn);
    if (!$user) { header('Location: login.php'); exit; }
    return $user;
}
function require_admin(mysqli $conn): array {
    $user = require_user($conn);
    if ($user['role'] !== 'admin') { http_response_code(403); exit('Acesso restrito ao administrador.'); }
    return $user;
}
function money($amount): string { return 'R$ ' . number_format((float)$amount, 2, ',', '.'); }
