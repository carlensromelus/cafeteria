<?php
// =========================
// CONEXÃO COM BANCO DE DADOS
// Lumora Café
// =========================

// Configurações do banco
$host = "localhost";
$user = "root";
$pass = "";
$db   = "lumora_cafe_v2";

// Cria conexão
$conn = new mysqli($host, $user, $pass, $db);


// Verifica erro de conexão
if ($conn->connect_error) {
    die("❌ Erro de conexão com o banco de dados: " . $conn->connect_error);
}

// Define charset (evita problema com acentos)
$conn->set_charset("utf8mb4");
?>




